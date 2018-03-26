<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use DeskPRO\Bundle\PortalBundle\Visitor\VisitorIdentificationProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Ensures an accurate visitor identifier is always present in the VisitorIdentificationProvider by hooking into the HttpKernel lifecycle.
 */
class VisitorIdentificationListener implements EventSubscriberInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \DeskPRO\Bundle\PortalBundle\Visitor\VisitorIdentificationProvider
     */
    private $visitor_provider;

    /**
     * Constructor.
     *
     * @param VisitorIdentificationProvider $visitor_provider
     * @param LoggerInterface               $logger
     */
    public function __construct(VisitorIdentificationProvider $visitor_provider, LoggerInterface $logger)
    {
        $this->logger           = $logger;
        $this->visitor_provider = $visitor_provider;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        // this does two things: 1. gets, or create if doesn't exist, a visitor identifier, and 2. set it as a request attribute
        $identifier = $this->visitor_provider->getVisitorIdentifier();
        $this->logger->info(sprintf('setting request attribute "%s" with visitor identifier "%s"', VisitorIdentificationProvider::ATTRIBUTE_NAME, $identifier));
        $event->getRequest()->attributes->set(
            VisitorIdentificationProvider::ATTRIBUTE_NAME,
            $identifier
        );
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $response   = $event->getResponse();
        $identifier = $this->visitor_provider->getVisitorIdentifier();

        // always set a non-expiring cookie during the response with the visitor identifier
        $cookie = new Cookie(
            VisitorIdentificationProvider::COOKIE_NAME,
            $identifier,
            new \DateTime('now + 5 years'),
            '/',
            null,
            false,
            false
        );
        $response->headers->setCookie($cookie);
        $this->logger->debug(sprintf('set cookie "%s" with visitor identifier "%s" - %s', VisitorIdentificationProvider::COOKIE_NAME, $identifier, $cookie));
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST  => ['onKernelRequest', 129],
            KernelEvents::RESPONSE => ['onKernelResponse'],
        ];
    }
}
