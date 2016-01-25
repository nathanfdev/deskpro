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
namespace DeskPRO\Bundle\AppBundle\QuickSearch\EventListener;

use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchEvent;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchEvents;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class LoadListener.
 */
class LoadListener implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            QuickSearchEvents::POST_SEARCH => ['onLoadEntities', 1000], // Load entities before permission check
        ];
    }

    /**
     * @param QuickSearchEvent $event
     */
    public function onLoadEntities(QuickSearchEvent $event)
    {
        $context = $event->getContext();

        $all_ids    = $context->ids->toArray();
        $loaded_ids = $context->entities->map(function ($entity) { return $entity->getId(); })->toArray();
        $require_ids = array_diff($all_ids, $loaded_ids);

        if (empty($require_ids)) {
            return;
        }

        $entities = $this->em->getRepository($context->getEntityName())->findBy(['id' => $require_ids]);
        foreach ($entities as $entity) {
            $context->entities->add($entity);
        }
    }
}
