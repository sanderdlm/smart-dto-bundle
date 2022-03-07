<?php

namespace Dreadnip\SmartDtoBundle\Hydration;

use Dreadnip\SmartDtoBundle\DataTransferObject\AbstractDataTransferObject;
use Dreadnip\SmartDtoBundle\Exception\DataTransferObjectException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

class DataTransferObjectHydrator
{
    private const INTERNAL_PROPERTIES = [
        'entity',
        'mappedClass',
    ];

    public function hydrate(object $dto, object $entity): AbstractDataTransferObject
    {
        $dtoClassName = get_class($dto);

        if (!is_subclass_of($dto, AbstractDataTransferObject::class)) {
            throw DataTransferObjectException::missingAbstractExtend($dtoClassName);
        }

        $dto->setEntity($entity);
        $dto->setMappedClass();

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $propertyInfo = $this->createPropertyInfo();

        // Read all public properties of the child DTO that extends this class
        $properties = $propertyInfo->getProperties($dtoClassName);

        if (!empty($properties)) {
            foreach ($properties as $propertyName) {
                // Skip the internal properties of the abstract DTO
                if (in_array($propertyName, self::INTERNAL_PROPERTIES)) {
                    continue;
                }

                // If the property is not writable (a.k.a. public), skip it
                if (!$propertyInfo->isWritable($dtoClassName, $propertyName)) {
                    continue;
                }

                // If a property is already set in the constructor, don't override it
                if ($dto->{$propertyName} !== null) {
                    continue;
                }

                // Get the value from the passed entity object
                $entityValue = $propertyAccessor->getValue($entity, $propertyName);

                // Get the property type & class name
                $types = $propertyInfo->getTypes($dtoClassName, $propertyName);

                if (empty($types)) {
                    throw DataTransferObjectException::missingPropertyTypeHint($propertyName, $dtoClassName);
                }

                $type = reset($types);
                $className = $type->getClassName();

                // If the property is a class that extends this class, handle it recursively
                if (
                    $className !== null &&
                    $entityValue !== null &&
                    is_subclass_of($className, AbstractDataTransferObject::class)
                ) {
                    $dto->{$propertyName} = $className::fromEntity($entityValue);
                } else {
                    $dto->{$propertyName} = $entityValue;
                }
            }
        }

        return $dto;
    }

    private function createPropertyInfo(): PropertyInfoExtractor
    {
        $reflectionExtractor = new ReflectionExtractor();

        return new PropertyInfoExtractor(
            [$reflectionExtractor],
            [$reflectionExtractor],
            [],
            [$reflectionExtractor],
        );
    }
}
