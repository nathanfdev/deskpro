<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\AppBundle\EventListener;

use Application\DeskPRO\Entity\ObjectLang;
use DeskPRO\Bundle\AppBundle\Entity\ObjectTranslatableInterface;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\LazyCriteriaCollection;

/**
 * Class ObjectTranslatableListener.
 */
class ObjectTranslatableListener implements EventSubscriber
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'postLoad',
            'postPersist',
            'postUpdate',
            'preRemove',
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
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->updateObjectTranslations($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->updateObjectTranslations($args);
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
     * @param LifecycleEventArgs $args
     */
    private function updateObjectTranslations(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof ObjectTranslatableInterface) {
            return;
        }

        $em = $args->getEntityManager();

        $old_translations = $this->getLazyCriteriaCollection($entity, $em);
        $new_translations = $entity->getObjectPropsTranslations();

        // persist new and changed entities
        foreach ($new_translations as $object_lang) {
            /* @var ObjectLang $object_lang */
            $object_lang->setObject($entity);
            $em->persist($object_lang);
            $em->flush($object_lang);
        }

        // remove deleted
        foreach ($old_translations as $object_lang) {
            if (!$new_translations->contains($object_lang)) {
                $em->remove($object_lang);
                $em->flush($object_lang);
            }
        }
    }

    /**
     * @param ObjectTranslatableInterface $entity
     *
     * @return Criteria
     */
    private function getCriteria(ObjectTranslatableInterface $entity)
    {
        return new Criteria(Criteria::expr()->eq('ref', $entity->getObjectRef()));
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

        return new LazyCriteriaCollection($persister, $this->getCriteria($entity));
    }
}
