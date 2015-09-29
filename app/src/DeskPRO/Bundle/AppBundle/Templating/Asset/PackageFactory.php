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

namespace DeskPRO\Bundle\AppBundle\Templating\Asset;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Config\DeskproConfigService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Templating\Asset\PathPackage;
use Symfony\Component\Templating\Asset\UrlPackage;

class PackageFactory
{
    /**
     * @var SettingsResolver
     */
    private $settings;

    /**
     * @var DeskproConfigService
     */
    private $config;

    /**
     * @var RequestStack
     */
    private $request_stack;

    /**
     * @param SettingsResolver     $settings
     * @param DeskproConfigService $config
     * @param RequestStack         $request_stack
     */
    public function __construct(SettingsResolver $settings, DeskproConfigService $config, RequestStack $request_stack)
    {
        $this->settings      = $settings;
        $this->config        = $config;
        $this->request_stack = $request_stack;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function autoGenPath($path)
    {
        $req = $this->request_stack->getCurrentRequest();

        if ($req) {
            return $req->getBasePath().'/'.$path;
        } else {
            return $this->settings->getGlobalSettings()->get('deskpro.core.settings').$path;
        }
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function getBaseUrl($path)
    {
        $config_url      = $this->config->getConfigValue('pub_assets_base_url');
        $config_urls_map = $this->config->getConfigValue('pub_asset_urls') ?: array();

        if ($path === 'pub/build' && ($config_url || !empty($config_urls_map[$path]))) {
            if (!empty($config_urls_map[$path])) {
                return rtrim($config_urls_map[$path], '/');
            } else {
                return rtrim($config_url, '/').'/'.$path;
            }
        }

        return $this->autoGenPath($path);
    }

    /**
     * @param $path
     *
     * @return int|null
     */
    private function getVersion($path)
    {
        switch ($path) {
            case 'pub/build':
                return defined('DP_BUILD_TIME') ? DP_BUILD_TIME : 0;
            default:
                return;
        }
    }

    /**
     * @param string $path
     */
    public function createPackageForPath($path, $ssl = false)
    {
        $path = trim($path, '/');

        $use_path = $this->getBaseUrl($path);
        $version  = $this->getVersion($path);

        if (($use_path[0] === '/' && $use_path[1] === '/') || preg_match('#^http(s)?://#i', $use_path)) {
            return new UrlPackage($use_path, $version, '%s?%s');
        } else {
            return new PathPackage($use_path, $version, '%s?%s');
        }
    }
}
