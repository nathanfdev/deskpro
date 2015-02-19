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

namespace Application\AppBundle\EventListener;

use Application\AppBundle\Helper\UrlHostChecker;
use Application\DeskPRO\Brand\BrandStack;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RedirectProtectionListener implements EventSubscriberInterface
{
    const ALLOW_REDIRECT_OFFSITE_HEADER = 'X-DeskPRO-Redirect-Offsite';

    /**
     * @var BrandStack
     */
    private $brand_stack;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UrlHostChecker
     */
    private $url_host_checker;

    public function __construct(BrandStack $brand_stack, UrlHostChecker $url_host_checker, LoggerInterface $logger)
    {
        $this->brand_stack = $brand_stack;
        $this->url_host_checker = $url_host_checker;
        $this->logger = $logger;
    }

    public function onResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $request  = $event->getRequest();

        // you can bypass this check if you set the right header
        // onto the redirect response object (ie. in the controller)
        if (
            $response->isRedirection()
            && !$response->headers->get(self::ALLOW_REDIRECT_OFFSITE_HEADER, false)
        ) {
            $location = $response->headers->get('Location');

            if ($this->url_host_checker->isMatch(
                $location,
                $request->getHost(),
                $request->getPort()
            )) {
                return; // matches current request
            }

            $deskpro_url_setting = $this->getBrandSetting('core.deskpro_url');
            if (
                $deskpro_url_setting
                && $this->url_host_checker->isMatchUrl($location, $deskpro_url_setting)
            ) {
                return; // matches brand settings
            }


            $this->logger->info(sprintf('invalid redirect detected: attempted to redirect to unauthorized host "%s"', $location));

            $event->setResponse(new Response(
                '<html><body>Invalid Redirect</body></html>',
                Response::HTTP_FORBIDDEN
            ));

            return;
        }
        // go ahead and remove the custom header, it wont be needed beyond this point
        if ($response->headers->get(self::ALLOW_REDIRECT_OFFSITE_HEADER, false)) {
            $response->headers->remove(self::ALLOW_REDIRECT_OFFSITE_HEADER);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array('onResponse')
        );
    }

    private function getBrandSetting($setting_name)
    {
        return $this->brand_stack->getActive()->getSetting($setting_name, null);
    }
}
