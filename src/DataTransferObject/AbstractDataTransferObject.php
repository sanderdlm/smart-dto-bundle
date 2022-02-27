<?php

namespace Dreadnip\SmartDtoBundle\DataTransferObject;

use Doctrine\ORM\Mapping\Entity;
use Dreadnip\SmartDtoBundle\Attribute\MapsTo;
use Dreadnip\SmartDtoBundle\Exception\DataTransferObjectException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

abstract class AbstractDataTransferObject
{
    protected object $source;
    protected string $mappedClass;

    public static function from(object $source): static
    {
        $class = static::class;
        $dto = new $class();

        $dto->detectMappedClass();

        if (!$source instanceof $dto->mappedClass) {
            throw new RuntimeException('Passed object is not the same class as configured by the MapsTo attribute.');
        }

        $dto->source = $source;

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $propertyInfo = new PropertyInfoExtractor(
            [new ReflectionExtractor()],
            [new ReflectionExtractor()],
            [],
            [new ReflectionExtractor()],
        );

        // Read all public properties of the child DTO that extends this class
        $properties = $propertyInfo->getProperties(static::class);

        if (!empty($properties)) {
            foreach ($properties as $property) {
                // If a property is already set in the constructor, don't override it
                if ($dto->{$property} !== null) {
                    continue;
                }

                // If the property is not writable (a.k.a. public), skip it
                if (!$propertyInfo->isWritable(static::class, $property)) {
                    continue;
                }

                // Get the value from the passed source object
                $sourceValue = $propertyAccessor->getValue($dto->source, $property);

                // Get the property type & class name
                $types = $propertyInfo->getTypes(static::class, $property);

                if (empty($types)) {
                    throw DataTransferObjectException::missingPropertyTypeHint($property, static::class);
                }

                $type = reset($types);
                $className = $type->getClassName();

                // If the property is a class that extends this class, handle it recursively
                if (
                    $className !== null &&
                    $sourceValue !== null &&
                    $dto->isDataTransferObject($className)
                ) {
                    $dto->{$property} = $className::from($sourceValue);
                } else {
                    $dto->{$property} = $sourceValue;
                }
            }
        }

        return $dto;
    }

    public function create(): object
    {
        $this->detectMappedClass();

        return $this->callConstructor($this->mappedClass, $this);
    }

    public function update(): object
    {
        return $this->callUpdate($this->source, $this);
    }

    private function callConstructor(string $class, AbstractDataTransferObject $dto): object
    {
        if (!method_exists($class, '__construct')) {
            throw DataTransferObjectException::missingMethod('__constructor', $class);
        }

        $reflectionMethod = new ReflectionMethod($class, '__construct');

        $parameters = $this->resolveMethodParameters($reflectionMethod, $dto);

        return new $class(...$parameters);
    }

    private function callUpdate(object $entity, AbstractDataTransferObject $dto): object
    {
        if (!method_exists($entity, 'update')) {
            throw DataTransferObjectException::missingMethod('update', get_class($entity));
        }

        $reflectionMethod = new ReflectionMethod($entity, 'update');

        $parameters = $this->resolveMethodParameters($reflectionMethod, $dto);

        $entity->update(...$parameters);

        return $entity;
    }

    /**
     * @return array<mixed>
     */
    private function resolveMethodParameters(ReflectionMethod $method, AbstractDataTransferObject $dto): array
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $resolvedParameters = [];

        foreach ($method->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (!property_exists($dto, $name)) {
                throw DataTransferObjectException::missingProperty($name, get_class($dto));
            }

            $matchingDtoProperty = $dto->{$name};

            /** @var ReflectionNamedType $propertyType */
            $propertyType = $parameter->getType();
            $propertyName = $propertyType->getName();

            // If the property is a class, e.g. App\Entity\Address and the
            // property in our DTO, e.g. $this->address is a DTO
            if (!$propertyType->isBuiltin() && $this->isDataTransferObject($matchingDtoProperty)) {
                // If we're running an update, we have to check for pre-existing entities to update
                if (isset($this->source)) {
                    $sourceValue = $propertyAccessor->getValue($this->source, $name);

                    // If there is a pre-existing entity, update it and return the object
                    if ($this->isEntity($propertyName) && $sourceValue instanceof $propertyName) {
                        $this->callUpdate($sourceValue, $matchingDtoProperty);

                        $resolvedParameters[] = $sourceValue;
                    } else {
                        $resolvedParameters[] = $this->callConstructor($propertyName, $matchingDtoProperty);
                    }
                } else {
                    $resolvedParameters[] = $this->callConstructor($propertyName, $matchingDtoProperty);
                }
            } else {
                $resolvedParameters[] = $matchingDtoProperty;
            }
        }

        return $resolvedParameters;
    }

    private function detectMappedClass(): void
    {
        $thisClass = get_class($this);
        $parentClass = get_parent_class($this);

        if ($parentClass === false) {
            $parentClass = null;
        }

        $attribute = $this->readAttribute($thisClass, MapsTo::class);

        /** @phpstan-ignore-next-line Bug, see https://github.com/phpstan/phpstan/issues/4302 */
        if ($attribute === null && $parentClass !== null) {
            $attribute = $this->readAttribute($parentClass, MapsTo::class);
        }

        if ($attribute === null) {
            throw DataTransferObjectException::missingAttribute('MapsTo', $thisClass, $parentClass);
        }

        /** @var MapsTo $instance */
        $instance = $attribute->newInstance();

        $this->mappedClass = $instance->getEntity();
    }

    private function isDataTransferObject(mixed $class): bool
    {
        return is_subclass_of($class, self::class);
    }

    private function isEntity(string $class): bool
    {
        return $this->readAttribute($class, Entity::class) !== null;
    }

    private function readAttribute(string $class, string $attribute): ?ReflectionAttribute
    {
        if (!class_exists($class)) {
            throw DataTransferObjectException::nonExistentClass($class);
        }

        $reflectionClass = new ReflectionClass($class);

        $attributes = $reflectionClass->getAttributes($attribute);

        if ($attributes) {
            return reset($attributes);
        }

        return null;
    }
}
