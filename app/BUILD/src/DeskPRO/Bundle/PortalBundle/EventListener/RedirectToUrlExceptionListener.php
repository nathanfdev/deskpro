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

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use DeskPRO\Bundle\AppBundle\Request\UrlCorrectorFactory;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Routing\RedirectToUrlException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * If anyone throws a RedirectToUrlException, we catch it here to return a redirect response to the kernel.
 */
class RedirectToUrlExceptionListener implements EventSubscriberInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var UrlCorrectorFactory
     */
    private $urlCorrectorFactory;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * Constructor.
     *
     * @param LoggerInterface     $logger
     * @param UrlCorrectorFactory $urlCorrectorFactory
     * @param BrandStack          $brandStack
     */
    public function __construct(
        LoggerInterface     $logger,
        UrlCorrectorFactory $urlCorrectorFactory = null,
        BrandStack          $brandStack = null
    ) {
        $this->logger              = $logger;
        $this->urlCorrectorFactory = $urlCorrectorFactory;
        $this->brandStack          = $brandStack;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 129], // high priority
        ];
    }

    /**
     * @internal
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        // only interested in a particular exception here
        // the PortalRouter throws this
        if (!$exception instanceof RedirectToUrlException) {
            return;
        }

        if (!$this->urlCorrectorFactory || !$this->brandStack) {
            return;
        }

        $url      = $exception->getUrl();
        $brand    = $this->brandStack->getActive()->getBrand();
        $request  = $event->getRequest();
        $brandUrl = $this->urlCorrectorFactory->createUrlCorrector($brand)->getCorrectedHelpdeskUrl($request);

        $url = rtrim($brandUrl, '/').'/'.ltrim($url, '/');

        $this->logger->info('RedirectToUrlException caught: '.$exception->getMessage().' -- 302 redirecting to "'.$url.'"');

        $response = new RedirectResponse($url, Response::HTTP_FOUND);
        $response->headers->set('X-DeskPRO-RedirectReason', 'RedirectToUrlException: '.$exception->getMessage());

        $event->setResponse($response);
        $event->stopPropagation();
    }
}
