<?php

namespace Application\PortalBundle\EventListener;

use Application\PortalBundle\Mode\PortalMode;
use Application\PortalBundle\Mode\PortalModeFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PortalModeListener implements EventSubscriberInterface
{
    /**
     * @var PortalModeFactory
     */
    private $factory;

    public function __construct(PortalModeFactory $factory)
    {
        $this->factory = $factory;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $mode = $this->factory->createMode($request->getPathInfo());

        $request->attributes->set(PortalMode::ATTR_NAME, $mode);
    }

    public static function getSubscribedEvents()
    {
        return array(KernelEvents::REQUEST => array('onKernelRequest', 256));
    }
}
