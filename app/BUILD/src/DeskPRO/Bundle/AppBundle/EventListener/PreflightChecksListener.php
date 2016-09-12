<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Runs basic checks to determine if we should go to the install screen.
 */
class PreflightChecksListener implements EventSubscriberInterface
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
            KernelEvents::REQUEST => ['onPreRequest', 3000],
        ];
    }

    public function onPreRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        // Missing PDO
        if (!extension_loaded('pdo')) {
            $r = new RedirectResponse($request->getBasePath().'/index.php/install/');
            $r->headers->set('X-DeskPRO-InstallRedirectReason', 'Missing PDO ext');
            $event->setResponse($r);
            $event->stopPropagation();

            return;
        }

        // Bad DB connection
        try {
            $settings = $this->container->get('settings_resolver')->getGlobalSettings(true);
        } catch (\Doctrine\DBAL\DBALException $e) {
            /* @var \DpRun\DpEnv $DP_ENV */
            global $DP_ENV;

            if (!$DP_ENV->getConfig('database.host') && !$DP_ENV->getConfig('database.0.host')) {
                $r = new Response('DeskPRO is not installed.');
                $r->headers->set('X-DeskPRO-InstallRedirectReason', 'Database error: '.$e->getMessage().' -- '.$e->getCode());
                $event->setResponse($r);
                $event->stopPropagation();

                return;
            } else {
                throw $e;
            }
        }

        // Not installed yet
        if (!$settings->get('core.install_build')) {
            $r = new RedirectResponse($request->getBasePath().'/index.php/install/');
            $r->headers->set('X-DeskPRO-InstallRedirectReason', 'Missing build number');
            $event->setResponse($r);
            $event->stopPropagation();

            return;
        }
    }
}
