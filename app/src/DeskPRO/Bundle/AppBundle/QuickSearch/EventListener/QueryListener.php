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

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchContext;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchEvent;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class QueryListener.
 */
class QueryListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            QuickSearchEvents::SEARCH => [
                ['onParseId', 1],
                ['onParseAccessCode', 2],
            ],
        ];
    }

    /**
     * @param QuickSearchEvent $event
     */
    public function onParseId(QuickSearchEvent $event)
    {
        $context = $event->getContext();
        $request = $context->getRequest();

        if ($request->isId()) {
            $context->ids->add((int) $request->getQuery());
        }
    }

    /**
     * @param QuickSearchEvent $event
     */
    public function onParseAccessCode(QuickSearchEvent $event)
    {
        $context = $event->getContext();
        $request = $context->getRequest();

        if ($context->getType() !== QuickSearchContext::TYPE_TICKET) {
            return;
        }

        $info = Ticket::decodeAccessCode($request->getQuery());
        if (!empty($info['ticket_id'])) {
            $context->ids->add((int) $info['ticket_id']);
        }
    }
}
