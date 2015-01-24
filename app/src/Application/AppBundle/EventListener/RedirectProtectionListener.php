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

    public function __construct(BrandStack $brand_stack, LoggerInterface $logger)
    {
        $this->brand_stack = $brand_stack;
        $this->logger = $logger;
    }

    public function onResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $request  = $event->getRequest();

        // you can bypass this check if you set the right header onto the redirect response object (ie. in the controller)
        if ($response->isRedirection() && !$response->headers->get(self::ALLOW_REDIRECT_OFFSITE_HEADER, false)) {
            $location = $response->headers->get('Location');

            // check current request
            if ($this->checkHostPortMatch($location, $request->getHost(), $request->getPort())) {
                return; // match!
            }

            // if current request fails, check settings for acceptable host-port combo
            if ($deskpro_url_setting = $this->brand_stack->getActive()->getSetting('core.deskpro_url', null)) {
                $deskpro_url_host = parse_url($deskpro_url_setting, PHP_URL_HOST);
                $deskpro_url_port = parse_url($deskpro_url_setting, PHP_URL_PORT);

                // ensure there is actually a url to check in settings
                if (null !== $deskpro_url_host) {
                    if ($this->checkHostPortMatch($location, $deskpro_url_host, $deskpro_url_port)) {
                        return; // match!
                    }
                }
            }

            $this->logger->info(sprintf('invalid redirect detected: attempted to redirect to unauthorized host "%s"', $location));

            // set the response and return
            $error_response = new Response('<html><body>Invalid Redirect</body></html>', Response::HTTP_FORBIDDEN);
            $event->setResponse($error_response);

            return;
        }

        // go ahead and remove the custom header, it wont be needed beyond this point
        if ($response->headers->get(self::ALLOW_REDIRECT_OFFSITE_HEADER, false)) {
            $response->headers->remove(self::ALLOW_REDIRECT_OFFSITE_HEADER);
        }
    }

    protected function checkHostPortMatch($destination_url, $request_host, $request_port)
    {
        if (null === $request_port) {
            $request_port = 80; // default to port 80
        }

        $dest_host = parse_url($destination_url, PHP_URL_HOST);
        $dest_port = parse_url($destination_url, PHP_URL_PORT);

        if (!$dest_port) {
            $dest_port = 80;
        }

        if (null === $dest_host) {
            return true; // url does not contain host info, so it is an absolute url redirect (example: "/news")
        }

        return $dest_host === $request_host && (int) $request_port === $dest_port;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array('onResponse')
        );
    }
}
