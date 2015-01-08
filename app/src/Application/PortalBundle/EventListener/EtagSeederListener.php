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

namespace Application\PortalBundle\EventListener;

use Application\AppBundle\Http\Cache\EtagManager;
use Application\DeskPRO\Brand\BrandStack;
use Application\DeskPRO\EntityRepository\Brand;
use Application\DeskPRO\Entity\Brand as BrandEntity;
use Application\DeskPRO\NewSettings\SettingsResolver;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Early in a request on the portal, we decide on a generic etag "seed" for this user (guest, or authenticated usergroup ids)
 */
class EtagSeederListener implements EventSubscriberInterface
{
    /**
     * @var EtagManager
     */
    private $etag_manager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;


    public function __construct(EtagManager $etag_manager, LoggerInterface $logger)
    {
        $this->etag_manager = $etag_manager;
        $this->logger = $logger;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // only run this on the master request - we only calc once per request.
            //return;
        }

        $event->getRequest()->attributes->set('etag_seed', $this->etag_manager->getEtagForCurrentUser());
    }

    public static function getSubscribedEvents()
    {
        return array(
            // low prioirty, let security and filtering happen first
            KernelEvents::REQUEST => array('onKernelRequest', -12)
        );
    }
}
