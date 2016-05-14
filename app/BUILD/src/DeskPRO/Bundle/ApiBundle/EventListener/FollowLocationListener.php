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

namespace DeskPRO\Bundle\ApiBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class FollowLocationListener.
 */
class FollowLocationListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onResponse', 1024],
        ];
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        $request  = $event->getRequest();
        $response = $event->getResponse();

        if ($request->getMethod() !== Request::METHOD_PUT
            || $response->getStatusCode() !== Response::HTTP_NO_CONTENT
            || !$request->query->get('follow_location')
            || !$response->headers->get('Location')) {
            return;
        }

        $response = $event->getResponse();
        $location = $response->headers->get('Location');

        $request  = Request::create($location);
        $response = $event->getKernel()->handle($request, HttpKernelInterface::SUB_REQUEST);

        $event->setResponse($response);
    }
}
