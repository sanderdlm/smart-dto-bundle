<?php

namespace Dreadnip\SmartDtoBundle\DataTransferObject;

use Dreadnip\SmartDtoBundle\Attribute\MapsTo;
use Dreadnip\SmartDtoBundle\Factory\EntityFactory;
use Dreadnip\SmartDtoBundle\Exception\DataTransferObjectException;
use Dreadnip\SmartDtoBundle\Hydration\DataTransferObjectHydrator;
use Dreadnip\SmartDtoBundle\Mapping\AttributeReader;

abstract class AbstractDataTransferObject
{
    private ?object $entity = null;
    private string $mappedClass;

    public static function fromEntity(object $entity): self
    {
        $class = static::class;
        $dto = new $class();

        return (new DataTransferObjectHydrator())->hydrate($dto, $entity);
    }

    public function create(): object
    {
        $this->setMappedClass();

        return (new EntityFactory())->create($this->getMappedClass(), $this);
    }

    public function update(): object
    {
        if ($this->getEntity() === null) {
            throw DataTransferObjectException::updateWithoutSource();
        }

        return (new EntityFactory())->update($this->getEntity(), $this);
    }

    public function getMappedClass(): string
    {
        return $this->mappedClass;
    }

    public function setMappedClass(): void
    {
        $this->mappedClass = $this->detectMappedClass();

        $this->validate();
    }

    public function getEntity(): ?object
    {
        return $this->entity;
    }

    public function setEntity(object $entity): void
    {
        $this->entity = $entity;
    }

    public function validate(): void
    {
        $mappedClass = $this->getMappedClass();
        $entity = $this->getEntity();

        if ($entity !== null && !$entity instanceof $mappedClass) {
            throw DataTransferObjectException::mappedClassMismatch($mappedClass, get_class($entity));
        }
    }

    private function detectMappedClass(): string
    {
        foreach ($this->getParentClasses() as $class) {
            $attribute = AttributeReader::getAttribute($class, MapsTo::class);

            if ($attribute !== null) {
                /** @var MapsTo $attribute */
                return $attribute->getEntity();
            }
        }

        throw DataTransferObjectException::missingAttribute('MapsTo', $this->getParentClasses());
    }

    /**
     * @return array<string>
     */
    private function getParentClasses(): array
    {
        $start = get_class($this);
        $chain = [$start];

        while (($parent = get_parent_class($start)) !== false) {
            $chain[] = $parent;
            $start = $parent;
        }

        return $chain;
    }
}
