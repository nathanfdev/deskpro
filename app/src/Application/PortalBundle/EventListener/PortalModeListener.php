<?php

namespace Application\PortalBundle\EventListener;

use Application\PortalBundle\Mode\PortalMode;
use Application\PortalBundle\Mode\PortalModeFactory;
use Application\PortalBundle\Mode\PortalModeStorage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PortalModeListener implements EventSubscriberInterface
{
    /**
     * @var PortalModeFactory
     */
    private $factory;

    /**
     * @var PortalModeStorage
     */
    private $store;

    public function __construct(PortalModeFactory $factory, PortalModeStorage $store)
    {
        $this->factory = $factory;
        $this->store = $store;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $mode = $this->factory->createMode($request->getPathInfo());

        $this->store->setMode($mode);
    }

    public static function getSubscribedEvents()
    {
        return array(KernelEvents::REQUEST => array('onKernelRequest', 256));
    }
}
