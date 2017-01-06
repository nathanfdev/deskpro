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

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\ObjectLang;
use DeskPRO\Bundle\AppBundle\Entity\ObjectTranslatableInterface;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\LazyCriteriaCollection;

/**
 * Class ObjectTranslatableListener.
 */
class ObjectTranslatableListener implements EventSubscriber
{
    /**
     * @var ObjectTranslatableInterface[]
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
            'onClear',
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof ObjectTranslatableInterface) {
            return;
        }

        $entity->setObjectPropsTranslations($this->getLazyCriteriaCollection($entity, $args->getEntityManager()));
        $this->updateQueue[spl_object_hash($entity)] = $entity;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof ObjectTranslatableInterface) {
            return;
        }

        $this->updateObjectTranslations($entity, $args->getEntityManager());
        $args->getEntityManager()->flush();
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        foreach ($this->updateQueue as $entity) {
            $this->updateObjectTranslations($entity, $args->getEntityManager());
        }
    }

    public function onClear()
    {
        $this->updateQueue = [];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof ObjectTranslatableInterface) {
            return;
        }

        $em    = $args->getEntityManager();
        $query = $em
            ->createQueryBuilder()
            ->delete()
            ->from(ObjectLang::class, 'e')
            ->where('e.ref = :ref')
            ->setParameter('ref', $entity->getObjectRef())
            ->getQuery()
        ;

        $query->execute();
    }

    /**
     * @param ObjectTranslatableInterface $entity
     * @param EntityManager               $em
     */
    private function updateObjectTranslations(ObjectTranslatableInterface $entity, EntityManager $em)
    {
        $newCollection = $entity->getObjectPropsTranslations();
        if ($newCollection instanceof LazyCriteriaCollection) {
            if (!$newCollection->isInitialized()) {
                return;
            }
        }

        $oldCollection = $this->getLazyCriteriaCollection($entity, $em);

        // persist new and changed entities
        foreach ($newCollection as $objectLang) {
            /* @var ObjectLang $objectLang */
            $objectLang->setObject($entity);
            $em->persist($objectLang);
            $em->getUnitOfWork()->computeChangeSets();
        }

        // remove deleted
        foreach ($oldCollection as $objectLang) {
            if (!$newCollection->contains($objectLang)) {
                $em->remove($objectLang);
            }
        }
    }

    /**
     * @param ObjectTranslatableInterface $entity
     * @param EntityManager               $em
     *
     * @return LazyCriteriaCollection
     */
    private function getLazyCriteriaCollection(ObjectTranslatableInterface $entity, EntityManager $em)
    {
        $persister = $em->getUnitOfWork()->getEntityPersister(ObjectLang::class);
        $criteria  = new Criteria(Criteria::expr()->eq('ref', $entity->getObjectRef()));

        return new LazyCriteriaCollection($persister, $criteria);
    }
}
