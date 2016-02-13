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
namespace DeskPRO\Bundle\AppBundle\EventListener;

use DeskPRO\Bundle\AppBundle\HttpKernel\DpKernelEvents;
use DeskPRO\Bundle\AppBundle\HttpKernel\Event\GetPreResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sets an attribute on the request when this is considered a "low level" request.
 * This is read from other listeners to prevent them doing unecessary work.
 */
class RequestTypeClassifierListener implements EventSubscriberInterface
{
    const LOW_REQUEST_ATTR = '_dp_is_low';

    public function onKernelPreRequest(GetPreResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$request->attributes->has(self::LOW_REQUEST_ATTR)) {
            if (preg_match('#^/dp/#', $request->getPathInfo()) || preg_match('#^/[a-z]{2}(?:_[A-Z]{2})?/dp/#', $request->getPathInfo())) {
                $request->attributes->set(self::LOW_REQUEST_ATTR, true);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            // high priority, called before everything
            DpKernelEvents::PRE_REQUEST => array('onKernelPreRequest', 5000),
        );
    }
}
