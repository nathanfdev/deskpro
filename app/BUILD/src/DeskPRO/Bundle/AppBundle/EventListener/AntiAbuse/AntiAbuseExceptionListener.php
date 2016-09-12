<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
