<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\CustomFieldData;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use DeskPRO\Bundle\AppBundle\Entity\CustomPerDataOwnerInterface;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\EventSubscriber;
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
    private $updateQueue = [];

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
        $this->updateQueue[$entity->getId()] = $entity;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        if (!$args->getEntity() instanceof CustomPerDataOwnerInterface) {
            return;
        }

        $this->updateCustomPerData($args->getEntity(), $args->getEntityManager());
        $args->getEntityManager()->flush();
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        foreach ($this->updateQueue as $id => $entity) {
            $this->updateCustomPerData($entity, $args->getEntityManager());
        }
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
                'd.root_definition = :def_ids'
            )
            ->setParameter('owner_id', $entity->getId())
            ->setParameter('def_ids', $this->getDefIds($entity, $em))
        ;

        $qb->getQuery()->execute();
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

        $oldCollection = $this->getLazyCriteriaCollection($entity, $em);
        $newCollection = $entity->getCustomPerData();

        // persist new and changed entities
        foreach ($newCollection as $customData) {
            /* @var CustomFieldData $customData */
            $customData->setOwner($entity);
            $em->persist($customData);
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
        $persister = $em->getUnitOfWork()->getEntityPersister(CustomFieldData::class);
        $criteria  = new Criteria();
        $criteria->andWhere($criteria->expr()->eq('owner_id', $entity->getId()));
        $criteria->andWhere($criteria->expr()->in('root_definition', $this->getDefIds($entity, $em)));

        return new LazyCriteriaCollection($persister, $criteria);
    }

    /**
     * @param CustomPerDataOwnerInterface $entity
     * @param EntityManager               $em
     *
     * @return array
     */
    private function getDefIds(CustomPerDataOwnerInterface $entity, EntityManager $em)
    {
        $qb = $em->createQueryBuilder();
        $qb
            ->select('d')
            ->from(CustomFieldDefinition::class, 'd')
            ->where(
                'd.owner_class = :owner_class',
                'd.parent is null'
            )
            ->setParameter('owner_class', get_class($entity))
        ;

        $defs = $qb->getQuery()->getResult();
        $ids  = array_map(function (CustomFieldDefinition $def) { return $def->getId(); }, $defs);

        return $ids;
    }
}
