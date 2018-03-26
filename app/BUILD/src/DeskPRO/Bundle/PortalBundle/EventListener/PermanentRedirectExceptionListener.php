<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use DeskPRO\Bundle\PortalBundle\HttpKernel\Exception\PermanentRedirectException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * If anyone throws a PermanentRedirectException, we catch it here to return a redirect response to the kernel.
 */
class PermanentRedirectExceptionListener implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     */
    private $url_generator;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param UrlGeneratorInterface $url_generator
     * @param LoggerInterface       $logger
     */
    public function __construct(UrlGeneratorInterface $url_generator, LoggerInterface $logger)
    {
        $this->url_generator = $url_generator;
        $this->logger        = $logger;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $e = $event->getException();

        // only interested in a particular exception here
        if (!$e instanceof PermanentRedirectException) {
            return;
        }

        $url = $this->url_generator->generate(
            $e->getRouteName(),
            $e->getRouteParams(),
            $e->getUrlType()
        );

        $this->logger->info('PermanentRedirectException caught: 301 redirecting to "'.$url.'"');

        $event->setResponse(new RedirectResponse($url, Response::HTTP_MOVED_PERMANENTLY));
        $event->stopPropagation();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 129], // very high priority
        ];
    }
}
