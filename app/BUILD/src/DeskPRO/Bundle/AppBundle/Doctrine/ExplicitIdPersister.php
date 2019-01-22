<?php

namespace DeskPRO\Bundle\AppBundle\Doctrine;

use Doctrine\ORM\EntityManager;

/**
 * Class ExplicitIdPersister.
 */
class ExplicitIdPersister
{
    /**
     * @param EntityManager $em
     * @param mixed         $entity
     * @param int           $explicitId
     * @param callable      $persistFn
     */
    public static function persistWithId(EntityManager $em, $entity, $explicitId, $persistFn)
    {
        if ($explicitId) {
            // default persist with id generator
            $persistFn($entity);
        }

        $reflection = new \ReflectionClass($entity);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($entity, $explicitId);

        $className     = get_class($entity);
        $metadata      = $em->getClassMetadata($className);
        $generator     = $metadata->idGenerator;
        $generatorType = $metadata->generatorType;

        $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

        $unitOfWork    = $em->getUnitOfWork();
        $persistersRef = new \ReflectionProperty($unitOfWork, 'persisters');
        $persistersRef->setAccessible(true);
        $persisters = $persistersRef->getValue($unitOfWork);
        unset($persisters[$className]);
        $persistersRef->setValue($unitOfWork, $persisters);

        $persistFn($entity);

        $property->setAccessible(false);
        $metadata->setIdGenerator($generator);
        $metadata->setIdGeneratorType($generatorType);

        $persisters = $persistersRef->getValue($unitOfWork);
        unset($persisters[$className]);
        $persistersRef->setValue($unitOfWork, $persisters);
        $persistersRef->setAccessible(false);
        $em->refresh($entity);
    }
}
