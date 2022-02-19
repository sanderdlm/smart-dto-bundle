<?php

namespace Dreadnip\SmartDtoBundle\DataTransferObject;

use App\Attribute\MapsTo;
use Doctrine\ORM\Mapping\Entity;
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

    final public function __construct(?object $source = null)
    {
        if ($source === null) {
            return;
        }

        $this->mappedClass = $this->detectMappedClass();

        if (!$source instanceof $this->mappedClass) {
            throw new RuntimeException('Passed object is not the same class as configured by the MapsTo attribute.');
        }

        $this->source = $source;

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
                // Only continue if the property is writable (a.k.a. public)
                if ($propertyInfo->isWritable(static::class, $property)) {
                    // Get the value from the passed source object
                    $sourceValue = $propertyAccessor->getValue($this->source, $property);

                    // Get the property type & class name
                    $types = $propertyInfo->getTypes(static::class, $property);

                    if (!empty($types)) {
                        $type = reset($types);
                        $className = $type->getClassName();

                        // If the property is a class that extends this class, handle it recursively
                        if ($className !== null && $this->isDataTransferObject($className)) {
                            $this->{$property} = new $className($sourceValue);
                        } else {
                            $this->{$property} = $sourceValue;
                        }
                    }
                }
            }
        }
    }

    public function create(): object
    {
        return $this->callConstructor($this->mappedClass, $this);
    }

    public function update(): void
    {
        $this->callUpdate($this->source, $this);
    }

    private function callConstructor(string $class, AbstractDataTransferObject $dto): object
    {
        if (!method_exists($class, '__construct')) {
            throw new \RuntimeException('Method "' . '__construct' . '" not found in ' . $class);
        }

        $reflectionMethod = new ReflectionMethod($class, '__construct');

        $parameters = $this->resolveMethodParameters($reflectionMethod, $dto);

        return new $class(...$parameters);
    }

    private function callUpdate(object $entity, AbstractDataTransferObject $dto): void
    {
        if (!method_exists($entity, 'update')) {
            throw new \RuntimeException('Method "' . 'update' . '" not found in ' . get_class($entity));
        }

        $reflectionMethod = new ReflectionMethod($entity, 'update');

        $parameters = $this->resolveMethodParameters($reflectionMethod, $dto);

        $entity->update(...$parameters);
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
                throw new \RuntimeException('Missing property ' . $name . ' on ' . get_class($dto) . '.');
            }

            $matchingDtoProperty = $dto->{$name};

            /** @var ReflectionNamedType $propertyType */
            $propertyType = $parameter->getType();
            $propertyName = $propertyType->getName();

            // If the property is a class, e.g. App\Entity\Address and the
            // property in our DTO, e.g. $this->address is a DTO
            if (!$propertyType->isBuiltin() && $this->isDataTransferObject($matchingDtoProperty)) {
                $sourceValue = $propertyAccessor->getValue($this->source, $name);

                // If there is a pre-existing entity, update it and return the object
                if ($this->isEntity($propertyName) && $sourceValue instanceof $propertyName) {
                    $this->callUpdate($sourceValue, $matchingDtoProperty);

                    $resolvedParameters[] = $sourceValue;
                } else {
                    $resolvedParameters[] = $this->callConstructor($propertyName, $matchingDtoProperty);
                }
            } else {
                $resolvedParameters[] = $matchingDtoProperty;
            }
        }

        return $resolvedParameters;
    }

    private function detectMappedClass(): string
    {
        $thisClass = get_class($this);
        $parentClass = get_parent_class($this);

        $attribute = $this->readAttribute($thisClass, MapsTo::class);

        /** @phpstan-ignore-next-line Bug, see https://github.com/phpstan/phpstan/issues/4302 */
        if ($attribute === null && $parentClass !== false) {
            $attribute = $this->readAttribute($parentClass, MapsTo::class);
        }

        if ($attribute === null) {
            throw new RuntimeException('Missing MapsTo attribute on ' . $thisClass . ' or ' . $parentClass);
        }

        /** @var MapsTo $instance */
        $instance = $attribute->newInstance();

        return $instance->getEntity();
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
            throw new RuntimeException('Class ' . $class . ' does not exist');
        }

        $reflectionClass = new ReflectionClass($class);

        $attributes = $reflectionClass->getAttributes($attribute);

        if ($attributes) {
            return reset($attributes);
        }

        return null;
    }
}
