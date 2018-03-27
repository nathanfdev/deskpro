<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\EventListener;

use DeskPRO\Bundle\AppBundle\Request\InterfaceInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Sessions are a bit funky in the old bundles where data isnt saved unless
 * its manually written. That's what this does.
 */
class LegacySessionCloseListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var InterfaceInfo
     */
    private $interfaceInfo;

    public function __construct(ContainerInterface $container, InterfaceInfo $interfaceInfo)
    {
        $this->container     = $container;
        $this->interfaceInfo = $interfaceInfo;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', 0],
        ];
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if ($this->interfaceInfo->isAdminInterface() || $this->interfaceInfo->isAgentInterface()) {
            if (session_id() != '') {
                if ($this->container->has('session')) {
                    $this->container->get('session')->save();
                }
                session_write_close();
            }
        }
    }
}
