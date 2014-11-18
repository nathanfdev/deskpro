<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\PortalBundle\EventListener;

use Symfony\Component\HttpFoundation\Cookie;
use Application\PortalBundle\Visitor\VisitorIdentificationProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;


/**
 * Ensures an accurate visitor identifier is always present in the VisitorIdentificationProvider by hooking into the HttpKernel lifecycle
 */
class VisitorIdentificationListener implements EventSubscriberInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Application\PortalBundle\Visitor\VisitorIdentificationProvider
     */
    private $visitor_provider;


    public function __construct(VisitorIdentificationProvider $visitor_provider, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->visitor_provider = $visitor_provider;
    }


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

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $response = $event->getResponse();
        $identifier = $this->visitor_provider->getVisitorIdentifier();

        // always set a non-expiring cookie during the response with the visitor identifier
        $cookie = new Cookie(
            VisitorIdentificationProvider::COOKIE_NAME,
            $identifier,
            new \DateTime('now + 5 years')
        );
        $response->headers->setCookie($cookie);
        $this->logger->debug(sprintf('set cookie "%s" with visitor identifier "%s" - %s', VisitorIdentificationProvider::COOKIE_NAME, $identifier, $cookie));
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST  => array('onKernelRequest', 129),
            KernelEvents::RESPONSE => array('onKernelResponse')
        );
    }
}
