<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\EventListener\Content;

use Application\DeskPRO\Entity\CategoryAbstract;
use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\Product;
use DeskPRO\Bundle\AppBundle\Content\CategorySlugManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;

/**
 * Just ensures that the slug of a content object is set correctly (pre persist, and pre update) before flushing.
 */
class DoctrineCategorySlugListener implements EventSubscriber
{
    /**
     * @var CategorySlugManager
     */
    private $slugManager;

    /**
     * Constructor.
     *
     * @param CategorySlugManager $slugManager
     */
    public function __construct(CategorySlugManager $slugManager)
    {
        $this->slugManager = $slugManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'prePersist',
            'preUpdate',
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->ensureValidSlug($args->getObject());
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($this->ensureValidSlug($entity)) {
            // updates require a signal to the UOW to recalculate its changeset

            /** @var EntityManager $em */
            $em  = $args->getObjectManager();
            $uow = $em->getUnitOfWork();
            $uow->recomputeSingleEntityChangeSet($em->getClassMetadata(get_class($entity)), $entity);
        }
    }

    /**
     * @param $entity
     *
     * @return bool
     */
    protected function ensureValidSlug($entity)
    {
        if (!$entity instanceof CategoryAbstract && !$entity instanceof Guide) {
            return false;
        }

        // product has no slug
        if ($entity instanceof Product) {
            return false;
        }

        $this->slugManager->ensureValidSlug($entity);

        return true;
    }
}
