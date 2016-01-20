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

namespace DeskPRO\Bundle\AppBundle\Assets;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Config\DeskproConfigService;
use DeskPRO\Component\Util\MapUtils;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;
use Symfony\Component\HttpFoundation\RequestStack;

class PackagesFactory
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
     * Array of [path => [[type, value, version], [type, value, version]].
     *
     * @var array
     */
    private $asset_paths;

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

        $asset_paths = $this->config->getConfigValue('asset_paths') ?: [];

        // shortcut used by devs -- its what gulp tells you to use
        $config_urls_map = $this->config->getConfigValue('pub_asset_urls') ?: array();
        if (!empty($config_urls_map)) {
            foreach ($config_urls_map as $path => $url) {
                if ($path === 'pub/build') {
                    $asset_paths['app_assets'] = [
                        'type'    => 'url',
                        'value'   => $url,
                        'version' => 'build',
                    ];
                }
            }
        }

        if ($asset_paths) {
            $this->asset_paths = MapUtils::map($asset_paths, function ($id, $p) {
                // Should be an array of arrays
                return [$id, PathMapInfo::createFromArray($p)];
            });
        } else {
            $this->asset_paths = [];
        }

        if (empty($this->asset_paths['legacy_web'])) {
            $this->asset_paths['legacy_web'] = PathMapInfo::create()->setDeskproPath('/web');
        }

        if (empty($this->asset_paths['vendor_assets'])) {
            $this->asset_paths['vendor_assets'] = PathMapInfo::create()
                ->setDeskproPath('/pub/node_modules')
                ->setVersion('build');
        }

        if (empty($this->asset_paths['app_assets'])) {
            $this->asset_paths['app_assets'] = PathMapInfo::create()
                ->setDeskproPath('/pub/build')
                ->setVersion('build');
        }
    }

    /**
     * @return Packages
     */
    public function createPackages()
    {
        $asset_packs = [];
        foreach ($this->asset_paths as $id => $p) {
            $asset_packs[$id] = $this->getAssetPackage($p);
        }

        $default = $this->getAssetPackage(PathMapInfo::create()->setDeskproPath('/web'));

        return new Packages($default, $asset_packs);
    }

    /**
     * @param PathMapInfo $p
     *
     * @return \Symfony\Component\Asset\Package[]
     */
    private function getAssetPackage(PathMapInfo $p)
    {
        $req = $this->request_stack->getCurrentRequest();

        //------------------------------
        // Create verison strat
        //------------------------------

        switch ($p->getVersion()) {
            case PathMapInfo::BUILD_VERSION:
                if (defined(DP_BUILD_TIME)) {
                    $version = new StaticVersionStrategy(DP_BUILD_TIME);
                } else {
                    $version = new StaticVersionStrategy(time());
                }
                break;

            case PathMapInfo::NO_VERSION:
                $version = new EmptyVersionStrategy();
                break;

            default:
                $version = new StaticVersionStrategy($p->getVersion());
        }

        //------------------------------
        // Create path pack
        //------------------------------

        if ($p->isDeskproPath() || $p->isRootPath()) {
            if ($req) {
                if ($p->isRootPath()) {
                    $pack = new PathPackage($p->getPath(), $version);

                    return $pack;
                } else {
                    $pack = new PathPackage(rtrim($req->getBasePath(), '/').$p->getPath(), $version);

                    return $pack;
                }
            } else {
                // no request means we need to use a URL (e.g., sending an email)
                $url = $this->settings->getGlobalSettings()->get('deskpro.core.settings');
                if ($p->isRootPath()) {
                    // Strip the path part
                    $url = preg_replace('#^(https?://)([^/]+)(/.*)$#', '$1$2', $url);
                }

                $url  = rtrim($url, '/');
                $pack = new UrlPackage($url.$p->getPath(), $version);

                return $pack;
            }

        //------------------------------
        // Create URL pack(s)
        //------------------------------
        } else {
            $urls  = $p->getAllUrls();
            $packs = new UrlPackage($urls, $version);

            return $packs;
        }
    }
}
