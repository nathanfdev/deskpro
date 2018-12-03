<?php

namespace DeskPRO\Bundle\BrandBundle\EventListener;

use DeskPRO\Bundle\BrandBundle\Request\OriginalRequestStorage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class OriginalRequestListener.
 */
class OriginalRequestListener implements EventSubscriberInterface
{
    /**
     * @var OriginalRequestStorage
     */
    private $originalRequestStorage;

    /**
     * Constructor.
     *
     * @param OriginalRequestStorage $originalRequestStorage
     */
    public function __construct(OriginalRequestStorage $originalRequestStorage)
    {
        $this->originalRequestStorage = $originalRequestStorage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            // high priority, call before everything
            KernelEvents::REQUEST => ['onKernelRequest', 15000],
        ];
    }

    /**
     * @internal
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request         = $event->getRequest();
        $originalRequest = clone $request;

        $this->originalRequestStorage->setOriginalRequest($originalRequest);
        $request->attributes->set('original_request', $originalRequest);
    }
}
