<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\EventListener\AntiAbuse;

use DeskPRO\Bundle\AppBundle\AntiAbuse\Exception\AntiAbuseException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * The AntiAbuse system will throw AntiAbuseException's if a response needs to be returned immediately.
 * This listener listens for such exceptions and returns the correct response.
 */
class AntiAbuseExceptionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [KernelEvents::EXCEPTION => ['handleException', 1028]];
    }

    public function handleException(GetResponseForExceptionEvent $event)
    {
        $anti_abuse_exception = $event->getException();
        if (!$anti_abuse_exception instanceof AntiAbuseException) {
            return;
        }

        $anti_abuse = $anti_abuse_exception->getAntiAbuseEvent();

        if ($anti_abuse->isResponseRecommended()) {
            $event->setResponse($anti_abuse->getRecommendedResponse());
            $event->stopPropagation();
        }
    }
}
