<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use Application\DeskPRO\Entity\AppInstance;

class AppsController extends AbstractController
{
	####################################################################################################################
	# list
	####################################################################################################################

	public function listAction()
	{
		$manager = $this->container->getAppManager();

		$apps = array();
		$installed_packages = array();
		foreach ($manager->getAllApps() as $a) {
			$apps[] = $a->toApiData();
			$installed_packages[$a->package->name] = true;
		}

		$packages = array();
		foreach ($manager->getAllPackages() as $p) {
			$p_data = $p->toApiData();
			$p_data['is_installed'] = isset($installed_packages[$p->name]);

			$packages[] = $p_data;
		}

		return $this->createApiResponse(array('packages' => $packages, 'apps' => $apps));
	}


	####################################################################################################################
	# get-package
	####################################################################################################################

	public function getPackageAction($name)
	{
		$manager = $this->container->getAppManager();

		if (!$manager->getPackage($name)) {
			throw $this->createNotFoundException();
		}

		$package = $manager->getPackage($name);

		#------------------------------
		# Get readme
		#------------------------------

		$readme = '';
		$readme_html = '';

		$readme_asset = $package->getTaggedAsset('readme.text');
		if ($readme_asset) {
			$readme = $this->container->getBlobStorage()->copyBlobRecordToString($readme_asset->blob);
		}

		$readme_html_asset = $package->getTaggedAsset('readme.html');
		if ($readme_html_asset) {
			$readme_html = $this->container->getBlobStorage()->copyBlobRecordToString($readme_html_asset->blob);
		}

		$data = $package->toApiData();
		$data['is_installed'] = false;
		$data['readme'] = $readme;
		$data['readme_html'] = $readme_html;

		#------------------------------
		# Get assets
		#------------------------------

		$data['assets'] = array();
		foreach ($package->assets as $asset) {
			$data['assets'][] = $asset->toApiData(false);
		}

		#------------------------------
		# Get installed app instances
		#------------------------------

		$data['apps'] = array();
		foreach ($manager->getPackageApps($package->name) as $app) {
			$data['apps'][] = $app->toApiData(false);
		}

		if ($data['apps']) {
			$data['is_installed'] = true;
		}

		return $this->createApiResponse(array('package' => $data));
	}


	####################################################################################################################
	# delete-package
	####################################################################################################################

	public function deletePackageAction($name)
	{
		$manager = $this->container->getAppManager();

		if (!$manager->getPackage($name)) {
			throw $this->createNotFoundException();
		}

		$package = $manager->getPackage($name);

		if ($package->native_name) {
			return $this->createApiErrorResponse('no_delete_native', "$name is a native application installed into the filesystem and cannot be deleted from the web interface");
		}

		if ($manager->getPackageApps($name)) {
			return $this->createApiErrorResponse('is_installed', "$name has one or more installed instances. Uninstall all instances then delete the app.");
		}

		// Remove all assets from blob storage
		$blob_storage = $this->container->getBlobStorage();
		foreach ($package->assets as $asset) {
			try {
				$blob_storage->deleteBlobRecord($asset->blob);
			} catch (\Exception $e) {}
		}

		$this->em->remove($package);
		$this->em->flush();

		return $this->createApiDeleteResponse(array('old_name' => $name));
	}


	####################################################################################################################
	# install-package
	####################################################################################################################

	public function installPackageAction($name)
	{
		$manager = $this->container->getAppManager();

		if (!$manager->getPackage($name)) {
			throw $this->createNotFoundException();
		}

		$package = $manager->getPackage($name);

		if ($package->is_single && $manager->getPackageApps($name)) {
			return $this->createApiErrorResponse('already_installed', "$name is already installed and the app has is_single=true");
		}

		$app = new AppInstance();
		$app->package = $package;
		$app->title = $this->in->getString('settings.dp_app.title') ?: $package->title;

		$settings = array();
		$settings_form = $this->in->getCleanValueArray('settings');
		foreach ($package->settings_def as $setting_def) {
			$value = isset($settings_form[$setting_def['name']]) ? $settings_form[$setting_def['name']] : null;
			if (!is_scalar($value)) {
				$value = null;
			}

			if ($value !== null) {
				switch ($setting_def) {
					case 'choice':
						$found = false;
						if (isset($setting_def['options'])) {
							foreach ($setting_def['options'] as $opt) {
								if ($opt['value'] == $value) {
									$found = true;
									break;
								}
							}
						}
						if (!$found) {
							$value = null;
						}
						break;

					case 'checkbox':
						if ($value === true || $value === 1 || $value === "1" || $value === "true") {
							$value = true;
						} else {
							$value = false;
						}
						break;
				}
			}

			if ($value === null && isset($setting_def['default_value'])) {
				$value = $setting_def['default_value'];
			}

			if ($value !== null) {
				$settings[$setting_def['name']] = $value;
			}
		}

		if ($settings) {
			$app->setSettings($settings);
		}

		$this->em->persist($app);
		$this->em->flush();

		return $this->createApiCreateResponse(
			array('id' => $app->id),
			$this->generateUrl('api_apps_instance', array('id' => $app->id))
		);
	}


	####################################################################################################################
	# get-instance
	####################################################################################################################

	public function getInstanceAction($id)
	{
		$manager = $this->container->getAppManager();

		if (!$manager->hasApp($id)) {
			throw $this->createNotFoundException();
		}

		$app = $manager->getApp($id);
		$package = $app->package;

		$data = $app->toApiData();

		#------------------------------
		# Get readme
		#------------------------------

		$readme = '';
		$readme_html = '';

		$readme_asset = $package->getTaggedAsset('readme.text');
		if ($readme_asset) {
			$readme = $this->container->getBlobStorage()->copyBlobRecordToString($readme_asset->blob);
		}

		$readme_html_asset = $package->getTaggedAsset('readme.html');
		if ($readme_html_asset) {
			$readme_html = $this->container->getBlobStorage()->copyBlobRecordToString($readme_html_asset->blob);
		}

		$data['package'] = $package->toApiData();
		$data['package']['is_installed'] = true;
		$data['package']['readme'] = $readme;
		$data['package']['readme_html'] = $readme_html;

		#------------------------------
		# Get assets
		#------------------------------

		$data['package']['assets'] = array();
		foreach ($package->assets as $asset) {
			$data['package']['assets'][] = $asset->toApiData(false);
		}

		return $this->createApiResponse(array('app' => $data));
	}


	####################################################################################################################
	# uninstall-instance
	####################################################################################################################

	public function uninstallInstanceAction($id)
	{
		$manager = $this->container->getAppManager();

		if (!$manager->hasApp($id)) {
			throw $this->createNotFoundException();
		}

		$app = $manager->getApp($id);

		$this->em->remove($app);
		$this->em->flush();

		return $this->createApiDeleteResponse(array('old_id' => $id));
	}
}