<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\DoctrineAssociation;

use DeskPRO\Component\DoctrineAssociation\Deferred\DeferredIdentity;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\MappingException;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Helps us get the IDs of a doctrine association more efficiently than looping through hydrated objects.
 */
class DoctrineAssociationManager
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var DeferredIdentity[]
     */
    private $deferred_ids;

    /**
     * @var DeferredIdentity[]
     */
    private $deferred_includes;

    /**
     * @var array the metadata object for an entity class (we cache here, since its always the same)
     */
    protected $metadatas;

    public function __construct(EntityManager $em)
    {
        $this->em                = $em;
        $this->deferred_ids      = [];
        $this->deferred_includes = [];
    }

    /**
     * Gives you an object that represents an array of association IDs (or a single ID int if the association is singular).
     *
     * You can get the real value at any time by calling $deferred_ids->resolve();
     *
     * Don't call resolve() until you need to. The longer you wait to call "resolve", the more chance we have to make optimized queries to resolve it.
     *
     * @param $entity
     * @param $property_name
     *
     * @return DeferredIdentity
     */
    public function deferAssociationIds($entity, $property_name)
    {
        $deferred = new DeferredIdentity($this, $entity, $property_name);

        $this->deferred_ids[] = $deferred;

        return $deferred;
    }

    public function markDeferredAsIncluded(DeferredIdentity $deferred_identity)
    {
        $this->deferred_includes[] = $deferred_identity;
    }

    /**
     * Get the doctrine array representing an association on the given property for an entity or null if not an association.
     *
     * It is safe to ask this even if it is not a mapped field, you will get null (not an exception).
     *
     * @param $entity
     * @param $property_name
     *
     * @return array|null
     */
    public function getAssociation($entity, $property_name)
    {
        try {
            return $this->getMetadata($entity)->getAssociationMapping($property_name);
        } catch (MappingException $e) {
            return;
        }
    }

    /**
     * Is this an association or not?
     *
     * @param $entity
     * @param $property_name
     *
     * @return bool
     */
    public function isAssociation($entity, $property_name)
    {
        return (bool) $this->getAssociation($entity, $property_name);
    }

    /**
     * @param object $entity
     *
     * @return ClassMetadata
     */
    public function getMetadata($entity)
    {
        $entity_class = get_class($entity);
        if (!isset($this->metadatas[$entity_class])) {
            $this->metadatas[$entity_class] = $this->em->getClassMetadata($entity_class);
        }

        return $this->metadatas[$entity_class];
    }

    public function resolveDeferredIdentity()
    {
        $property_accessor = PropertyAccess::createPropertyAccessor();

        // NOTE: this is not optimized. it is just doing the basics. but it is here so that we CAN have a
        //       chance to optimize it.
        foreach ($this->deferred_ids as $deferred) {
            if ($deferred->isResolvedIdentity()) {
                continue; // already resolved the ids for this one
            }

            $entity        = $deferred->getSourceEntity();
            $property_name = $deferred->getPropertyName();

            $assoc = $this->getAssociation($entity, $property_name);
            if ($assoc['type'] < ClassMetadata::TO_ONE) {
                // gets the single ID (no query necessary here)
                $obj = $property_accessor->getValue($entity, $property_name);
                if ($obj) {
                    $result = $this->getEntityIdentifier($obj);
                } else {
                    $result = null;
                }
            } else {
                // a query IS done here, and it creates a proxy doctrine object with only the ID (the rest is lazy)
                $collection = $property_accessor->getValue($entity, $property_name);

                $ids = [];

                if ($collection) {
                    foreach ($collection as $child) {
                        // again, no real query is done on this method call
                        $ids[] = $this->getEntityIdentifier($child);
                    }
                }

                $result = $ids;
            }

            $deferred->resolveIdentityPayload($result);
        }
    }

    /**
     * Execute no queries, but get the identifier of an entity.
     *
     * Doctire returns an array - in case of a composite we concatenate it with a dash.
     *
     * @param $entity
     *
     * @return int|string
     */
    protected function getEntityIdentifier($entity)
    {
        $id = implode('-', $this->em->getUnitOfWork()->getEntityIdentifier($entity));

        if (is_numeric($id)) {
            return (int) $id; // most of the time there is no "-" composite key, return a real int.
        }

        return $id;
    }

    public function resolveDeferredEntities()
    {
        $property_accessor = PropertyAccess::createPropertyAccessor();

        foreach ($this->deferred_ids as $deferred) {
            if (!$deferred->isIncludeEntities()) {
                continue; // ths one doesn't need the actual entities, skip it
            }

            if (!$deferred->isResolvedIdentity()) {
                $this->resolveDeferredIdentity(); // ids need to be resolved first
            }

            $entity        = $deferred->getSourceEntity();
            $property_name = $deferred->getPropertyName();

            $assoc = $this->getAssociation($entity, $property_name);
            if ($assoc['type'] < ClassMetadata::TO_ONE) {
                // gets the single ID (no query necessary here)
                $obj = $property_accessor->getValue($entity, $property_name);
                $deferred->resolveEntitiesPayload($obj);
            } else {
                // a query IS done here, and it creates a proxy doctrine object with only the ID (the rest is lazy)
                $collection = $property_accessor->getValue($entity, $property_name);
                $deferred->resolveEntitiesPayload($collection);
            }
        }
    }
}
