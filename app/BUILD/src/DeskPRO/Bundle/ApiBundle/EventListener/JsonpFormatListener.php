<?php

namespace DeskPRO\Bundle\ApiBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class JsonpFormatListener.
 */
class JsonpFormatListener implements EventSubscriberInterface
{
    const JSONP_CALLBACK_PARAM = 'callback';

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 256],
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($request->query->has(self::JSONP_CALLBACK_PARAM)) {
            $request->setFormat('jsonp', 'application/javascript');
            $request->attributes->set('_format', 'jsonp');
            $request->attributes->set('media_type', 'jsonp');
        }
    }
}
