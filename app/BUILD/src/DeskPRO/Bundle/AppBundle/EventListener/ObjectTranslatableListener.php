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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\QueryBuilder;

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
            'postPersist',
            'postUpdate',
            'preRemove',
        ];
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
        $query = $this
            ->prepareQueryBuilder($entity, $em)
            ->delete()
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

        $em           = $args->getEntityManager();
        $object_langs = new ArrayCollection(
            $this
                ->prepareQueryBuilder($entity, $em)
                ->select('e')
                ->getQuery()
                ->getResult()
        );

        /* @var ObjectLang $object_lang */
        foreach ($entity->getObjectPropsTranslations() as $prop_name => $property_translations) {
            // persist new and changed entities
            foreach ($property_translations as $object_lang) {
                $object_lang->setObject($entity);
                $em->persist($object_lang);
                $em->flush($object_lang);
            }

            // remove deleted
            foreach ($object_langs as $object_lang) {
                if ($object_lang->getPropName() === $prop_name && !$property_translations->contains($object_lang)) {
                    $em->remove($object_lang);
                    $em->flush($object_lang);
                }
            }
        }
    }

    /**
     * Returns entity related translations.
     *
     * @param ObjectTranslatableInterface $entity
     * @param EntityManager               $em
     *
     * @return QueryBuilder
     */
    private function prepareQueryBuilder(ObjectTranslatableInterface $entity, EntityManager $em)
    {
        $qb = $em->createQueryBuilder();
        $qb
            ->from(ObjectLang::class, 'e')
            ->where('e.ref = :ref')
            ->setParameters([
                'ref' => $entity->getObjectRef(),
            ])
        ;

        return $qb;
    }
}
