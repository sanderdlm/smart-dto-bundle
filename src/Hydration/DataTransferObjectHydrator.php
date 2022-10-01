<?php

declare(strict_types=1);

namespace Dreadnip\SmartDtoBundle\Hydration;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Entity;
use Dreadnip\SmartDtoBundle\Attribute\MapsTo;
use Dreadnip\SmartDtoBundle\DataMapperTrait;
use Dreadnip\SmartDtoBundle\Exception\DataTransferObjectException;
use Dreadnip\SmartDtoBundle\Mapping\AttributeReader;
use RuntimeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

class DataTransferObjectHydrator
{
    public function hydrate(object $dto, object $entity): object
    {
        $dtoClassName = get_class($dto);

        if (!$this->hasDataMapperTrait($dto)) {
            throw DataTransferObjectException::missingTrait($dtoClassName);
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $propertyInfo = $this->createPropertyInfo();

        // Read all public properties of the child DTO that extends this class
        $properties = $propertyInfo->getProperties($dtoClassName);

        if ($properties !== null) {
            foreach ($properties as $property) {
                if (!$propertyInfo->isWritable($dtoClassName, $property)) {
                    continue;
                }

                $sourceValue = $propertyAccessor->getValue($entity, $property);

                $types = $propertyInfo->getTypes($dtoClassName, $property);

                if (empty($types)) {
                    throw DataTransferObjectException::missingPropertyTypeHint($property, $dtoClassName);
                }

                $type = reset($types);

                $dto->{$property} = $this->getPropertyValue($type->getClassName(), $sourceValue);
            }
        }

        return $dto;
    }

    private function getPropertyValue(?string $className, mixed $sourceValue): mixed
    {
        if ($sourceValue === null) {
            return null;
        }

        if ($className === null) {
            return $sourceValue;
        }

        if ($this->hasDataMapperTrait($className)) {
            return $className::from($sourceValue);
        }

        if ($sourceValue instanceof Collection) {
            $collectionAsArray = $sourceValue->toArray();

            foreach ($collectionAsArray as &$item) {
                $itemClass = get_class($item);

                if (!$itemClass) {
                    continue;
                }

                if (AttributeReader::getAttribute($itemClass, Entity::class) === null) {
                    continue;
                }

                /** @var ?MapsTo $mapsToAttribute */
                $mapsToAttribute = AttributeReader::getAttribute($itemClass, MapsTo::class);

                if ($mapsToAttribute === null) {
                    throw new RuntimeException(sprintf(
                        'Entity %s is missing the MapsTo attribute with the matching DTO.',
                        $itemClass
                    ));
                }

                $mappedDTO = $mapsToAttribute->dataTransferObject;

                if (!$this->hasDataMapperTrait($mappedDTO)) {
                    continue;
                }

                $item = $mappedDTO::from($item);
            }

            return new ArrayCollection($collectionAsArray);
        }

        return $sourceValue;
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

    private function hasDataMapperTrait(string|object $class): bool
    {
        return is_array(class_uses($class)) && in_array(DataMapperTrait::class, class_uses($class));
    }
}
