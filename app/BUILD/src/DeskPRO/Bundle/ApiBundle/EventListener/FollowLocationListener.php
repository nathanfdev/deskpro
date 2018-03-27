<?php

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

        if ($this->needToFollowLocation($request, $response)) {
            $location      = $response->headers->get('Location');
            $followRequest = Request::create($location, 'GET', $request->query->all());
            $response      = $event->getKernel()->handle($followRequest, HttpKernelInterface::SUB_REQUEST);
            $event->setResponse($response);
        }
    }

    private function needToFollowLocation(Request $request, Response $response)
    {
        return in_array($request->getMethod(), [Request::METHOD_PUT, Request::METHOD_POST], true)
                && in_array($response->getStatusCode(), [Response::HTTP_NO_CONTENT, Response::HTTP_FOUND], true)
                && $request->query->get('follow_location')
                && $response->headers->get('Location');
    }
}
