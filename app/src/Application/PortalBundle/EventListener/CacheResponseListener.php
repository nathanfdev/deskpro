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

use Application\PortalBundle\HttpKernel\PortalCacheHelper;
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
class CacheResponseListener implements EventSubscriberInterface
{
    /**
     * @var PortalCacheHelper
     */
    private $portal_cache_helper;


    public function __construct(PortalCacheHelper $portal_cache_helper)
    {
        $this->portal_cache_helper = $portal_cache_helper;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $response = $event->getResponse();
        if ($this->portal_cache_helper->isGuestRequest()) {

            // all master guest responses, remember - these responses NEVER have an ESI tag in them (always inlined)
            $response->setPublic();
            $response->setSharedMaxAge(600);
            //$response->setMaxAge(600); // commented out for dev purposes (so I can see live debug info in the browser)

        } else {

            $response->setPrivate();
            $response->setSharedMaxAge(0);
            $response->setMaxAge(0);

        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array('onKernelResponse')
        );
    }
}
