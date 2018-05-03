<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\ObjectLang;
use DeskPRO\Bundle\AppBundle\Entity\ObjectTranslatableInterface;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
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
            'preFlush',
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
     * @param PreFlushEventArgs $args
     */
    public function preFlush(PreFlushEventArgs $args)
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

            if (!$objectLang->getId()) {
                // check there is no unique constraint errors
                // remove outdated not mapped translations
                $qb = $em->createQueryBuilder();
                $qb
                    ->delete()
                    ->from(ObjectLang::class, 'o')
                    ->where(
                        'o.ref = :ref',
                        'o.prop_name = :prop_name',
                        'o.language = :language'
                    )
                    ->setParameter('ref', $objectLang->getRef())
                    ->setParameter('prop_name', $objectLang->getPropName())
                    ->setParameter('language', $objectLang->getLanguage())
                    ->getQuery()
                    ->execute()
                ;
            }

            $uow = $em->getUnitOfWork();
            $uow->computeChangeSet($em->getClassMetadata(get_class($objectLang)), $objectLang);
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
