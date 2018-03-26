<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\DoctrineAssociation\Deferred;

use DeskPRO\Component\DoctrineAssociation\DoctrineAssociationManager;

/**
 * This represents an array (or a single int) of foreign key IDs for the doctrine association that
 * will be queried for in the future.
 *
 * Often times (especially in our API) we know we want a set of IDs, but we might want LOTS of sets of
 * IDs. To avoid performing lots of query's in a loop, you can defer the resolving of the IDs if you don't
 * need them immediately by asking the DoctrineAssociationManager for an instance of this class.
 *
 * You don't normally construct this yourself. See DoctrineAssociationManager::deferAssociationIds().
 *
 * When you really do need the data, you can call resolve() and get the array (or single int). You should
 * avoid doing this until you have registered all of your DeferredIdentity with the manager, and it will try
 * to make the most optimized queries it can to deliver on all of these promises.
 */
class DeferredIdentity
{
    /**
     * @var DoctrineAssociationManager
     */
    private $assoc_manager;
    private $source_entity;
    private $property_name;
    private $resolved_identity;
    private $resolved_entities;
    private $identity_payload;
    private $include_entity;
    private $entity_payload;

    public function __construct(DoctrineAssociationManager $assoc_manager, $source_entity, $property_name, $include_entity = false)
    {
        $this->assoc_manager     = $assoc_manager;
        $this->source_entity     = $source_entity;
        $this->property_name     = $property_name;
        $this->include_entity    = $include_entity;
        $this->resolved_identity = false;
        $this->resolved_entities = false;
        $this->identity_payload  = null;
        $this->entity_payload    = null;
    }

    /**
     * This will automatically trigger the association manager to resolve everything, so
     * be sure to push this call off as far into the future as possible. Ideally you want
     * to register all Deferred's with the association manager before resolving any of them.
     *
     * @return mixed the result
     */
    public function resolve()
    {
        if (!$this->resolved_identity) {
            $this->assoc_manager->resolveDeferredIdentity();
        }

        if ($this->include_entity && !$this->resolved_entities) {
            $this->assoc_manager->resolveDeferredEntities();
        }
    }

    /**
     * @return bool
     */
    public function isResolvedIdentity()
    {
        return $this->resolved_identity;
    }

    /**
     * @return bool
     */
    public function isResolvedEntities()
    {
        return $this->resolved_entities;
    }

    /**
     * @return mixed
     */
    public function getSourceEntity()
    {
        return $this->source_entity;
    }

    /**
     * @return mixed
     */
    public function getPropertyName()
    {
        return $this->property_name;
    }

    /**
     * @return mixed
     */
    public function getIdentityPayload()
    {
        return $this->identity_payload;
    }

    /**
     * The DoctrineAssociationManager will set a result to mark this as resolved.
     *
     * @param mixed $identity_payload
     */
    public function resolveIdentityPayload($identity_payload)
    {
        $this->identity_payload  = $identity_payload;
        $this->resolved_identity = true;
    }

    /**
     * The DoctrineAssociationManager will set a result to mark this as resolved.
     *
     * @param mixed $entity_payload
     */
    public function resolveEntitiesPayload($entity_payload)
    {
        $this->entity_payload    = $entity_payload;
        $this->resolved_entities = true;
    }

    /**
     * @return mixed
     */
    public function getEntityPayload()
    {
        return $this->entity_payload;
    }

    public function markForInclude()
    {
        $this->include_entity = true;
    }

    public function isIncludeEntities()
    {
        return $this->include_entity;
    }
}
