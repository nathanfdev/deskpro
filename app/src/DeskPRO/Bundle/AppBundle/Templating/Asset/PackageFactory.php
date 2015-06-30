<?php

namespace DeskPRO\Bundle\AppBundle\Templating\Asset;

use Application\DeskPRO\NewSettings\SettingsResolver;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Templating\Asset\UrlPackage;
use Symfony\Component\Templating\Asset\PathPackage;

class PackageFactory
{
    /**
     * @var SettingsResolver
     */
    private $settings;

    /**
     * @var RequestStack
     */
    private $request_stack;

    /**
     * @param SettingsResolver $settings
     * @param RequestStack $request_stack
     */
    function __construct(SettingsResolver $settings, RequestStack $request_stack)
    {
        $this->settings = $settings;
        $this->request_stack = $request_stack;
    }

    /**
     * @param string $path
     * @return string
     */
    private function autoGenPath($path)
    {
        $req = $this->request_stack->getCurrentRequest();

        if ($req) {
            return $req->getBasePath() . '/' . $path;
        } else {
            return $this->settings->getGlobalSettings()->get('deskpro.core.settings') . $path;
        }
    }

    /**
     * @param string $path
     * @return string
     */
    private function getBaseUrl($path)
    {
        if ($path === 'pub/build' && ($config_url = $this->settings->getGlobalSettings()->get('pub_asset_url'))) {
            $config_url = rtrim($config_url, '/');
            return $config_url . '/' . $path;
        }

        return $this->autoGenPath($path);
    }

    /**
     * @param $path
     * @return int|null
     */
    private function getVersion($path)
    {
        switch ($path) {
            case 'pub/build':
                return defined('DP_BUILD_TIME') ? DP_BUILD_TIME : 0;
            default:
                return null;
        }
    }

    /**
     * @param string $path
     */
    public function createPackageForPath($path, $ssl = false)
    {
        $path = trim($path, '/');

        $use_path = $this->getBaseUrl($path);
        $version = $this->getVersion($path);

        if (($use_path[0] === '/' && $use_path[1] === '/') || preg_match('#^http(s)?://#i', $use_path)) {
            return new UrlPackage($use_path, $version, '%s?%s');
        } else {
            return new PathPackage($use_path, $version, '%s?%s');
        }
    }
}