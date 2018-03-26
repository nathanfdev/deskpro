<?php

namespace DeskPRO\Bundle\AppBundle\QuickSearch\EventListener;

use Application\DeskPRO\Entity\Ticket;
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
        $request = $event->getRequest();

        if ($request->isId()) {
            $context->addId($request->getQuery());
        }
    }

    /**
     * @param QuickSearchEvent $event
     */
    public function onParseAccessCode(QuickSearchEvent $event)
    {
        $context = $event->getContext();
        $request = $event->getRequest();

        if (!$context->isTicket()) {
            return;
        }

        $info = Ticket::decodeAccessCode($request->getQuery());
        if (!empty($info['ticket_id'])) {
            $context->addId($info['ticket_id']);
        }
    }
}
