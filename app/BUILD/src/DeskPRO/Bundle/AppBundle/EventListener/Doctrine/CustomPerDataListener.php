<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\CustomFieldData;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use DeskPRO\Bundle\AppBundle\Entity\CustomPerDataOwnerInterface;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\LazyCriteriaCollection;

/**
 * Class CustomPerDataListener.
 */
class CustomPerDataListener implements EventSubscriber
{
    /**
     * @var array
     */
    private $defIds = [];

    /**
     * @var array
     */
    private $updateQueue = [];

    /**
     * @var array
     */
    private $collectionCache = [];

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'postLoad',
            'postPersist',
            'preRemove',
            'onFlush',
            'onClear',
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof CustomPerDataOwnerInterface) {
            return;
        }

        $entity->setCustomPerData($this->getLazyCriteriaCollection($entity, $args->getEntityManager()));
        $this->updateQueue[spl_object_hash($entity)] = $entity;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof CustomPerDataOwnerInterface) {
            return;
        }

        $this->updateQueue[spl_object_hash($entity)] = $entity;
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        foreach ($this->updateQueue as $entity) {
            $this->updateCustomPerData($entity, $args->getEntityManager());
        }
    }

    public function onClear()
    {
        $this->updateQueue     = [];
        $this->collectionCache = [];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof CustomPerDataOwnerInterface) {
            return;
        }

        $em = $args->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb
            ->delete()
            ->from(CustomFieldData::class, 'd')
            ->innerJoin('d.root_definition', 'def')
            ->where(
                'd.owner_id = :owner_id',
                'd.root_definition IN (:def_ids)'
            )
            ->setParameter('owner_id', $entity->getId())
            ->setParameter('def_ids', $this->getDefIds($entity, $em))
        ;

        $qb->getQuery()->execute();
        unset($this->updateQueue[spl_object_hash($entity)]);
        unset($this->collectionCache[spl_object_hash($entity)]);
    }

    /**
     * @param mixed         $entity
     * @param EntityManager $em
     */
    private function updateCustomPerData($entity, EntityManager $em)
    {
        if (!$entity instanceof CustomPerDataOwnerInterface) {
            return;
        }

        $newCollection = $entity->getCustomPerData();
        if ($newCollection instanceof LazyCriteriaCollection) {
            if (!$newCollection->isInitialized()) {
                return;
            }
        }

        $oldCollection = $this->getLazyCriteriaCollection($entity, $em);

        // persist new and changed entities
        foreach ($newCollection as $customData) {
            /* @var CustomFieldData $customData */
            if (!$customData->owner_id && $entity->getId()) {
                $customData->setOwner($entity);
                $em->persist($customData);
                $em->getUnitOfWork()->computeChangeSets();
            }
        }

        // remove deleted
        foreach ($oldCollection as $customData) {
            if (!$newCollection->contains($customData)) {
                $em->remove($customData);
            }
        }
    }

    /**
     * @param CustomPerDataOwnerInterface $entity
     * @param EntityManager               $em
     *
     * @return LazyCriteriaCollection
     */
    private function getLazyCriteriaCollection(CustomPerDataOwnerInterface $entity, EntityManager $em)
    {
        if (!isset($this->collectionCache[spl_object_hash($entity)])) {
            $persister = $em->getUnitOfWork()->getEntityPersister(CustomFieldData::class);
            $criteria  = new Criteria();
            $criteria->andWhere($criteria->expr()->eq('owner_id', $entity->getId()));
            $criteria->andWhere($criteria->expr()->in('root_definition', $this->getDefIds($entity, $em)));

            $this->collectionCache[spl_object_hash($entity)] = new LazyCriteriaCollection($persister, $criteria);
        }

        return $this->collectionCache[spl_object_hash($entity)];
    }

    /**
     * @param CustomPerDataOwnerInterface $entity
     * @param EntityManager               $em
     *
     * @return array
     */
    private function getDefIds(CustomPerDataOwnerInterface $entity, EntityManager $em)
    {
        $entityClass = ClassUtils::getClass($entity);
        if (!isset($this->defIds[$entityClass])) {
            $qb = $em->createQueryBuilder();
            $qb
                ->select('d')
                ->from(CustomFieldDefinition::class, 'd')
                ->where(
                    'd.owner_class = :owner_class',
                    'd.parent is null'
                )
                ->setParameter('owner_class', ClassUtils::getClass($entity))
            ;

            $defs = $qb->getQuery()->getResult();
            $ids  = array_map(function (CustomFieldDefinition $def) {
                return $def->getId();
            }, $defs);

            $this->defIds[$entityClass] = $ids;
        }

        return $this->defIds[$entityClass];
    }
}
