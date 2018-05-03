<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\Phrase;
use DeskPRO\Bundle\AppBundle\Entity\PhraseTranslatableInterface;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\LazyCriteriaCollection;

/**
 * Class PhraseTranslatableListener.
 */
class PhraseTranslatableListener implements EventSubscriber
{
    /**
     * @var PhraseTranslatableInterface
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
        if (!$entity instanceof PhraseTranslatableInterface) {
            return;
        }

        $entity->setPhraseTranslations($this->getLazyCriteriaCollection($entity, $args->getEntityManager()));
        $this->updateQueue[spl_object_hash($entity)] = $entity;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof PhraseTranslatableInterface) {
            return;
        }

        $this->updatePhraseTranslations($entity, $args->getEntityManager());
        $args->getEntityManager()->flush();
    }

    /**
     * @param PreFlushEventArgs $args
     */
    public function preFlush(PreFlushEventArgs $args)
    {
        foreach ($this->updateQueue as $entity) {
            $this->updatePhraseTranslations($entity, $args->getEntityManager());
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
        if (!$entity instanceof PhraseTranslatableInterface) {
            return;
        }

        $em    = $args->getEntityManager();
        $query = $em
            ->createQueryBuilder()
            ->delete()
            ->from(Phrase::class, 'e')
            ->where('e.name LIKE :name')
            ->setParameter('name', $entity->getPhraseName('').'%')
            ->getQuery()
        ;

        $query->execute();
    }

    /**
     * @param PhraseTranslatableInterface $entity
     * @param EntityManager               $em
     */
    private function updatePhraseTranslations(PhraseTranslatableInterface $entity, EntityManager $em)
    {
        $newCollection = $entity->getPhraseTranslations();
        if ($newCollection instanceof LazyCriteriaCollection) {
            if (!$newCollection->isInitialized()) {
                return;
            }
        }

        $oldCollection = $this->getLazyCriteriaCollection($entity, $em);

        // persist new and changed entities
        foreach ($newCollection as $phrase) {
            /* @var Phrase $phrase */
            $em->persist($phrase);

            $uow = $em->getUnitOfWork();
            $uow->computeChangeSet($em->getClassMetadata(get_class($phrase)), $phrase);
        }

        // remove deleted
        foreach ($oldCollection as $phrase) {
            if (!$newCollection->contains($phrase)) {
                $em->remove($phrase);
            }
        }
    }

    /**
     * Get all phrases for specific entity.
     *
     * @param PhraseTranslatableInterface $entity
     * @param EntityManager               $em
     *
     * @return LazyCriteriaCollection
     */
    private function getLazyCriteriaCollection(PhraseTranslatableInterface $entity, EntityManager $em)
    {
        $persister = $em->getUnitOfWork()->getEntityPersister(Phrase::class);
        $criteria  = new Criteria(Criteria::expr()->contains('name', $entity->getPhraseName('')));

        return new LazyCriteriaCollection($persister, $criteria);
    }
}
