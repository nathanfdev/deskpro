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
