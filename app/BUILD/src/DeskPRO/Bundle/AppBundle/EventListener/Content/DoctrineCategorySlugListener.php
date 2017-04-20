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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\EventListener\Content;

use Application\DeskPRO\Entity\CategoryAbstract;
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
        if (!$entity instanceof CategoryAbstract) {
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
