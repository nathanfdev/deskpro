<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\EventListener;

use Application\DeskPRO\NewSettings\SettingsBag;
use DpSys\License;
use DpSys\LowError\SystemErrorHandler;
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
        return [
            // above when Security is run because we need the lic to check auto-agent
            KernelEvents::REQUEST  => ['onPreRequest', 10],
            ConsoleEvents::COMMAND => ['onCommand', 0],
        ];
    }

    public function onPreRequest(GetResponseEvent $event)
    {
        $me = $this;
        License::setLoaderFunction(function () use ($me) {
            return $me->licenseLoader();
        });
    }

    public function onCommand(ConsoleCommandEvent $event)
    {
        $me = $this;
        License::setLoaderFunction(function () use ($me) {
            return $me->licenseLoader();
        });
    }

    /**
     * @internal
     *
     * @return array
     */
    public function licenseLoader()
    {
        if (defined('DP_LIC_FILE')) {
            $licenseCode = file_get_contents(DP_LIC_FILE);
        } elseif (defined('DP_LIC_STR')) {
            $licenseCode = DP_LIC_STR;
        } else {
            $licenseCode = $this->getSetting('core.license');
        }

        if (defined('DP_INSTALL_KEY')) {
            $installKey = DP_INSTALL_KEY;
        } else {
            $installKey = $this->getSetting('core.install_key');
        }

        return [
            'license_code' => $licenseCode,
            'install_key'  => $installKey,
        ];
    }

    /**
     * @param string $id
     *
     * @return string
     */
    private function getSetting($id)
    {
        global $DP_ENV;
        $settingsConfig = $DP_ENV->getConfig('settings', []);

        if (isset($settingsConfig[$id])) {
            return $settingsConfig[$id];
        }

        if (!$this->container || !$this->container->has('settings_resolver')) {
            SystemErrorHandler::logException(new \RuntimeException('Calling on License before container is available'));

            return null;
        }

        /** @var SettingsBag $settings */
        $settings = $this->container->get('settings_resolver')->getGlobalSettings();

        return $settings->get($id, '');
    }
}
