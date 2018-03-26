<?php

namespace DeskPRO\Bundle\AppBundle\Entity\ObjectAlias;

use DeskPRO\Bundle\AppBundle\ObjectAlias\Converters;
use DeskPRO\Bundle\AppBundle\ObjectAlias\QualifiedName;
use Doctrine\ORM;

class Aliases
{
    /**
     * Returns the list of entity types representing aliases
     *
     * @param ORM\EntityManager $entityManager
     * @return array|string[]
     */
    public static function getAliasTypes(ORM\EntityManager $entityManager)
    {
        $metadata = $entityManager->getClassMetadata(AbstractAlias::class);
        return $metadata->subClasses;
    }

    /**
     * @param string $objectType
     * @param ORM\EntityManager $entityManager
     * @return bool
     */
    public static function canHaveAlias($objectType, ORM\EntityManager $entityManager)
    {
        $metadata = $entityManager->getClassMetadata(AbstractAlias::class);
        if (empty($metadata->subClasses)) {
            return false;
        }

        foreach ($metadata->subClasses as $className) {
            if (Aliases::isAliasFor($className, $objectType)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param {string} $aliasName
     * @param {mixed} $object
     * @param Builder $builder
     * @return AbstractAlias
     */
    public static function createAlias( $alias, $object, Builder $builder)
    {
        $name = Converters::toNameFromString($alias);
        if (QualifiedName::isValidIdentifier($name)) {
            $builder->setAlias($alias);
        }

        $builder->setObject($object);

        $appQualifier = FullyQualifiedAppName::parseName($name);
        if ($appQualifier) {
            $builder->setApp($appQualifier);
        }

        return $builder->build();
    }

    /**
     * @param {string} $objectType the fully qualified class name for the desired object
     * @param ORM\EntityManager $entityManager
     * @return string
     */
    public static function resolveAliasType($objectType, ORM\EntityManager $entityManager)
    {
        $metadata = $entityManager->getClassMetadata(AbstractAlias::class);
        if (empty($metadata->subClasses)) {
            return null;
        }

        $resolution = [];
        foreach ($metadata->subClasses as $className) {
            if (Aliases::isAliasFor($className, $objectType)) {
                $resolution[] = $className;
            }
        }

        if (0 === count($resolution)) {
            return null;
        }

        if (1 === count($resolution)) {
            return $resolution[0];
        }

        throw new \DomainException('Ambiguous alias resolution');
    }

    /**
     * @param $aliasType
     * @param string|object $object
     * @return bool
     */
    private static function isAliasFor($aliasType, $object)
    {
        $objectType = is_string($object) ? $object : get_class($object);

        /** @var \ReflectionMethod $setObjectMethod */
        $setObjectMethod = null;

        $reflectionClass = new \ReflectionClass($aliasType);
        if ($reflectionClass->hasMethod('setObject')) {
            $setObjectMethod = $reflectionClass->getMethod('setObject');
        }

        $declaredType = null;
        if ($setObjectMethod && $setObjectMethod->getNumberOfParameters() === 1) {
            $parameter = $setObjectMethod->getParameters()[0];
            try {
                $declaredType = $parameter->getClass()->getName();
            } catch (\Exception $e) { $declaredType = null; }
        }

        if (!is_null($declaredType) && $declaredType === $objectType) {
            return true;
        }

        return false;
    }
}
