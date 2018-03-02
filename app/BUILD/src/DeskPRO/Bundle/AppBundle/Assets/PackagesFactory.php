<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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
use DeskPRO\Bundle\AppBundle\AppEnv\AppEnv;
use DeskPRO\Component\Util\MapUtils;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class PackagesFactory.
 */
class PackagesFactory
{
    /**
     * @var AppEnv
     */
    private $appEnv;

    /**
     * @var SettingsResolver
     */
    private $settings;

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
     * Array of varname=>value for replacements to put in the URL, if any.
     *
     * @var array
     */
    private $asset_path_replacements;

    /**
     * @param AppEnv           $appEnv
     * @param SettingsResolver $settings
     * @param RequestStack     $request_stack
     * @param array            $asset_paths
     * @param array            $asset_path_vars
     */
    public function __construct(
        AppEnv           $appEnv,
        SettingsResolver $settings,
        RequestStack     $request_stack,
        $asset_paths = [],
        $asset_path_vars = []
    ) {
        $this->appEnv        = $appEnv;
        $this->settings      = $settings;
        $this->request_stack = $request_stack;

        // This should always be set during a normal request
        if (empty($asset_path_vars)) {
            $asset_path_vars['DP_ACTIVE_BUILD'] = defined('DP_ACTIVE_BUILD') ? DP_ACTIVE_BUILD : 'BUILD';
        }

        $this->asset_path_replacements = ['find' => [], 'replace' => []];
        foreach ($asset_path_vars as $k => $v) {
            $this->asset_path_replacements['find'][]    = '%'.$k.'%';
            $this->asset_path_replacements['replace'][] = $v;
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
            $this->asset_paths['legacy_web'] = PathMapInfo::create()
                ->setDeskproPath('/assets/%DP_ACTIVE_BUILD%/web')
                ->setVersion(PathMapInfo::BUILD_VERSION)
            ;
        }

        if (empty($this->asset_paths['vendor_assets'])) {
            $this->asset_paths['vendor_assets'] = PathMapInfo::create()
                ->setDeskproPath('/assets/%DP_ACTIVE_BUILD%/pub/node_modules')
                ->setVersion(PathMapInfo::BUILD_VERSION)
            ;
        }

        if (empty($this->asset_paths['app_assets'])) {
            $this->asset_paths['app_assets'] = PathMapInfo::create()
                ->setDeskproPath('/assets/%DP_ACTIVE_BUILD%/pub/build')
                ->setVersion(PathMapInfo::BUILD_VERSION)
            ;
        }

        if (empty($this->asset_paths['assets_root'])) {
            $this->asset_paths['assets_root'] = PathMapInfo::create()
                ->setDeskproPath('/assets/%DP_ACTIVE_BUILD%')
                ->setVersion(PathMapInfo::BUILD_VERSION)
            ;
        }

        if (empty($this->asset_paths['appsrc_assets'])) {
            $this->asset_paths['appsrc_assets'] = PathMapInfo::create()
                ->setDeskproPath('/assets/%DP_ACTIVE_BUILD%/pub/src')
                ->setVersion(PathMapInfo::BUILD_VERSION)
            ;
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

        $default = $this->getAssetPackage(PathMapInfo::create()->setDeskproPath('/assets/%DP_ACTIVE_BUILD%/web'));

        return new Packages($default, $asset_packs);
    }

    /**
     * @param string $str
     *
     * @return string
     */
    private function replaceVars($str)
    {
        return str_replace(
            $this->asset_path_replacements['find'],
            $this->asset_path_replacements['replace'],
            $str
        );
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
        // Create version strategy
        //------------------------------

        switch ($p->getVersion()) {
            case PathMapInfo::BUILD_VERSION:
                if ($this->appEnv->getConfig('paths.asset_version')) {
                    $version = new DpStaticVersionStrategy($this->appEnv->getConfig('paths.asset_version'));
                } elseif (defined('DP_BUILD_TIME')) {
                    $version = new DpStaticVersionStrategy(DP_BUILD_TIME);
                } else {
                    $version = new DpStaticVersionStrategy(time());
                }
                break;

            case PathMapInfo::NO_VERSION:
                $version = new EmptyVersionStrategy();
                break;

            default:
                $version = new DpStaticVersionStrategy($p->getVersion());
        }

        //------------------------------
        // Create path pack
        //------------------------------

        if ($p->isDeskproPath() || $p->isRootPath()) {
            if ($req) {
                if ($p->isRootPath()) {
                    $pack = new PathPackage($this->replaceVars($p->getPath()), $version);

                    return $pack;
                } else {
                    $pack = new PathPackage($this->replaceVars(rtrim($req->getBasePath(), '/').$p->getPath()), $version);

                    return $pack;
                }
            } else {
                // no request means we need to use a URL (e.g., sending an email)
                $url = $this->settings->getGlobalSettings()->get('deskpro.core.settings');
                if ($p->isRootPath()) {
                    // Strip the path part
                    $url = preg_replace('#^(https?://)([^/]+)(/.*)$#', '$1$2', $url);
                }

                $url = rtrim($url, '/');

                // The URL can be empty if we have no settings
                // or the container is being built for the first time
                // so this is a fallback
                if (!$url) {
                    return new PathPackage($this->replaceVars($p->getPath()), $version);
                }

                $pack = new UrlPackage($this->replaceVars($url.$p->getPath()), $version);

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
