<?php

namespace DeskPRO\Bundle\BrandBundle\EventListener;

use DeskPRO\Bundle\AppBundle\HttpKernel\SkipLowRequestInterface;
use DeskPRO\Bundle\BrandBundle\Request\RequestBrandCorrector;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class BrandSlugListener.
 */
class BrandSlugListener implements EventSubscriberInterface, SkipLowRequestInterface
{
    /**
     * @var RequestBrandCorrector
     */
    private $brandCorrector;

    /**
     * Constructor.
     *
     * @param RequestBrandCorrector $brandCorrector
     */
    public function __construct(RequestBrandCorrector $brandCorrector)
    {
        $this->brandCorrector = $brandCorrector;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            // high priority, hack request before anything
            KernelEvents::REQUEST => ['onKernelRequest', 10000],
        ];
    }

    /**
     * @internal
     *
     * @param GetResponseEvent $event
     *
     * @throws NotFoundHttpException
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->brandCorrector->patchRequest($event->getRequest());
    }
}
