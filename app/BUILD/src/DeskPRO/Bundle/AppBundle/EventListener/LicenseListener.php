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

use DpSys\License;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Inits the license loader for lic check that is done on every request. Removing/disabling this
 * will result in a bad license key being set, which will make the system stop working.
 */
class LicenseListener implements EventSubscriberInterface
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
            KernelEvents::REQUEST  => ['onPreRequest', 0],
            ConsoleEvents::COMMAND => ['onCommand', 0],
        );
    }

    public function onPreRequest(GetResponseEvent $event)
    {
        $container = $this->container;
        $me        = $this;
        License::setLoaderFunction(function () use ($me, $container) {
            return $me->licenseLoader($container);
        });
    }

    public function onCommand(ConsoleCommandEvent $event)
    {
        $container = $this->container;
        $me        = $this;
        License::setLoaderFunction(function () use ($me, $container) {
            return $me->licenseLoader($container);
        });
    }

    /**
     * @internal
     *
     * @param ContainerInterface $container
     *
     * @return array
     */
    public function licenseLoader(ContainerInterface $container)
    {
        $settings = $container->get('settings_resolver')->getGlobalSettings();

        if (defined('DP_LIC_FILE')) {
            $license_code = file_get_contents(DP_LIC_FILE);
        } elseif (defined('DP_LIC_STR')) {
            $license_code = DP_LIC_STR;
        } else {
            $license_code = $settings->get('core.license');
        }

        if (defined('DP_INSTALL_KEY')) {
            $install_key = DP_INSTALL_KEY;
        } else {
            $install_key = $settings->get('core.install_key');
        }

        $licopt = $settings->get('core.licenseopt');
        if ($licopt) {
            $license_code .= '#'.$licopt;
        }

        return [
            'license_code' => $license_code,
            'install_key'  => $install_key,
        ];
    }
}
