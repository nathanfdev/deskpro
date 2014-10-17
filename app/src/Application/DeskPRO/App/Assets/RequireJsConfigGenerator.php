<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\App\Assets;

use Application\DeskPRO\App\AppManagerInterface;
use Application\DeskPRO\Assets\RequireJsConfigGenerator as BaseRequireJsConfigGenerator;

class RequireJsConfigGenerator extends BaseRequireJsConfigGenerator
{
	public function __construct(AppManagerInterface $manager, $native_file_root = null)
	{
		foreach ($manager->getAllPackages() as $package) {
			if ($native_file_root && $package->native_name) {
				$native_baseurl = $native_file_root . '/' . $package->native_name;
			} else {
				$native_baseurl = null;
			}

			$appAsset = $package->getTaggedAsset('app_js');
			$name = "{$package->name}/app";

			if ($appAsset) {
				if ($native_baseurl) {
					$appjs_path = preg_replace('#\.js$#', '', $native_baseurl . '/app/app.js');
				} else {
					$appjs_path = preg_replace('#\.js$#', '', $appAsset->blob->getDownloadUrl(false, false));
				}
				$this->addPath($name, $appjs_path);
			}

			// If its a native app, then we can get away with just using the prefix path
			if ($native_baseurl) {
				$name = $package->name;
				$asset_path = $native_baseurl . '/js';
				$this->addPath($name, $asset_path);

			// Otherwise, we need to use the download URL that will contain unique auth codes
			} else {
				foreach ($package->getTaggedAssets('js') as $asset) {
					$name = $package->name . '/js/' . str_replace('.js', '', $asset->name);
					$asset_path = preg_replace('#\.js$#', '', $asset->blob->getDownloadUrl(false, false));

					$this->addPath($name, $asset_path);
				}
			}
		}
	}
}