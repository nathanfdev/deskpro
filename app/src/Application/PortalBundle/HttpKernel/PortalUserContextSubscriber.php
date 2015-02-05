<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\PortalBundle\HttpKernel;

use FOS\HttpCache\UserContext\HashGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Ensure that cache headers are ONLY sent in the event of a GUEST user-context-hash request
 */
class PortalUserContextSubscriber implements EventSubscriberInterface
{
    /**
     * @var RequestMatcherInterface
     */
    private $requestMatcher;

    /**
     * @var string
     */
    private $hashHeader;

    /**
     * @var PortalCacheHelper
     */
    private $cache_helper;

    public function __construct(
        RequestMatcherInterface $requestMatcher,
        PortalCacheHelper $cache_helper,
        $hashHeader = "X-User-Context-Hash"
    )
    {
        $this->requestMatcher = $requestMatcher;
        $this->hashHeader = $hashHeader;
        $this->cache_helper = $cache_helper;
    }

    /**
     * Turn off cache headers if the hash is NOT a guest hash
     *
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($event->getRequestType() != HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        // ensure its a user-context-hash request
        if (!$this->requestMatcher->matches($event->getRequest())) {
            return;
        }

        $response = $event->getResponse();

        // ensure the response has the hash
        if (!$response->headers->has($this->hashHeader)) {
            return;
        }

        $hash = $response->headers->get($this->hashHeader);

        if ($this->cache_helper->isGuestHash($hash)) {
            return; // leave the response as is, this is a guest
        }

        // undo the UserContextSubscriber by making it a no-cache response
        $response->setClientTtl(0);
        $response->headers->addCacheControlDirective('no-cache');

        // users with a session will now always ask for the hash, since our proxy wont cache this $response
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array('onKernelResponse', 256) // run this right after the UserContextsubscriber
        );
    }
}
