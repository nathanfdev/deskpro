<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use DeskPRO\Bundle\AppBundle\Helper\UrlHostChecker;
use DeskPRO\Bundle\AppBundle\HttpKernel\SkipLowRequestInterface;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * This response listener inspects redirect responses and rejects off-site redirects unless a speical header is set.
 */
class RedirectProtectionListener implements EventSubscriberInterface, SkipLowRequestInterface
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
        $this->brand_stack      = $brand_stack;
        $this->url_host_checker = $url_host_checker;
        $this->logger           = $logger;
    }

    public function onResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $request  = $event->getRequest();

        // you can bypass this check if you set the right header
        // onto the redirect response object (ie. in the controller)
        if (
            $response->isRedirect() // isRedirect and not isRedirection -- https://github.com/symfony/symfony/issues/12347
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
                // we need to use request's scheme, host and port for redirection instead of brand ones

                $newLocation    = $request->getScheme().'://'.$request->getHost();
                $parsedLocation = parse_url($location);

                // sometimes we get relative location here, so need to use brand port until fixed
                if (parse_url($deskpro_url_setting, PHP_URL_PORT)) {
                    $newLocation .= ':'.$request->getPort();
                }

                $newLocation .= @$parsedLocation['path'];
                if (@$parsedLocation['query']) {
                    $newLocation .= '?'.$parsedLocation['query'];
                }

                $response->setContent(str_replace($location, $newLocation, $response->getContent()));
                $response->headers->set('Location', $newLocation);

                return; // matches brand settings
            }

            $this->logger->info(sprintf('invalid redirect detected: attempted to redirect to unauthorized host "%s" (brand URL is "%s")', $location, $deskpro_url_setting));

            $event->setResponse(new Response(
                sprintf(
                    '<html><body><h1>Invalid Redirect</h1><p>Attempted to redirect to an offsite host.</p><!-- Host: %s, Attempted Target: %s --></body></html>',
                    htmlspecialchars($location),
                    htmlspecialchars($deskpro_url_setting)
                ),
                Response::HTTP_FORBIDDEN
            ));

            return;
        }
        // go ahead and remove the custom header, it wont be needed beyond this point
        if ($response->headers->get(self::ALLOW_REDIRECT_OFFSITE_HEADER, false)) {
            $response->headers->remove(self::ALLOW_REDIRECT_OFFSITE_HEADER);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onResponse'],
        ];
    }

    private function getBrandSetting($setting_name)
    {
        return $this->brand_stack->getActive()->getSetting($setting_name, null);
    }
}
