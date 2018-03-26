<?php

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
            // fallback if it's unable to get brand for some reason, redirect to the same host
            $url = trim($event->getRequest()->getSchemeAndHttpHost(), '/').'/'.ltrim($exception->getUrl(), '/');
        } else {
            $url      = $exception->getUrl();
            $brand    = $this->brandStack->getActive()->getBrand();
            $request  = $event->getRequest();
            $brandUrl = $this->urlCorrectorFactory->createUrlCorrector($brand)->getCorrectedHelpdeskUrl($request);

            $url = rtrim($brandUrl, '/').'/'.ltrim($url, '/');
        }

        $this->logger->info('RedirectToUrlException caught: '.$exception->getMessage().' -- 302 redirecting to "'.$url.'"');

        $response = new RedirectResponse($url, Response::HTTP_FOUND);
        $response->headers->set('X-DeskPRO-RedirectReason', 'RedirectToUrlException: '.$exception->getMessage());

        $event->setResponse($response);
        $event->stopPropagation();
    }
}
