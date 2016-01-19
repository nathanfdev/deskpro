<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

use DeskPRO\Bundle\AppBundle\HttpKernel\DpKernelEvents;
use DeskPRO\Bundle\AppBundle\HttpKernel\Event\GetPreResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
        return array(
            DpKernelEvents::PRE_REQUEST => array('onPreRequest', 3000),
        );
    }

    public function onPreRequest(GetPreResponseEvent $event)
    {
        $request = $event->getRequest();

        // Missing PDO
        if (!deskpro_install_check_pdo_mysql()) {
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
            global $DP_CONFIG;
            if ($e->getCode() == '42S02' || @$DP_CONFIG['db']['user'] == 'YOUR_DATABASE_USER' || @$DP_CONFIG['db']['password'] == 'YOUR_DATABASE_PASS' || @$DP_CONFIG['db']['dbname'] == 'YOUR_DATABASE_NAME') {
                // This will show an error page if already installed, so the redirect to install wont happen
                deskpro_handle_boot_db_exception($e);

                $r = new RedirectResponse($request->getBasePath().'/index.php/install/');
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
