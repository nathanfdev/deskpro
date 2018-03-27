<?php

namespace DeskPRO\Bundle\AppBundle\QuickSearch\EventListener;

use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchEvent;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SortListener.
 */
class SortListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            QuickSearchEvents::FINISH => ['onDefaultSort', 1000], // Call this first
        ];
    }

    /**
     * @param QuickSearchEvent $event
     */
    public function onDefaultSort(QuickSearchEvent $event)
    {
        $context = $event->getContext();
        $request = $event->getRequest();

        if ($request->getSort()) {
            return;
        }

        $entities = $context->getEntities();
        $ids      = $context->getIds();

        usort($ids, function ($a, $b) {
            return $a - $b;
        });
        usort($entities, function ($a, $b) {
            return $a->id - $b->id;
        });

        $context->setEntities($entities);
        $context->setIds($ids);
    }
}
