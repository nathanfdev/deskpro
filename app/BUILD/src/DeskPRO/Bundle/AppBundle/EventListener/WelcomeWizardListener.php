<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\EventListener;

use DeskPRO\Bundle\AppBundle\Request\RequestUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Redirects to the admin 'start' page after install.
 */
final class WelcomeWizardListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest', -200],
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        if (!(
            $event->isMasterRequest()
            && !RequestUtils::isLowRequest($event->getRequest())
            && $event->getRequest()->getMethod() === 'GET'
            && !$event->getRequest()->isXmlHttpRequest()
            && strpos($event->getRequest()->getPathInfo(), '/api') === false
            && strpos($event->getRequest()->getPathInfo(), '/login') === false
            && strpos($event->getRequest()->getPathInfo(), '/admin/start') === false
        )
        ) {
            return;
        }

        $settings = $this->container->get('settings_resolver');
        if (!$settings->getGlobalSettings()->get('core.setup_initial')) {
            $event->setResponse(new RedirectResponse($event->getRequest()->getBaseUrl().'/admin/start'));
            $event->stopPropagation();

            return;
        }
    }
}
