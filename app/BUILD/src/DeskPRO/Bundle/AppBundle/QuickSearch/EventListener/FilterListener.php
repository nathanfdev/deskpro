<?php

namespace DeskPRO\Bundle\AppBundle\QuickSearch\EventListener;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchEvent;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class FilterListener.
 */
class FilterListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            QuickSearchEvents::FINISH => ['onFilterPeopleByPhoneNumber'],
        ];
    }

    /**
     * @internal
     *
     * @param QuickSearchEvent $event
     */
    public function onFilterPeopleByPhoneNumber(QuickSearchEvent $event)
    {
        $context = $event->getContext();
        $request = $event->getRequest();

        if (!$context->isPerson()) {
            return;
        }
        if (!$request->getParam('with_phone_number')) {
            return;
        }

        $entities = $context->getEntities();
        $entities = array_filter($entities, function (Person $person) {
            return $person->getPrimaryPhoneNumber();
        });

        $context->setEntities($entities);
    }
}
