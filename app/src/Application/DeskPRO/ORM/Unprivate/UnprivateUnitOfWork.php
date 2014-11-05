<?php
/* This file has been auto-generated (2014-11-05). See build-vendors-mutate.php */
namespace Application\DeskPRO\ORM\Unprivate;
use Doctrine\ORM\Configuration, Doctrine\ORM\Persisters, Doctrine\ORM\EntityManager, Doctrine\ORM\Events, Doctrine\ORM\Event, Doctrine\ORM\Query, Doctrine\ORM\Internal, Doctrine\ORM\NativeQuery, Doctrine\ORM\QueryBuilder, Doctrine\ORM\PersistentCollection, Doctrine\ORM\ORMInvalidArgumentException, Doctrine\ORM\ORMException, Doctrine\ORM\OptimisticLockException, Doctrine\ORM\TransactionRequiredException, Doctrine\ORM\EntityNotFoundException;
use Exception;
use InvalidArgumentException;
use UnexpectedValueException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\Common\PropertyChangedListener;
use Doctrine\Common\Persistence\ObjectManagerAware;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Proxy\Proxy;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\ListenersInvoker;
class UnprivateUnitOfWork extends \Doctrine\ORM\UnitOfWork
{
    const STATE_MANAGED = 1;
    const STATE_NEW = 2;
    const STATE_DETACHED = 3;
    const STATE_REMOVED = 4;
    const HINT_DEFEREAGERLOAD = 'deferEagerLoad';
    protected $identityMap = array();
    protected $entityIdentifiers = array();
    protected $originalEntityData = array();
    protected $entityChangeSets = array();
    protected $entityStates = array();
    protected $scheduledForDirtyCheck = array();
    protected $entityInsertions = array();
    protected $entityUpdates = array();
    protected $extraUpdates = array();
    protected $entityDeletions = array();
    protected $collectionDeletions = array();
    protected $collectionUpdates = array();
    protected $visitedCollections = array();
    protected $em;
    protected $commitOrderCalculator;
    protected $persisters = array();
    protected $collectionPersisters = array();
    protected $evm;
    protected $listenersInvoker;
    protected $orphanRemovals = array();
    protected $readOnlyObjects = array();
    protected $eagerLoadingEntities = array();
    public function __construct(EntityManager $em)
    {
        $this->em               = $em;
        $this->evm              = $em->getEventManager();
        $this->listenersInvoker = new ListenersInvoker($em);
    }
    public function commit($entity = null)
    {
                if ($this->evm->hasListeners(Events::preFlush)) {
            $this->evm->dispatchEvent(Events::preFlush, new PreFlushEventArgs($this->em));
        }
                if ($entity === null) {
            $this->computeChangeSets();
        } elseif (is_object($entity)) {
            $this->computeSingleEntityChangeSet($entity);
        } elseif (is_array($entity)) {
            foreach ($entity as $object) {
                $this->computeSingleEntityChangeSet($object);
            }
        }
        if ( ! ($this->entityInsertions ||
                $this->entityDeletions ||
                $this->entityUpdates ||
                $this->collectionUpdates ||
                $this->collectionDeletions ||
                $this->orphanRemovals)) {
            $this->dispatchOnFlushEvent();
            $this->dispatchPostFlushEvent();
            return;         }
        if ($this->orphanRemovals) {
            foreach ($this->orphanRemovals as $orphan) {
                $this->remove($orphan);
            }
        }
        $this->dispatchOnFlushEvent();
                $commitOrder = $this->getCommitOrder();
        $conn = $this->em->getConnection();
        $conn->beginTransaction();
        try {
            if ($this->entityInsertions) {
                foreach ($commitOrder as $class) {
                    $this->executeInserts($class);
                }
            }
            if ($this->entityUpdates) {
                foreach ($commitOrder as $class) {
                    $this->executeUpdates($class);
                }
            }
                        if ($this->extraUpdates) {
                $this->executeExtraUpdates();
            }
                        foreach ($this->collectionDeletions as $collectionToDelete) {
                $this->getCollectionPersister($collectionToDelete->getMapping())->delete($collectionToDelete);
            }
                        foreach ($this->collectionUpdates as $collectionToUpdate) {
                $this->getCollectionPersister($collectionToUpdate->getMapping())->update($collectionToUpdate);
            }
                        if ($this->entityDeletions) {
                for ($count = count($commitOrder), $i = $count - 1; $i >= 0; --$i) {
                    $this->executeDeletions($commitOrder[$i]);
                }
            }
            $conn->commit();
        } catch (Exception $e) {
            $this->em->close();
            $conn->rollback();
            throw $e;
        }
                foreach ($this->visitedCollections as $coll) {
            $coll->takeSnapshot();
        }
        $this->dispatchPostFlushEvent();
                $this->entityInsertions =
        $this->entityUpdates =
        $this->entityDeletions =
        $this->extraUpdates =
        $this->entityChangeSets =
        $this->collectionUpdates =
        $this->collectionDeletions =
        $this->visitedCollections =
        $this->scheduledForDirtyCheck =
        $this->orphanRemovals = array();
    }
    protected function computeScheduleInsertsChangeSets()
    {
        foreach ($this->entityInsertions as $entity) {
            $class = $this->em->getClassMetadata(get_class($entity));
            $this->computeChangeSet($class, $entity);
        }
    }
    protected function computeSingleEntityChangeSet($entity)
    {
        $state = $this->getEntityState($entity);
        if ($state !== self::STATE_MANAGED && $state !== self::STATE_REMOVED) {
            throw new \InvalidArgumentException("Entity has to be managed or scheduled for removal for single computation " . self::objToStr($entity));
        }
        $class = $this->em->getClassMetadata(get_class($entity));
        if ($state === self::STATE_MANAGED && $class->isChangeTrackingDeferredImplicit()) {
            $this->persist($entity);
        }
                $this->computeScheduleInsertsChangeSets();
        if ($class->isReadOnly) {
            return;
        }
                if ($entity instanceof Proxy && ! $entity->__isInitialized__) {
            return;
        }
                $oid = spl_object_hash($entity);
        if ( ! isset($this->entityInsertions[$oid]) && isset($this->entityStates[$oid])) {
            $this->computeChangeSet($class, $entity);
        }
    }
    protected function executeExtraUpdates()
    {
        foreach ($this->extraUpdates as $oid => $update) {
            list ($entity, $changeset) = $update;
            $this->entityChangeSets[$oid] = $changeset;
            $this->getEntityPersister(get_class($entity))->update($entity);
        }
    }
    public function getEntityChangeSet($entity)
    {
        $oid = spl_object_hash($entity);
        if (isset($this->entityChangeSets[$oid])) {
            return $this->entityChangeSets[$oid];
        }
        return array();
    }
    public function computeChangeSet(ClassMetadata $class, $entity)
    {
        $oid = spl_object_hash($entity);
        if (isset($this->readOnlyObjects[$oid])) {
            return;
        }
        if ( ! $class->isInheritanceTypeNone()) {
            $class = $this->em->getClassMetadata(get_class($entity));
        }
        $invoke = $this->listenersInvoker->getSubscribedSystems($class, Events::preFlush) & ~ListenersInvoker::INVOKE_MANAGER;
        if ($invoke !== ListenersInvoker::INVOKE_NONE) {
            $this->listenersInvoker->invoke($class, Events::preFlush, $entity, new PreFlushEventArgs($this->em), $invoke);
        }
        $actualData = array();
        foreach ($class->reflFields as $name => $refProp) {
            $value = $refProp->getValue($entity);
            if ($class->isCollectionValuedAssociation($name) && $value !== null) {
                if ($value instanceof PersistentCollection) {
                    if ($value->getOwner() === $entity) {
                        continue;
                    }
                    $value = new ArrayCollection($value->getValues());
                }
                                if ( ! $value instanceof Collection) {
                    $value = new ArrayCollection($value);
                }
                $assoc = $class->associationMappings[$name];
                                $value = new PersistentCollection(
                    $this->em, $this->em->getClassMetadata($assoc['targetEntity']), $value
                );
                $value->setOwner($entity, $assoc);
                $value->setDirty( ! $value->isEmpty());
                $class->reflFields[$name]->setValue($entity, $value);
                $actualData[$name] = $value;
                continue;
            }
            if (( ! $class->isIdentifier($name) || ! $class->isIdGeneratorIdentity()) && ($name !== $class->versionField)) {
                $actualData[$name] = $value;
            }
        }
        if ( ! isset($this->originalEntityData[$oid])) {
                                    $this->originalEntityData[$oid] = $actualData;
            $changeSet = array();
            foreach ($actualData as $propName => $actualValue) {
                if ( ! isset($class->associationMappings[$propName])) {
                    $changeSet[$propName] = array(null, $actualValue);
                    continue;
                }
                $assoc = $class->associationMappings[$propName];
                if ($assoc['isOwningSide'] && $assoc['type'] & ClassMetadata::TO_ONE) {
                    $changeSet[$propName] = array(null, $actualValue);
                }
            }
            $this->entityChangeSets[$oid] = $changeSet;
        } else {
                                    $originalData           = $this->originalEntityData[$oid];
            $isChangeTrackingNotify = $class->isChangeTrackingNotify();
            $changeSet              = ($isChangeTrackingNotify && isset($this->entityChangeSets[$oid]))
                ? $this->entityChangeSets[$oid]
                : array();
            foreach ($actualData as $propName => $actualValue) {
                                if ( ! (isset($originalData[$propName]) || array_key_exists($propName, $originalData))) {
                    continue;
                }
                $orgValue = $originalData[$propName];
                                if ($orgValue === $actualValue) {
                    continue;
                }
                                if ( ! isset($class->associationMappings[$propName])) {
                    if ($isChangeTrackingNotify) {
                        continue;
                    }
                    $changeSet[$propName] = array($orgValue, $actualValue);
                    continue;
                }
                $assoc = $class->associationMappings[$propName];
                                                                if ($actualValue instanceof PersistentCollection) {
                    $owner = $actualValue->getOwner();
                    if ($owner === null) {                         $actualValue->setOwner($entity, $assoc);
                    } else if ($owner !== $entity) {                         if (!$actualValue->isInitialized()) {
                            $actualValue->initialize();                         }
                        $newValue = clone $actualValue;
                        $newValue->setOwner($entity, $assoc);
                        $class->reflFields[$propName]->setValue($entity, $newValue);
                    }
                }
                if ($orgValue instanceof PersistentCollection) {
                                        $coid = spl_object_hash($orgValue);
                    if (isset($this->collectionDeletions[$coid])) {
                        continue;
                    }
                    $this->collectionDeletions[$coid] = $orgValue;
                    $changeSet[$propName] = $orgValue;
                    continue;
                }
                if ($assoc['type'] & ClassMetadata::TO_ONE) {
                    if ($assoc['isOwningSide']) {
                        $changeSet[$propName] = array($orgValue, $actualValue);
                    }
                    if ($orgValue !== null && $assoc['orphanRemoval']) {
                        $this->scheduleOrphanRemoval($orgValue);
                    }
                }
            }
            if ($changeSet) {
                $this->entityChangeSets[$oid]   = $changeSet;
                $this->originalEntityData[$oid] = $actualData;
                $this->entityUpdates[$oid]      = $entity;
            }
        }
                foreach ($class->associationMappings as $field => $assoc) {
            if (($val = $class->reflFields[$field]->getValue($entity)) !== null) {
                $this->computeAssociationChanges($assoc, $val);
                if (!isset($this->entityChangeSets[$oid]) &&
                    $assoc['isOwningSide'] &&
                    $assoc['type'] == ClassMetadata::MANY_TO_MANY &&
                    $val instanceof PersistentCollection &&
                    $val->isDirty()) {
                    $this->entityChangeSets[$oid]   = array();
                    $this->originalEntityData[$oid] = $actualData;
                    $this->entityUpdates[$oid]      = $entity;
                }
            }
        }
    }
    public function computeChangeSets()
    {
                $this->computeScheduleInsertsChangeSets();
                foreach ($this->identityMap as $className => $entities) {
            $class = $this->em->getClassMetadata($className);
                        if ($class->isReadOnly) {
                continue;
            }
                                    switch (true) {
                case ($class->isChangeTrackingDeferredImplicit()):
                    $entitiesToProcess = $entities;
                    break;
                case (isset($this->scheduledForDirtyCheck[$className])):
                    $entitiesToProcess = $this->scheduledForDirtyCheck[$className];
                    break;
                default:
                    $entitiesToProcess = array();
            }
            foreach ($entitiesToProcess as $entity) {
                                if ($entity instanceof Proxy && ! $entity->__isInitialized__) {
                    continue;
                }
                                $oid = spl_object_hash($entity);
                if ( ! isset($this->entityInsertions[$oid]) && isset($this->entityStates[$oid])) {
                    $this->computeChangeSet($class, $entity);
                }
            }
        }
    }
    protected function computeAssociationChanges($assoc, $value)
    {
        if ($value instanceof Proxy && ! $value->__isInitialized__) {
            return;
        }
        if ($value instanceof PersistentCollection && $value->isDirty()) {
            $coid = spl_object_hash($value);
            if ($assoc['isOwningSide']) {
                $this->collectionUpdates[$coid] = $value;
            }
            $this->visitedCollections[$coid] = $value;
        }
                                $unwrappedValue = ($assoc['type'] & ClassMetadata::TO_ONE) ? array($value) : $value->unwrap();
        $targetClass    = $this->em->getClassMetadata($assoc['targetEntity']);
        foreach ($unwrappedValue as $key => $entry) {
            $state = $this->getEntityState($entry, self::STATE_NEW);
            if ( ! ($entry instanceof $assoc['targetEntity'])) {
                throw new ORMException(
                    sprintf(
                        'Found entity of type %s on association %s#%s, but expecting %s',
                        get_class($entry),
                        $assoc['sourceEntity'],
                        $assoc['fieldName'],
                        $targetClass->name
                    )
                );
            }
            switch ($state) {
                case self::STATE_NEW:
                    if ( ! $assoc['isCascadePersist']) {
                        throw ORMInvalidArgumentException::newEntityFoundThroughRelationship($assoc, $entry);
                    }
                    $this->persistNew($targetClass, $entry);
                    $this->computeChangeSet($targetClass, $entry);
                    break;
                case self::STATE_REMOVED:
                                                            if ($assoc['type'] & ClassMetadata::TO_MANY) {
                        unset($value[$key]);
                    }
                    break;
                case self::STATE_DETACHED:
                                                            throw ORMInvalidArgumentException::detachedEntityFoundThroughRelationship($assoc, $entry);
                    break;
                default:
                                                    }
        }
    }
    protected function persistNew($class, $entity)
    {
        $oid    = spl_object_hash($entity);
        $invoke = $this->listenersInvoker->getSubscribedSystems($class, Events::prePersist);
        if ($invoke !== ListenersInvoker::INVOKE_NONE) {
            $this->listenersInvoker->invoke($class, Events::prePersist, $entity, new LifecycleEventArgs($entity, $this->em), $invoke);
        }
        $idGen = $class->idGenerator;
        if ( ! $idGen->isPostInsertGenerator()) {
            $idValue = $idGen->generate($this->em, $entity);
            if ( ! $idGen instanceof \Doctrine\ORM\Id\AssignedGenerator) {
                $idValue = array($class->identifier[0] => $idValue);
                $class->setIdentifierValues($entity, $idValue);
            }
            $this->entityIdentifiers[$oid] = $idValue;
        }
        $this->entityStates[$oid] = self::STATE_MANAGED;
        $this->scheduleForInsert($entity);
    }
    public function recomputeSingleEntityChangeSet(ClassMetadata $class, $entity)
    {
        $oid = spl_object_hash($entity);
        if ( ! isset($this->entityStates[$oid]) || $this->entityStates[$oid] != self::STATE_MANAGED) {
            throw ORMInvalidArgumentException::entityNotManaged($entity);
        }
                if ($class->isChangeTrackingNotify()) {
            return;
        }
        if ( ! $class->isInheritanceTypeNone()) {
            $class = $this->em->getClassMetadata(get_class($entity));
        }
        $actualData = array();
        foreach ($class->reflFields as $name => $refProp) {
            if (( ! $class->isIdentifier($name) || ! $class->isIdGeneratorIdentity())
                && ($name !== $class->versionField)
                && ! $class->isCollectionValuedAssociation($name)) {
                $actualData[$name] = $refProp->getValue($entity);
            }
        }
        if ( ! isset($this->originalEntityData[$oid])) {
            throw new \RuntimeException('Cannot call recomputeSingleEntityChangeSet before computeChangeSet on an entity.');
        }
        $originalData = $this->originalEntityData[$oid];
        $changeSet = array();
        foreach ($actualData as $propName => $actualValue) {
            $orgValue = isset($originalData[$propName]) ? $originalData[$propName] : null;
            if ($orgValue !== $actualValue) {
                $changeSet[$propName] = array($orgValue, $actualValue);
            }
        }
        if ($changeSet) {
            if (isset($this->entityChangeSets[$oid])) {
                $this->entityChangeSets[$oid] = array_merge($this->entityChangeSets[$oid], $changeSet);
            } else if ( ! isset($this->entityInsertions[$oid])) {
                $this->entityChangeSets[$oid] = $changeSet;
                $this->entityUpdates[$oid]    = $entity;
            }
            $this->originalEntityData[$oid] = $actualData;
        }
    }
    protected function executeInserts($class)
    {
        $entities   = array();
        $className  = $class->name;
        $persister  = $this->getEntityPersister($className);
        $invoke     = $this->listenersInvoker->getSubscribedSystems($class, Events::postPersist);
        foreach ($this->entityInsertions as $oid => $entity) {
            if ($this->em->getClassMetadata(get_class($entity))->name !== $className) {
                continue;
            }
            $persister->addInsert($entity);
            unset($this->entityInsertions[$oid]);
            if ($invoke !== ListenersInvoker::INVOKE_NONE) {
                $entities[] = $entity;
            }
        }
        $postInsertIds = $persister->executeInserts();
        if ($postInsertIds) {
                        foreach ($postInsertIds as $id => $entity) {
                $oid     = spl_object_hash($entity);
                $idField = $class->identifier[0];
                $class->reflFields[$idField]->setValue($entity, $id);
                $this->entityIdentifiers[$oid] = array($idField => $id);
                $this->entityStates[$oid] = self::STATE_MANAGED;
                $this->originalEntityData[$oid][$idField] = $id;
                $this->addToIdentityMap($entity);
            }
        }
        foreach ($entities as $entity) {
            $this->listenersInvoker->invoke($class, Events::postPersist, $entity, new LifecycleEventArgs($entity, $this->em), $invoke);
        }
    }
    protected function executeUpdates($class)
    {
        $className          = $class->name;
        $persister          = $this->getEntityPersister($className);
        $preUpdateInvoke    = $this->listenersInvoker->getSubscribedSystems($class, Events::preUpdate);
        $postUpdateInvoke   = $this->listenersInvoker->getSubscribedSystems($class, Events::postUpdate);
        foreach ($this->entityUpdates as $oid => $entity) {
            if ($this->em->getClassMetadata(get_class($entity))->name !== $className) {
                continue;
            }
            if ($preUpdateInvoke != ListenersInvoker::INVOKE_NONE) {
                $this->listenersInvoker->invoke($class, Events::preUpdate, $entity, new PreUpdateEventArgs($entity, $this->em, $this->entityChangeSets[$oid]), $preUpdateInvoke);
                $this->recomputeSingleEntityChangeSet($class, $entity);
            }
            if ( ! empty($this->entityChangeSets[$oid])) {
                $persister->update($entity);
            }
            unset($this->entityUpdates[$oid]);
            if ($postUpdateInvoke != ListenersInvoker::INVOKE_NONE) {
                $this->listenersInvoker->invoke($class, Events::postUpdate, $entity, new LifecycleEventArgs($entity, $this->em), $postUpdateInvoke);
            }
        }
    }
    protected function executeDeletions($class)
    {
        $className  = $class->name;
        $persister  = $this->getEntityPersister($className);
        $invoke     = $this->listenersInvoker->getSubscribedSystems($class, Events::postRemove);
        foreach ($this->entityDeletions as $oid => $entity) {
            if ($this->em->getClassMetadata(get_class($entity))->name !== $className) {
                continue;
            }
            $persister->delete($entity);
            unset(
                $this->entityDeletions[$oid],
                $this->entityIdentifiers[$oid],
                $this->originalEntityData[$oid],
                $this->entityStates[$oid]
            );
                                                if ( ! $class->isIdentifierNatural()) {
                $class->reflFields[$class->identifier[0]]->setValue($entity, null);
            }
            if ($invoke !== ListenersInvoker::INVOKE_NONE) {
                $this->listenersInvoker->invoke($class, Events::postRemove, $entity, new LifecycleEventArgs($entity, $this->em), $invoke);
            }
        }
    }
    protected function getCommitOrder(array $entityChangeSet = null)
    {
        if ($entityChangeSet === null) {
            $entityChangeSet = array_merge($this->entityInsertions, $this->entityUpdates, $this->entityDeletions);
        }
        $calc = $this->getCommitOrderCalculator();
                                                $newNodes = array();
        foreach ($entityChangeSet as $entity) {
            $className = $this->em->getClassMetadata(get_class($entity))->name;
            if ($calc->hasClass($className)) {
                continue;
            }
            $class = $this->em->getClassMetadata($className);
            $calc->addClass($class);
            $newNodes[] = $class;
        }
                while ($class = array_pop($newNodes)) {
            foreach ($class->associationMappings as $assoc) {
                if ( ! ($assoc['isOwningSide'] && $assoc['type'] & ClassMetadata::TO_ONE)) {
                    continue;
                }
                $targetClass = $this->em->getClassMetadata($assoc['targetEntity']);
                if ( ! $calc->hasClass($targetClass->name)) {
                    $calc->addClass($targetClass);
                    $newNodes[] = $targetClass;
                }
                $calc->addDependency($targetClass, $class);
                                if ( ! $targetClass->subClasses) {
                    continue;
                }
                foreach ($targetClass->subClasses as $subClassName) {
                    $targetSubClass = $this->em->getClassMetadata($subClassName);
                    if ( ! $calc->hasClass($subClassName)) {
                        $calc->addClass($targetSubClass);
                        $newNodes[] = $targetSubClass;
                    }
                    $calc->addDependency($targetSubClass, $class);
                }
            }
        }
        return $calc->getCommitOrder();
    }
    public function scheduleForInsert($entity)
    {
        $oid = spl_object_hash($entity);
        if (isset($this->entityUpdates[$oid])) {
            throw new InvalidArgumentException("Dirty entity can not be scheduled for insertion.");
        }
        if (isset($this->entityDeletions[$oid])) {
            throw ORMInvalidArgumentException::scheduleInsertForRemovedEntity($entity);
        }
        if (isset($this->originalEntityData[$oid]) && ! isset($this->entityInsertions[$oid])) {
            throw ORMInvalidArgumentException::scheduleInsertForManagedEntity($entity);
        }
        if (isset($this->entityInsertions[$oid])) {
            throw ORMInvalidArgumentException::scheduleInsertTwice($entity);
        }
        $this->entityInsertions[$oid] = $entity;
        if (isset($this->entityIdentifiers[$oid])) {
            $this->addToIdentityMap($entity);
        }
        if ($entity instanceof NotifyPropertyChanged) {
            $entity->addPropertyChangedListener($this);
        }
    }
    public function isScheduledForInsert($entity)
    {
        return isset($this->entityInsertions[spl_object_hash($entity)]);
    }
    public function scheduleForUpdate($entity)
    {
        $oid = spl_object_hash($entity);
        if ( ! isset($this->entityIdentifiers[$oid])) {
            throw ORMInvalidArgumentException::entityHasNoIdentity($entity, "scheduling for update");
        }
        if (isset($this->entityDeletions[$oid])) {
            throw ORMInvalidArgumentException::entityIsRemoved($entity, "schedule for update");
        }
        if ( ! isset($this->entityUpdates[$oid]) && ! isset($this->entityInsertions[$oid])) {
            $this->entityUpdates[$oid] = $entity;
        }
    }
    public function scheduleExtraUpdate($entity, array $changeset)
    {
        $oid         = spl_object_hash($entity);
        $extraUpdate = array($entity, $changeset);
        if (isset($this->extraUpdates[$oid])) {
            list($ignored, $changeset2) = $this->extraUpdates[$oid];
            $extraUpdate = array($entity, $changeset + $changeset2);
        }
        $this->extraUpdates[$oid] = $extraUpdate;
    }
    public function isScheduledForUpdate($entity)
    {
        return isset($this->entityUpdates[spl_object_hash($entity)]);
    }
    public function isScheduledForDirtyCheck($entity)
    {
        $rootEntityName = $this->em->getClassMetadata(get_class($entity))->rootEntityName;
        return isset($this->scheduledForDirtyCheck[$rootEntityName][spl_object_hash($entity)]);
    }
    public function scheduleForDelete($entity)
    {
        $oid = spl_object_hash($entity);
        if (isset($this->entityInsertions[$oid])) {
            if ($this->isInIdentityMap($entity)) {
                $this->removeFromIdentityMap($entity);
            }
            unset($this->entityInsertions[$oid], $this->entityStates[$oid]);
            return;         }
        if ( ! $this->isInIdentityMap($entity)) {
            return;
        }
        $this->removeFromIdentityMap($entity);
        if (isset($this->entityUpdates[$oid])) {
            unset($this->entityUpdates[$oid]);
        }
        if ( ! isset($this->entityDeletions[$oid])) {
            $this->entityDeletions[$oid] = $entity;
            $this->entityStates[$oid]    = self::STATE_REMOVED;
        }
    }
    public function isScheduledForDelete($entity)
    {
        return isset($this->entityDeletions[spl_object_hash($entity)]);
    }
    public function isEntityScheduled($entity)
    {
        $oid = spl_object_hash($entity);
        return isset($this->entityInsertions[$oid])
            || isset($this->entityUpdates[$oid])
            || isset($this->entityDeletions[$oid]);
    }
    public function addToIdentityMap($entity)
    {
        $classMetadata = $this->em->getClassMetadata(get_class($entity));
        $idHash        = implode(' ', $this->entityIdentifiers[spl_object_hash($entity)]);
        if ($idHash === '') {
            throw ORMInvalidArgumentException::entityWithoutIdentity($classMetadata->name, $entity);
        }
        $className = $classMetadata->rootEntityName;
        if (isset($this->identityMap[$className][$idHash])) {
            return false;
        }
        $this->identityMap[$className][$idHash] = $entity;
        return true;
    }
    public function getEntityState($entity, $assume = null)
    {
        $oid = spl_object_hash($entity);
        if (isset($this->entityStates[$oid])) {
            return $this->entityStates[$oid];
        }
        if ($assume !== null) {
            return $assume;
        }
                                        $class = $this->em->getClassMetadata(get_class($entity));
        $id    = $class->getIdentifierValues($entity);
        if ( ! $id) {
            return self::STATE_NEW;
        }
        if ($class->containsForeignIdentifier) {
            $id = $this->flattenIdentifier($class, $id);
        }
        switch (true) {
            case ($class->isIdentifierNatural());
                                if ($class->isVersioned) {
                    return ($class->getFieldValue($entity, $class->versionField))
                        ? self::STATE_DETACHED
                        : self::STATE_NEW;
                }
                                if ($this->tryGetById($id, $class->rootEntityName)) {
                    return self::STATE_DETACHED;
                }
                                if ($this->getEntityPersister($class->name)->exists($entity)) {
                    return self::STATE_DETACHED;
                }
                return self::STATE_NEW;
            case ( ! $class->idGenerator->isPostInsertGenerator()):
                                if ($this->tryGetById($id, $class->rootEntityName)) {
                    return self::STATE_DETACHED;
                }
                                if ($this->getEntityPersister($class->name)->exists($entity)) {
                    return self::STATE_DETACHED;
                }
                return self::STATE_NEW;
            default:
                return self::STATE_DETACHED;
        }
    }
    public function removeFromIdentityMap($entity)
    {
        $oid           = spl_object_hash($entity);
        $classMetadata = $this->em->getClassMetadata(get_class($entity));
        $idHash        = implode(' ', $this->entityIdentifiers[$oid]);
        if ($idHash === '') {
            throw ORMInvalidArgumentException::entityHasNoIdentity($entity, "remove from identity map");
        }
        $className = $classMetadata->rootEntityName;
        if (isset($this->identityMap[$className][$idHash])) {
            unset($this->identityMap[$className][$idHash]);
            unset($this->readOnlyObjects[$oid]);
            return true;
        }
        return false;
    }
    public function getByIdHash($idHash, $rootClassName)
    {
        return $this->identityMap[$rootClassName][$idHash];
    }
    public function tryGetByIdHash($idHash, $rootClassName)
    {
        if (isset($this->identityMap[$rootClassName][$idHash])) {
            return $this->identityMap[$rootClassName][$idHash];
        }
        return false;
    }
    public function isInIdentityMap($entity)
    {
        $oid = spl_object_hash($entity);
        if ( ! isset($this->entityIdentifiers[$oid])) {
            return false;
        }
        $classMetadata = $this->em->getClassMetadata(get_class($entity));
        $idHash        = implode(' ', $this->entityIdentifiers[$oid]);
        if ($idHash === '') {
            return false;
        }
        return isset($this->identityMap[$classMetadata->rootEntityName][$idHash]);
    }
    public function containsIdHash($idHash, $rootClassName)
    {
        return isset($this->identityMap[$rootClassName][$idHash]);
    }
    public function persist($entity)
    {
        $visited = array();
        $this->doPersist($entity, $visited);
    }
    protected function doPersist($entity, array &$visited)
    {
        $oid = spl_object_hash($entity);
        if (isset($visited[$oid])) {
            return;         }
        $visited[$oid] = $entity;
        $class = $this->em->getClassMetadata(get_class($entity));
                                        $entityState = $this->getEntityState($entity, self::STATE_NEW);
        switch ($entityState) {
            case self::STATE_MANAGED:
                                if ($class->isChangeTrackingDeferredExplicit()) {
                    $this->scheduleForDirtyCheck($entity);
                }
                break;
            case self::STATE_NEW:
                $this->persistNew($class, $entity);
                break;
            case self::STATE_REMOVED:
                                unset($this->entityDeletions[$oid]);
                $this->entityStates[$oid] = self::STATE_MANAGED;
                break;
            case self::STATE_DETACHED:
                                throw ORMInvalidArgumentException::detachedEntityCannot($entity, "persisted");
            default:
                throw new UnexpectedValueException("Unexpected entity state: $entityState." . self::objToStr($entity));
        }
        $this->cascadePersist($entity, $visited);
    }
    public function remove($entity)
    {
        $visited = array();
        $this->doRemove($entity, $visited);
    }
    protected function doRemove($entity, array &$visited)
    {
        $oid = spl_object_hash($entity);
        if (isset($visited[$oid])) {
            return;         }
        $visited[$oid] = $entity;
                        $this->cascadeRemove($entity, $visited);
        $class       = $this->em->getClassMetadata(get_class($entity));
        $entityState = $this->getEntityState($entity);
        switch ($entityState) {
            case self::STATE_NEW:
            case self::STATE_REMOVED:
                                break;
            case self::STATE_MANAGED:
                $invoke = $this->listenersInvoker->getSubscribedSystems($class, Events::preRemove);
                if ($invoke !== ListenersInvoker::INVOKE_NONE) {
                    $this->listenersInvoker->invoke($class, Events::preRemove, $entity, new LifecycleEventArgs($entity, $this->em), $invoke);
                }
                $this->scheduleForDelete($entity);
                break;
            case self::STATE_DETACHED:
                throw ORMInvalidArgumentException::detachedEntityCannot($entity, "removed");
            default:
                throw new UnexpectedValueException("Unexpected entity state: $entityState." . self::objToStr($entity));
        }
    }
    public function merge($entity)
    {
        $visited = array();
        return $this->doMerge($entity, $visited);
    }
    protected function flattenIdentifier($class, $id)
    {
        $flatId = array();
        foreach ($id as $idField => $idValue) {
            if (isset($class->associationMappings[$idField])) {
                $targetClassMetadata = $this->em->getClassMetadata($class->associationMappings[$idField]['targetEntity']);
                $associatedId        = $this->getEntityIdentifier($idValue);
                $flatId[$idField] = $associatedId[$targetClassMetadata->identifier[0]];
            } else {
                $flatId[$idField] = $idValue;
            }
        }
        return $flatId;
    }
    protected function doMerge($entity, array &$visited, $prevManagedCopy = null, $assoc = null)
    {
        $oid = spl_object_hash($entity);
        if (isset($visited[$oid])) {
            return $visited[$oid];         }
        $visited[$oid] = $entity;
        $class = $this->em->getClassMetadata(get_class($entity));
                                        $managedCopy = $entity;
        if ($this->getEntityState($entity, self::STATE_DETACHED) !== self::STATE_MANAGED) {
            if ($entity instanceof Proxy && ! $entity->__isInitialized()) {
                $this->em->getProxyFactory()->resetUninitializedProxy($entity);
                $entity->__load();
            }
                        $id = $class->getIdentifierValues($entity);
                        if ( ! $id) {
                $managedCopy = $this->newInstance($class);
                $this->persistNew($class, $managedCopy);
            } else {
                $flatId = ($class->containsForeignIdentifier)
                    ? $this->flattenIdentifier($class, $id)
                    : $id;
                $managedCopy = $this->tryGetById($flatId, $class->rootEntityName);
                if ($managedCopy) {
                                        if ($this->getEntityState($managedCopy) == self::STATE_REMOVED) {
                        throw ORMInvalidArgumentException::entityIsRemoved($managedCopy, "merge");
                    }
                } else {
                                        $managedCopy = $this->em->find($class->name, $flatId);
                }
                if ($managedCopy === null) {
                                                            if ( ! $class->isIdentifierNatural()) {
                        throw new EntityNotFoundException;
                    }
                    $managedCopy = $this->newInstance($class);
                    $class->setIdentifierValues($managedCopy, $id);
                    $this->persistNew($class, $managedCopy);
                } else {
                    if ($managedCopy instanceof Proxy && ! $managedCopy->__isInitialized__) {
                        $managedCopy->__load();
                    }
                }
            }
            if ($class->isVersioned) {
                $reflField          = $class->reflFields[$class->versionField];
                $managedCopyVersion = $reflField->getValue($managedCopy);
                $entityVersion      = $reflField->getValue($entity);
                                if ($managedCopyVersion != $entityVersion) {
                    throw OptimisticLockException::lockFailedVersionMismatch($entity, $entityVersion, $managedCopyVersion);
                }
            }
                        foreach ($class->reflClass->getProperties() as $prop) {
                $name = $prop->name;
                $prop->setAccessible(true);
                if ( ! isset($class->associationMappings[$name])) {
                    if ( ! $class->isIdentifier($name)) {
                        $prop->setValue($managedCopy, $prop->getValue($entity));
                    }
                } else {
                    $assoc2 = $class->associationMappings[$name];
                    if ($assoc2['type'] & ClassMetadata::TO_ONE) {
                        $other = $prop->getValue($entity);
                        if ($other === null) {
                            $prop->setValue($managedCopy, null);
                        } else if ($other instanceof Proxy && !$other->__isInitialized__) {
                                                        continue;
                        } else if ( ! $assoc2['isCascadeMerge']) {
                            if ($this->getEntityState($other) === self::STATE_DETACHED) {
                                $targetClass = $this->em->getClassMetadata($assoc2['targetEntity']);
                                $relatedId = $targetClass->getIdentifierValues($other);
                                if ($targetClass->subClasses) {
                                    $other = $this->em->find($targetClass->name, $relatedId);
                                } else {
                                    $other = $this->em->getProxyFactory()->getProxy($assoc2['targetEntity'], $relatedId);
                                    $this->registerManaged($other, $relatedId, array());
                                }
                            }
                            $prop->setValue($managedCopy, $other);
                        }
                    } else {
                        $mergeCol = $prop->getValue($entity);
                        if ($mergeCol instanceof PersistentCollection && !$mergeCol->isInitialized()) {
                                                                                    continue;
                        }
                        $managedCol = $prop->getValue($managedCopy);
                        if (!$managedCol) {
                            $managedCol = new PersistentCollection($this->em,
                                    $this->em->getClassMetadata($assoc2['targetEntity']),
                                    new ArrayCollection
                                    );
                            $managedCol->setOwner($managedCopy, $assoc2);
                            $prop->setValue($managedCopy, $managedCol);
                            $this->originalEntityData[$oid][$name] = $managedCol;
                        }
                        if ($assoc2['isCascadeMerge']) {
                            $managedCol->initialize();
                                                        if (!$managedCol->isEmpty() && $managedCol !== $mergeCol) {
                                $managedCol->unwrap()->clear();
                                $managedCol->setDirty(true);
                                if ($assoc2['isOwningSide'] && $assoc2['type'] == ClassMetadata::MANY_TO_MANY && $class->isChangeTrackingNotify()) {
                                    $this->scheduleForDirtyCheck($managedCopy);
                                }
                            }
                        }
                    }
                }
                if ($class->isChangeTrackingNotify()) {
                                        $this->propertyChanged($managedCopy, $name, null, $prop->getValue($managedCopy));
                }
            }
            if ($class->isChangeTrackingDeferredExplicit()) {
                $this->scheduleForDirtyCheck($entity);
            }
        }
        if ($prevManagedCopy !== null) {
            $assocField = $assoc['fieldName'];
            $prevClass = $this->em->getClassMetadata(get_class($prevManagedCopy));
            if ($assoc['type'] & ClassMetadata::TO_ONE) {
                $prevClass->reflFields[$assocField]->setValue($prevManagedCopy, $managedCopy);
            } else {
                $prevClass->reflFields[$assocField]->getValue($prevManagedCopy)->add($managedCopy);
                if ($assoc['type'] == ClassMetadata::ONE_TO_MANY) {
                    $class->reflFields[$assoc['mappedBy']]->setValue($managedCopy, $prevManagedCopy);
                }
            }
        }
                $visited[spl_object_hash($managedCopy)] = true;
        $this->cascadeMerge($entity, $managedCopy, $visited);
        return $managedCopy;
    }
    public function detach($entity)
    {
        $visited = array();
        $this->doDetach($entity, $visited);
    }
    protected function doDetach($entity, array &$visited, $noCascade = false)
    {
        $oid = spl_object_hash($entity);
        if (isset($visited[$oid])) {
            return;         }
        $visited[$oid] = $entity;
        switch ($this->getEntityState($entity, self::STATE_DETACHED)) {
            case self::STATE_MANAGED:
                if ($this->isInIdentityMap($entity)) {
                    $this->removeFromIdentityMap($entity);
                }
                unset(
                    $this->entityInsertions[$oid],
                    $this->entityUpdates[$oid],
                    $this->entityDeletions[$oid],
                    $this->entityIdentifiers[$oid],
                    $this->entityStates[$oid],
                    $this->originalEntityData[$oid]
                );
                break;
            case self::STATE_NEW:
            case self::STATE_DETACHED:
                return;
        }
        if ( ! $noCascade) {
            $this->cascadeDetach($entity, $visited);
        }
    }
    public function refresh($entity)
    {
        $visited = array();
        $this->doRefresh($entity, $visited);
    }
    protected function doRefresh($entity, array &$visited)
    {
        $oid = spl_object_hash($entity);
        if (isset($visited[$oid])) {
            return;         }
        $visited[$oid] = $entity;
        $class = $this->em->getClassMetadata(get_class($entity));
        if ($this->getEntityState($entity) !== self::STATE_MANAGED) {
            throw ORMInvalidArgumentException::entityNotManaged($entity);
        }
        $this->getEntityPersister($class->name)->refresh(
            array_combine($class->getIdentifierFieldNames(), $this->entityIdentifiers[$oid]),
            $entity
        );
        $this->cascadeRefresh($entity, $visited);
    }
    protected function cascadeRefresh($entity, array &$visited)
    {
        $class = $this->em->getClassMetadata(get_class($entity));
        $associationMappings = array_filter(
            $class->associationMappings,
            function ($assoc) { return $assoc['isCascadeRefresh']; }
        );
        foreach ($associationMappings as $assoc) {
            $relatedEntities = $class->reflFields[$assoc['fieldName']]->getValue($entity);
            switch (true) {
                case ($relatedEntities instanceof PersistentCollection):
                                        $relatedEntities = $relatedEntities->unwrap();
                case ($relatedEntities instanceof Collection):
                case (is_array($relatedEntities)):
                    foreach ($relatedEntities as $relatedEntity) {
                        $this->doRefresh($relatedEntity, $visited);
                    }
                    break;
                case ($relatedEntities !== null):
                    $this->doRefresh($relatedEntities, $visited);
                    break;
                default:
                                }
        }
    }
    protected function cascadeDetach($entity, array &$visited)
    {
        $class = $this->em->getClassMetadata(get_class($entity));
        $associationMappings = array_filter(
            $class->associationMappings,
            function ($assoc) { return $assoc['isCascadeDetach']; }
        );
        foreach ($associationMappings as $assoc) {
            $relatedEntities = $class->reflFields[$assoc['fieldName']]->getValue($entity);
            switch (true) {
                case ($relatedEntities instanceof PersistentCollection):
                                        $relatedEntities = $relatedEntities->unwrap();
                case ($relatedEntities instanceof Collection):
                case (is_array($relatedEntities)):
                    foreach ($relatedEntities as $relatedEntity) {
                        $this->doDetach($relatedEntity, $visited);
                    }
                    break;
                case ($relatedEntities !== null):
                    $this->doDetach($relatedEntities, $visited);
                    break;
                default:
                                }
        }
    }
    protected function cascadeMerge($entity, $managedCopy, array &$visited)
    {
        $class = $this->em->getClassMetadata(get_class($entity));
        $associationMappings = array_filter(
            $class->associationMappings,
            function ($assoc) { return $assoc['isCascadeMerge']; }
        );
        foreach ($associationMappings as $assoc) {
            $relatedEntities = $class->reflFields[$assoc['fieldName']]->getValue($entity);
            if ($relatedEntities instanceof Collection) {
                if ($relatedEntities === $class->reflFields[$assoc['fieldName']]->getValue($managedCopy)) {
                    continue;
                }
                if ($relatedEntities instanceof PersistentCollection) {
                                        $relatedEntities = $relatedEntities->unwrap();
                }
                foreach ($relatedEntities as $relatedEntity) {
                    $this->doMerge($relatedEntity, $visited, $managedCopy, $assoc);
                }
            } else if ($relatedEntities !== null) {
                $this->doMerge($relatedEntities, $visited, $managedCopy, $assoc);
            }
        }
    }
    protected function cascadePersist($entity, array &$visited)
    {
        $class = $this->em->getClassMetadata(get_class($entity));
        $associationMappings = array_filter(
            $class->associationMappings,
            function ($assoc) { return $assoc['isCascadePersist']; }
        );
        foreach ($associationMappings as $assoc) {
            $relatedEntities = $class->reflFields[$assoc['fieldName']]->getValue($entity);
            switch (true) {
                case ($relatedEntities instanceof PersistentCollection):
                                        $relatedEntities = $relatedEntities->unwrap();
                case ($relatedEntities instanceof Collection):
                case (is_array($relatedEntities)):
                    foreach ($relatedEntities as $relatedEntity) {
                        $this->doPersist($relatedEntity, $visited);
                    }
                    break;
                case ($relatedEntities !== null):
                    $this->doPersist($relatedEntities, $visited);
                    break;
                default:
                                }
        }
    }
    protected function cascadeRemove($entity, array &$visited)
    {
        $class = $this->em->getClassMetadata(get_class($entity));
        $associationMappings = array_filter(
            $class->associationMappings,
            function ($assoc) { return $assoc['isCascadeRemove']; }
        );
        $entitiesToCascade = array();
        foreach ($associationMappings as $assoc) {
            if ($entity instanceof Proxy && !$entity->__isInitialized__) {
                $entity->__load();
            }
            $relatedEntities = $class->reflFields[$assoc['fieldName']]->getValue($entity);
            switch (true) {
                case ($relatedEntities instanceof Collection):
                case (is_array($relatedEntities)):
                                        foreach ($relatedEntities as $relatedEntity) {
                        $entitiesToCascade[] = $relatedEntity;
                    }
                    break;
                case ($relatedEntities !== null):
                    $entitiesToCascade[] = $relatedEntities;
                    break;
                default:
                                }
        }
        foreach ($entitiesToCascade as $relatedEntity) {
            $this->doRemove($relatedEntity, $visited);
        }
    }
    public function lock($entity, $lockMode, $lockVersion = null)
    {
        if ($entity === null) {
            throw new \InvalidArgumentException("No entity passed to UnitOfWork#lock().");
        }
        if ($this->getEntityState($entity, self::STATE_DETACHED) != self::STATE_MANAGED) {
            throw ORMInvalidArgumentException::entityNotManaged($entity);
        }
        $class = $this->em->getClassMetadata(get_class($entity));
        switch ($lockMode) {
            case \Doctrine\DBAL\LockMode::OPTIMISTIC;
                if ( ! $class->isVersioned) {
                    throw OptimisticLockException::notVersioned($class->name);
                }
                if ($lockVersion === null) {
                    return;
                }
                $entityVersion = $class->reflFields[$class->versionField]->getValue($entity);
                if ($entityVersion != $lockVersion) {
                    throw OptimisticLockException::lockFailedVersionMismatch($entity, $lockVersion, $entityVersion);
                }
                break;
            case \Doctrine\DBAL\LockMode::PESSIMISTIC_READ:
            case \Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE:
                if (!$this->em->getConnection()->isTransactionActive()) {
                    throw TransactionRequiredException::transactionRequired();
                }
                $oid = spl_object_hash($entity);
                $this->getEntityPersister($class->name)->lock(
                    array_combine($class->getIdentifierFieldNames(), $this->entityIdentifiers[$oid]),
                    $lockMode
                );
                break;
            default:
                        }
    }
    public function getCommitOrderCalculator()
    {
        if ($this->commitOrderCalculator === null) {
            $this->commitOrderCalculator = new Internal\CommitOrderCalculator;
        }
        return $this->commitOrderCalculator;
    }
    public function clear($entityName = null)
    {
        if ($entityName === null) {
            $this->identityMap =
            $this->entityIdentifiers =
            $this->originalEntityData =
            $this->entityChangeSets =
            $this->entityStates =
            $this->scheduledForDirtyCheck =
            $this->entityInsertions =
            $this->entityUpdates =
            $this->entityDeletions =
            $this->collectionDeletions =
            $this->collectionUpdates =
            $this->extraUpdates =
            $this->readOnlyObjects =
            $this->visitedCollections =
            $this->orphanRemovals = array();
            if ($this->commitOrderCalculator !== null) {
                $this->commitOrderCalculator->clear();
            }
        } else {
            $visited = array();
            foreach ($this->identityMap as $className => $entities) {
                if ($className === $entityName) {
                    foreach ($entities as $entity) {
                        $this->doDetach($entity, $visited, true);
                    }
                }
            }
        }
        if ($this->evm->hasListeners(Events::onClear)) {
            $this->evm->dispatchEvent(Events::onClear, new Event\OnClearEventArgs($this->em, $entityName));
        }
    }
    public function scheduleOrphanRemoval($entity)
    {
        $this->orphanRemovals[spl_object_hash($entity)] = $entity;
    }
    public function scheduleCollectionDeletion(PersistentCollection $coll)
    {
        $coid = spl_object_hash($coll);
                        if (isset($this->collectionUpdates[$coid])) {
            unset($this->collectionUpdates[$coid]);
        }
        $this->collectionDeletions[$coid] = $coll;
    }
    public function isCollectionScheduledForDeletion(PersistentCollection $coll)
    {
        return isset($this->collectionDeletions[spl_object_hash($coll)]);
    }
    protected function newInstance($class)
    {
        $entity = $class->newInstance();
        if ($entity instanceof \Doctrine\Common\Persistence\ObjectManagerAware) {
            $entity->injectObjectManager($this->em, $class);
        }
        return $entity;
    }
    public function createEntity($className, array $data, &$hints = array())
    {
        $class = $this->em->getClassMetadata($className);
        if ($class->isIdentifierComposite) {
            $id = array();
            foreach ($class->identifier as $fieldName) {
                $id[$fieldName] = isset($class->associationMappings[$fieldName])
                    ? $data[$class->associationMappings[$fieldName]['joinColumns'][0]['name']]
                    : $data[$fieldName];
            }
        } else {
            $id = isset($class->associationMappings[$class->identifier[0]])
                ? $data[$class->associationMappings[$class->identifier[0]]['joinColumns'][0]['name']]
                : $data[$class->identifier[0]];
            $id = array($class->identifier[0] => $id);
        }
        $idHash = implode(' ', $id);
        if (isset($this->identityMap[$class->rootEntityName][$idHash])) {
            $entity = $this->identityMap[$class->rootEntityName][$idHash];
            $oid = spl_object_hash($entity);
            if (
                isset($hints[Query::HINT_REFRESH])
                && isset($hints[Query::HINT_REFRESH_ENTITY])
                && ($unmanagedProxy = $hints[Query::HINT_REFRESH_ENTITY]) !== $entity
                && $unmanagedProxy instanceof Proxy
                && $this->isIdentifierEquals($unmanagedProxy, $entity)
            ) {
                foreach ($class->identifier as $fieldName) {
                    $class->reflFields[$fieldName]->setValue($unmanagedProxy, null);
                }
                return $unmanagedProxy;
            }
            if ($entity instanceof Proxy && ! $entity->__isInitialized()) {
                $entity->__setInitialized(true);
                $overrideLocalValues = true;
                if ($entity instanceof NotifyPropertyChanged) {
                    $entity->addPropertyChangedListener($this);
                }
                                if ($overrideLocalValues && $entity instanceof ObjectManagerAware) {
                    $entity->injectObjectManager($this->em, $class);
                }
            } else {
                $overrideLocalValues = isset($hints[Query::HINT_REFRESH]);
                                if(isset($hints[Query::HINT_REFRESH_ENTITY])) {
                    $overrideLocalValues = $hints[Query::HINT_REFRESH_ENTITY] === $entity;
                }
                                if ($overrideLocalValues && $entity instanceof ObjectManagerAware) {
                    $entity->injectObjectManager($this->em, $class);
                }
            }
            if ($overrideLocalValues) {
                $this->originalEntityData[$oid] = $data;
            }
        } else {
            $entity = $this->newInstance($class);
            $oid    = spl_object_hash($entity);
            $this->entityIdentifiers[$oid]  = $id;
            $this->entityStates[$oid]       = self::STATE_MANAGED;
            $this->originalEntityData[$oid] = $data;
            $this->identityMap[$class->rootEntityName][$idHash] = $entity;
            if ($entity instanceof NotifyPropertyChanged) {
                $entity->addPropertyChangedListener($this);
            }
            $overrideLocalValues = true;
        }
        if ( ! $overrideLocalValues) {
            return $entity;
        }
        foreach ($data as $field => $value) {
            if (isset($class->fieldMappings[$field])) {
                $class->reflFields[$field]->setValue($entity, $value);
            }
        }
                unset($this->eagerLoadingEntities[$class->rootEntityName][$idHash]);
        if (isset($this->eagerLoadingEntities[$class->rootEntityName]) && ! $this->eagerLoadingEntities[$class->rootEntityName]) {
            unset($this->eagerLoadingEntities[$class->rootEntityName]);
        }
                if (isset($hints[Query::HINT_FORCE_PARTIAL_LOAD])) {
            return $entity;
        }
        foreach ($class->associationMappings as $field => $assoc) {
                        if (isset($hints['fetchAlias']) && isset($hints['fetched'][$hints['fetchAlias']][$field])) {
                continue;
            }
            $targetClass = $this->em->getClassMetadata($assoc['targetEntity']);
            switch (true) {
                case ($assoc['type'] & ClassMetadata::TO_ONE):
                    if ( ! $assoc['isOwningSide']) {
                                                $class->reflFields[$field]->setValue($entity, $this->getEntityPersister($assoc['targetEntity'])->loadOneToOneEntity($assoc, $entity));
                        continue 2;
                    }
                    $associatedId = array();
                                        foreach ($assoc['targetToSourceKeyColumns'] as $targetColumn => $srcColumn) {
                        $joinColumnValue = isset($data[$srcColumn]) ? $data[$srcColumn] : null;
                        if ($joinColumnValue !== null) {
                            if ($targetClass->containsForeignIdentifier) {
                                $associatedId[$targetClass->getFieldForColumn($targetColumn)] = $joinColumnValue;
                            } else {
                                $associatedId[$targetClass->fieldNames[$targetColumn]] = $joinColumnValue;
                            }
                        }
                    }
                    if ( ! $associatedId) {
                                                $class->reflFields[$field]->setValue($entity, null);
                        $this->originalEntityData[$oid][$field] = null;
                        continue;
                    }
                    if ( ! isset($hints['fetchMode'][$class->name][$field])) {
                        $hints['fetchMode'][$class->name][$field] = $assoc['fetch'];
                    }
                                                                                                    $relatedIdHash = implode(' ', $associatedId);
                    switch (true) {
                        case (isset($this->identityMap[$targetClass->rootEntityName][$relatedIdHash])):
                            $newValue = $this->identityMap[$targetClass->rootEntityName][$relatedIdHash];
                                                                                                                if ($hints['fetchMode'][$class->name][$field] == ClassMetadata::FETCH_EAGER &&
                                isset($hints[self::HINT_DEFEREAGERLOAD]) &&
                                !$targetClass->isIdentifierComposite &&
                                $newValue instanceof Proxy &&
                                $newValue->__isInitialized__ === false) {
                                $this->eagerLoadingEntities[$targetClass->rootEntityName][$relatedIdHash] = current($associatedId);
                            }
                            break;
                        case ($targetClass->subClasses):
                                                                                                                $newValue = $this->getEntityPersister($assoc['targetEntity'])->loadOneToOneEntity($assoc, $entity, $associatedId);
                            break;
                        default:
                            switch (true) {
                                                                case ($hints['fetchMode'][$class->name][$field] !== ClassMetadata::FETCH_EAGER):
                                    $newValue = $this->em->getProxyFactory()->getProxy($assoc['targetEntity'], $associatedId);
                                    break;
                                                                case (isset($hints[self::HINT_DEFEREAGERLOAD]) && ! $targetClass->isIdentifierComposite):
                                                                        $this->eagerLoadingEntities[$targetClass->rootEntityName][$relatedIdHash] = current($associatedId);
                                    $newValue = $this->em->getProxyFactory()->getProxy($assoc['targetEntity'], $associatedId);
                                    break;
                                default:
                                                                        $newValue = $this->em->find($assoc['targetEntity'], $associatedId);
                                    break;
                            }
                                                        $newValueOid = spl_object_hash($newValue);
                            $this->entityIdentifiers[$newValueOid] = $associatedId;
                            $this->identityMap[$targetClass->rootEntityName][$relatedIdHash] = $newValue;
                            if (
                                $newValue instanceof NotifyPropertyChanged &&
                                ( ! $newValue instanceof Proxy || $newValue->__isInitialized())
                            ) {
                                $newValue->addPropertyChangedListener($this);
                            }
                            $this->entityStates[$newValueOid] = self::STATE_MANAGED;
                                                        break;
                    }
                    $this->originalEntityData[$oid][$field] = $newValue;
                    $class->reflFields[$field]->setValue($entity, $newValue);
                    if ($assoc['inversedBy'] && $assoc['type'] & ClassMetadata::ONE_TO_ONE) {
                        $inverseAssoc = $targetClass->associationMappings[$assoc['inversedBy']];
                        $targetClass->reflFields[$inverseAssoc['fieldName']]->setValue($newValue, $entity);
                    }
                    break;
                default:
                                        $pColl = new PersistentCollection($this->em, $targetClass, new ArrayCollection);
                    $pColl->setOwner($entity, $assoc);
                    $pColl->setInitialized(false);
                    $reflField = $class->reflFields[$field];
                    $reflField->setValue($entity, $pColl);
                    if ($assoc['fetch'] == ClassMetadata::FETCH_EAGER) {
                        $this->loadCollection($pColl);
                        $pColl->takeSnapshot();
                    }
                    $this->originalEntityData[$oid][$field] = $pColl;
                    break;
            }
        }
        if ($overrideLocalValues) {
            $invoke = $this->listenersInvoker->getSubscribedSystems($class, Events::postLoad);
            if ($invoke !== ListenersInvoker::INVOKE_NONE) {
                $this->listenersInvoker->invoke($class, Events::postLoad, $entity, new LifecycleEventArgs($entity, $this->em), $invoke);
            }
        }
        return $entity;
    }
    public function triggerEagerLoads()
    {
        if ( ! $this->eagerLoadingEntities) {
            return;
        }
                $eagerLoadingEntities       = $this->eagerLoadingEntities;
        $this->eagerLoadingEntities = array();
        foreach ($eagerLoadingEntities as $entityName => $ids) {
            if ( ! $ids) {
                continue;
            }
            $class = $this->em->getClassMetadata($entityName);
            $this->getEntityPersister($entityName)->loadAll(
                array_combine($class->identifier, array(array_values($ids)))
            );
        }
    }
    public function loadCollection(PersistentCollection $collection)
    {
        $assoc     = $collection->getMapping();
        $persister = $this->getEntityPersister($assoc['targetEntity']);
        switch ($assoc['type']) {
            case ClassMetadata::ONE_TO_MANY:
                $persister->loadOneToManyCollection($assoc, $collection->getOwner(), $collection);
                break;
            case ClassMetadata::MANY_TO_MANY:
                $persister->loadManyToManyCollection($assoc, $collection->getOwner(), $collection);
                break;
        }
        $collection->setInitialized(true);
    }
    public function getIdentityMap()
    {
        return $this->identityMap;
    }
    public function getOriginalEntityData($entity)
    {
        $oid = spl_object_hash($entity);
        if (isset($this->originalEntityData[$oid])) {
            return $this->originalEntityData[$oid];
        }
        return array();
    }
    public function setOriginalEntityData($entity, array $data)
    {
        $this->originalEntityData[spl_object_hash($entity)] = $data;
    }
    public function setOriginalEntityProperty($oid, $property, $value)
    {
        $this->originalEntityData[$oid][$property] = $value;
    }
    public function getEntityIdentifier($entity)
    {
        return $this->entityIdentifiers[spl_object_hash($entity)];
    }
    public function getSingleIdentifierValue($entity)
    {
        $class = $this->em->getClassMetadata(get_class($entity));
        if ($class->isIdentifierComposite) {
            throw ORMInvalidArgumentException::invalidCompositeIdentifier();
        }
        $values = $this->isInIdentityMap($entity)
            ? $this->getEntityIdentifier($entity)
            : $class->getIdentifierValues($entity);
        return isset($values[$class->identifier[0]]) ? $values[$class->identifier[0]] : null;
    }
    public function tryGetById($id, $rootClassName)
    {
        $idHash = implode(' ', (array) $id);
        if (isset($this->identityMap[$rootClassName][$idHash])) {
            return $this->identityMap[$rootClassName][$idHash];
        }
        return false;
    }
    public function scheduleForDirtyCheck($entity)
    {
        $rootClassName = $this->em->getClassMetadata(get_class($entity))->rootEntityName;
        $this->scheduledForDirtyCheck[$rootClassName][spl_object_hash($entity)] = $entity;
    }
    public function hasPendingInsertions()
    {
        return ! empty($this->entityInsertions);
    }
    public function size()
    {
        $countArray = array_map(function ($item) { return count($item); }, $this->identityMap);
        return array_sum($countArray);
    }
    public function getEntityPersister($entityName)
    {
        if (isset($this->persisters[$entityName])) {
            return $this->persisters[$entityName];
        }
        $class = $this->em->getClassMetadata($entityName);
        switch (true) {
            case ($class->isInheritanceTypeNone()):
                $persister = new Persisters\BasicEntityPersister($this->em, $class);
                break;
            case ($class->isInheritanceTypeSingleTable()):
                $persister = new Persisters\SingleTablePersister($this->em, $class);
                break;
            case ($class->isInheritanceTypeJoined()):
                $persister = new Persisters\JoinedSubclassPersister($this->em, $class);
                break;
            default:
                $persister = new Persisters\UnionSubclassPersister($this->em, $class);
        }
        $this->persisters[$entityName] = $persister;
        return $this->persisters[$entityName];
    }
    public function getCollectionPersister(array $association)
    {
        $type = $association['type'];
        if (isset($this->collectionPersisters[$type])) {
            return $this->collectionPersisters[$type];
        }
        switch ($type) {
            case ClassMetadata::ONE_TO_MANY:
                $persister = new Persisters\OneToManyPersister($this->em);
                break;
            case ClassMetadata::MANY_TO_MANY:
                $persister = new Persisters\ManyToManyPersister($this->em);
                break;
        }
        $this->collectionPersisters[$type] = $persister;
        return $this->collectionPersisters[$type];
    }
    public function registerManaged($entity, array $id, array $data)
    {
        $oid = spl_object_hash($entity);
        $this->entityIdentifiers[$oid]  = $id;
        $this->entityStates[$oid]       = self::STATE_MANAGED;
        $this->originalEntityData[$oid] = $data;
        $this->addToIdentityMap($entity);
        if ($entity instanceof NotifyPropertyChanged && ( ! $entity instanceof Proxy || $entity->__isInitialized())) {
            $entity->addPropertyChangedListener($this);
        }
    }
    public function clearEntityChangeSet($oid)
    {
        $this->entityChangeSets[$oid] = array();
    }
    public function propertyChanged($entity, $propertyName, $oldValue, $newValue)
    {
        $oid   = spl_object_hash($entity);
        $class = $this->em->getClassMetadata(get_class($entity));
        $isAssocField = isset($class->associationMappings[$propertyName]);
        if ( ! $isAssocField && ! isset($class->fieldMappings[$propertyName])) {
            return;         }
                $this->entityChangeSets[$oid][$propertyName] = array($oldValue, $newValue);
        if ( ! isset($this->scheduledForDirtyCheck[$class->rootEntityName][$oid])) {
            $this->scheduleForDirtyCheck($entity);
        }
    }
    public function getScheduledEntityInsertions()
    {
        return $this->entityInsertions;
    }
    public function getScheduledEntityUpdates()
    {
        return $this->entityUpdates;
    }
    public function getScheduledEntityDeletions()
    {
        return $this->entityDeletions;
    }
    public function getScheduledCollectionDeletions()
    {
        return $this->collectionDeletions;
    }
    public function getScheduledCollectionUpdates()
    {
        return $this->collectionUpdates;
    }
    public function initializeObject($obj)
    {
        if ($obj instanceof Proxy) {
            $obj->__load();
            return;
        }
        if ($obj instanceof PersistentCollection) {
            $obj->initialize();
        }
    }
    protected static function objToStr($obj)
    {
        return method_exists($obj, '__toString') ? (string)$obj : get_class($obj).'@'.spl_object_hash($obj);
    }
    public function markReadOnly($object)
    {
        if ( ! is_object($object) || ! $this->isInIdentityMap($object)) {
            throw ORMInvalidArgumentException::readOnlyRequiresManagedEntity($object);
        }
        $this->readOnlyObjects[spl_object_hash($object)] = true;
    }
    public function isReadOnly($object)
    {
        if ( ! is_object($object)) {
            throw ORMInvalidArgumentException::readOnlyRequiresManagedEntity($object);
        }
        return isset($this->readOnlyObjects[spl_object_hash($object)]);
    }
    protected function dispatchOnFlushEvent()
    {
        if ($this->evm->hasListeners(Events::onFlush)) {
            $this->evm->dispatchEvent(Events::onFlush, new OnFlushEventArgs($this->em));
        }
    }
    protected function dispatchPostFlushEvent()
    {
        if ($this->evm->hasListeners(Events::postFlush)) {
            $this->evm->dispatchEvent(Events::postFlush, new PostFlushEventArgs($this->em));
        }
    }
    protected function isIdentifierEquals($entity1, $entity2)
    {
        if ($entity1 === $entity2) {
            return true;
        }
        $class = $this->em->getClassMetadata(get_class($entity1));
        if ($class !== $this->em->getClassMetadata(get_class($entity2))) {
            return false;
        }
        $oid1 = spl_object_hash($entity1);
        $oid2 = spl_object_hash($entity2);
        $id1 = isset($this->entityIdentifiers[$oid1])
            ? $this->entityIdentifiers[$oid1]
            : $this->flattenIdentifier($class, $class->getIdentifierValues($entity1));
        $id2 = isset($this->entityIdentifiers[$oid2])
            ? $this->entityIdentifiers[$oid2]
            : $this->flattenIdentifier($class, $class->getIdentifierValues($entity2));
        return $id1 === $id2 || implode(' ', $id1) === implode(' ', $id2);
    }
}
