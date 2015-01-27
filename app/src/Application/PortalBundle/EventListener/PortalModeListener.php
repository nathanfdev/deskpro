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
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        // an internal request (e.g. /_proxy?_path=x&foo=bar) is still a master request
        // these (from tags) have a query string serialized representation of the mode
        // we need to mkae sure this is in the portal_mode_storage service, too!
        $path = rawurldecode($request->getPathInfo());
        if ('/_proxy' === substr($path, 0, 7)) {
            $this->processInternalRequest($event);
            return;
        }

        if ('/_' === substr($path, 0, 2)) {
            // ignore any other path that starts with _ (profiler and such)
            return;
        }

        $mode = $this->factory->createMode($request->getPathInfo());

        $this->store->setMode($mode);
    }

    protected function processInternalRequest(GetResponseEvent $event)
    {
        // tags store the mode as a urlencoded serialized mode object in the /_proxy query string
        $query_string = $event->getRequest()->query;
        if ($query_string->has(PortalMode::ATTR_NAME)) {
            if ($serialized_mode = $query_string->get(PortalMode::ATTR_NAME)) {
                $mode = unserialize(urldecode($serialized_mode));
                $this->store->setMode($mode);

                return true;
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(KernelEvents::REQUEST => array('onKernelRequest', 256));
    }
}
