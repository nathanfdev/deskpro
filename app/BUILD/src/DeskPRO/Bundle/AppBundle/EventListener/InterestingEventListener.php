<?php

namespace DeskPRO\Bundle\AppBundle\EventListener;

use DeskPRO\Bundle\AppBundle\Metrics\InterestingEvent;
use DpSys\LowError\SystemErrorHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InterestingEventListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            InterestingEvent::NAME => 'onInterestingEvent',
        ];
    }

    public function onInterestingEvent(InterestingEvent $event)
    {
        global $DP_ENV;

        if (!$DP_ENV) {
            return;
        }

        $cb = $DP_ENV->getConfig('env.interesting_events_fn');

        if (!$cb) {
            return;
        }

        try {
            $cb($event);
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e, false, null, true);
        }
    }
}
