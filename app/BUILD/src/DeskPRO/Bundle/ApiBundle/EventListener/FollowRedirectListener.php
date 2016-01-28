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

namespace DeskPRO\Bundle\ApiBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class FollowRedirectListener implements EventSubscriberInterface
{
    const FOLLOW_REDIRECT_PARAMETER = 'follow_redirect';

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array('onResponse', 1024),
        );
    }

    public function onResponse(FilterResponseEvent $event)
    {
        $request  = $event->getRequest();
        $response = $event->getResponse();
        if ($response->getStatusCode() === Response::HTTP_FOUND
            && $request->get(self::FOLLOW_REDIRECT_PARAMETER, false) !== false) {
            $response = $this->getNewResponse($event);
            $event->setResponse($response);
        }
    }

    private function getNewResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $location = $response->headers->get('Location', false);
        if (!$location) {
            // TODO possibly we have to throw some kind of exception in this case
            return $response;
        }
        $request  = $this->createSubRequest($event->getRequest(), $location);
        $response = $event->getKernel()->handle($request, HttpKernelInterface::SUB_REQUEST);

        return $response;
    }

    private function createSubRequest(Request $request, $location)
    {
        $clone = clone $request;
        $clone->server->set('REQUEST_URI', $location);
        $clone->server->set('REQUEST_METHOD', Request::METHOD_GET);
        $query = $clone->query;
        $query->remove(self::FOLLOW_REDIRECT_PARAMETER);
        $query   = $query->all();
        $cookies = $clone->cookies->all();
        $server  = $clone->server->all();
        $content = $clone->getContent();
        $clone->initialize($query, [], [], $cookies, [], $server, $content);

        return $clone;
    }
}
