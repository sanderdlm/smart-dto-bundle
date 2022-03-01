<?php

namespace Dreadnip\SmartDtoBundle\Factory;

use Dreadnip\SmartDtoBundle\DataTransferObject\AbstractDataTransferObject;
use Dreadnip\SmartDtoBundle\Exception\DataTransferObjectException;
use Dreadnip\SmartDtoBundle\ValueObject\Property;
use ReflectionMethod;
use ReflectionNamedType;
use Symfony\Component\PropertyAccess\PropertyAccess;

class EntityFactory
{
    public function create(string $class, AbstractDataTransferObject $dto): object
    {
        if (!method_exists($class, '__construct')) {
            throw DataTransferObjectException::missingMethod('__constructor', $class);
        }

        $reflectionMethod = new ReflectionMethod($class, '__construct');

        $parameters = $this->buildParameterList($reflectionMethod, $dto);

        $resolvedParameters = $this->resolveParameters($parameters);

        return new $class(...$resolvedParameters);
    }

    public function update(object $entity, AbstractDataTransferObject $dto): object
    {
        if (!method_exists($entity, 'update')) {
            throw DataTransferObjectException::missingMethod('update', get_class($entity));
        }

        $reflectionMethod = new ReflectionMethod($entity, 'update');

        $parameters = $this->buildParameterList($reflectionMethod, $dto);

        $resolvedParameters = $this->resolveParameters($parameters, $entity);

        $entity->update(...$resolvedParameters);

        return $entity;
    }

    /**
     * @return array<Property>
     */
    public function buildParameterList(ReflectionMethod $method, AbstractDataTransferObject $dto): array
    {
        $parameters = [];

        foreach ($method->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (!property_exists($dto, $name)) {
                throw DataTransferObjectException::missingProperty($name, get_class($dto));
            }

            /** @var ReflectionNamedType $propertyType */
            $propertyType = $parameter->getType();

            $parameters[] = new Property(
                $name,
                $propertyType,
                $dto->{$name}
            );
        }

        return $parameters;
    }

    /**
     * @param array<Property> $parameters
     * @return array<mixed>
     */
    public function resolveParameters(array $parameters, ?object $entity = null): array
    {
        return array_map(function (Property $parameter) use ($entity) {
            return $this->resolveParameter($parameter, $entity);
        }, $parameters);
    }

    public function resolveParameter(Property $parameter, ?object $entity = null): mixed
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        if (!$parameter->isBuiltin() && $parameter->isDataTransferObject()) {
            if ($entity === null) {
                return $this->create($parameter->getType(), $parameter->getMatch());
            } else {
                $entityValue = $propertyAccessor->getValue($entity, $parameter->getName());

                $propertyType = $parameter->getType();
                if (!$parameter->isEntity() || !$entityValue instanceof $propertyType) {
                    return$this->create($parameter->getType(), $parameter->getMatch());
                }

                $this->update($entityValue, $parameter->getMatch());

                return $entityValue;
            }
        }

        return $parameter->getMatch();
    }
}
