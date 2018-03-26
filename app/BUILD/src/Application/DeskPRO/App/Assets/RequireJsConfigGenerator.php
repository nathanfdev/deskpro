<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\App\Assets;

use Application\DeskPRO\App\AppManagerInterface;
use Application\DeskPRO\Assets\RequireJsConfigGenerator as BaseRequireJsConfigGenerator;

class RequireJsConfigGenerator extends BaseRequireJsConfigGenerator
{
    public function __construct(AppManagerInterface $manager, $native_file_root = null, $only_installed = true)
    {
        foreach ($manager->getAllPackages() as $package) {
            // No app is installed, dont need to output rjs map for it
            if ($only_installed && !count($manager->getPackageApps($package->name))) {
                continue;
            }

            if ($native_file_root && $package->native_name) {
                $native_baseurl = $native_file_root.'/'.$package->native_name;
            } else {
                $native_baseurl = null;
            }

            $appAsset = $package->getTaggedAsset('app_js');
            $name     = "{$package->name}/app";

            if ($appAsset) {
                if ($native_baseurl) {
                    $appjs_path = preg_replace('#\.js$#', '', $native_baseurl.'/app/app.js');
                } else {
                    $appjs_path = preg_replace('#\.js$#', '', $appAsset->blob->getDownloadUrl(false, false));
                }
                $this->addPath($name, $appjs_path);
            }

            $moduleAsset = $package->getTaggedAsset('module_js');
            $name        = "{$package->name}/module";

            if ($moduleAsset) {
                if ($native_baseurl) {
                    $modulejs_path = preg_replace('#\.js$#', '', $native_baseurl.'/app/module.js');
                } else {
                    $modulejs_path = preg_replace('#\.js$#', '', $moduleAsset->blob->getDownloadUrl(false, false));
                }
                $this->addPath($name, $modulejs_path);
            }

            // If its a native app, then we can get away with just using the prefix path
            if ($native_baseurl) {
                $name       = $package->name;
                $asset_path = $native_baseurl.'/js';
                $this->addPath($name, $asset_path);

            // Otherwise, we need to use the download URL that will contain unique auth codes
            } else {
                foreach ($package->getTaggedAssets('js') as $asset) {
                    // JS assets can be named without the '/js/' part, so we have $name and $name2 for legacy
                    $name  = $package->name.'/'.str_replace('.js', '', $asset->name);
                    $name2 = $package->name.'/js/'.str_replace('.js', '', $asset->name);

                    $asset_path = preg_replace('#\.js$#', '', $asset->blob->getDownloadUrl(false, false));

                    $this->addPath($name, $asset_path);
                    $this->addPath($name2, $asset_path);
                }
            }
        }
    }
}
