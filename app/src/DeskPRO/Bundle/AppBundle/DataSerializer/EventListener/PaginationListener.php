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
namespace DeskPRO\Bundle\AppBundle\DataSerializer\EventListener;

use DeskPRO\Bundle\AppBundle\DataSerializer\DataSerializerEvent;
use DeskPRO\Bundle\AppBundle\DataSerializer\DataSerializerEvents;
use Pagerfanta\Pagerfanta;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Deals with PagerFanta instances. Sets the "meta" data for pagination.
 */
class PaginationListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            DataSerializerEvents::PRE_SERIALIZE  => ['preSerialize', 128],
            DataSerializerEvents::POST_SERIALIZE => ['postSerialize'],
        ];
    }

    /**
     * If the source data is a PagerFanta instace, it prepares the context with the correct Main Data.
     *
     * @param DataSerializerEvent $event
     */
    public function preSerialize(DataSerializerEvent $event)
    {
        $context     = $event->getContext();
        $source_data = $context->getSourceData();

        if (!$source_data instanceof Pagerfanta) {
            // it's important to always make sure you call setMainData here with the main data
            // even if we're not dealing with a pager
            $context->setMainData($source_data);
        } else {
            $main_data = $source_data->getCurrentPageResults();
            $context->setMainData($main_data);
        }
    }

    /**
     * If the source data is a PagerFanta instance, add its pagger info to the "meta" part of the serializaiton.
     *
     * @param DataSerializerEvent $event
     */
    public function postSerialize(DataSerializerEvent $event)
    {
        $context     = $event->getContext();
        $source_data = $context->getSourceData();

        if (!$source_data instanceof Pagerfanta) {
            return;
        }

        $serialized = $context->getSerializedArray();

        if (!array_key_exists('meta', $serialized)) {
            $serialized['meta'] = [];
        }

        $meta = $serialized['meta'];

        $total_pages = ceil($source_data->count() / $source_data->getMaxPerPage());
        if ($total_pages < 1) {
            $total_pages = 1; // we shouldn't ever report less than 1 total pages
        }

        $pagination = [
            'total'        => $source_data->count(),
            'count'        => count($source_data->getCurrentPageResults()),
            'per_page'     => $source_data->getMaxPerPage(),
            'current_page' => $source_data->getCurrentPage(),
            'total_pages'  => $total_pages,
        ];

        $meta['pagination'] = $pagination;

        $serialized['meta'] = $meta;

        $context->setSerializedArray($serialized);
    }
}
