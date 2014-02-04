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
use Application\DeskPRO\Entity\AppPackage;
use Orb\Util\Strings;
use Imagine\Image\Box as ImageBox;

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

		if (!$manager->hasPackage($name)) {
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

		$settings = $this->_readAppSettings($package, $this->in->getCleanValueArray('settings'));
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


	/**
	 * @param AppPackage $package
	 * @param array $settings_form
	 * @return array
	 */
	private function _readAppSettings(AppPackage $package, array $settings_form)
	{
		$settings = array();
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

		return $settings;
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

		$data = $app->toApiData();

		return $this->createApiResponse(array('app' => $data));
	}


	####################################################################################################################
	# update-instance
	####################################################################################################################

	public function updateInstanceAction($id)
	{
		$manager = $this->container->getAppManager();

		if (!$manager->hasApp($id)) {
			throw $this->createNotFoundException();
		}

		$app = $manager->getApp($id);

		$settings = $this->_readAppSettings($app->package, $this->in->getCleanValueArray('settings'));

		$app->title = $this->in->getString('settings.dp_app.title') ?: $app->package->title;
		$app->setSettings($settings);

		$this->em->persist($app);
		$this->em->flush();

		return $this->createApiSuccessResponse();
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

	####################################################################################################################
	# create-custom-app
	####################################################################################################################

	public function createCustomAppAction()
	{
		$package = new AppPackage();
		$package->name         = "com.deskpro.custom." . Strings::random(10, Strings::CHARS_ALPHA_I);
		$package->title        = $this->in->getString('options.title') ?: "Untitled";
		$package->description  = $this->in->getString('options.description') ?: "";
		$package->author_name  = $this->person->getDisplayName();
		$package->author_email = $this->person->getEmailAddress();
		$package->author_link  = $this->container->getSetting('core.deskpro_url');
		$package->api_version  = 1;
		$package->version      = 1;
		$package->version_name = "1.0.0";
		$package->is_custom    = true;
		$package->is_single    = true;
		$package->scopes       = array(AppPackage::SCOPE_AGENT);

		$this->em->persist($package);
		$blob_storage = $this->container->getBlobStorage();

		$with_blank = false;
		$prop_tab_title = "Tab Title";

		$locations = array();
		$js_files = array();
		$html_files = array();
		$require_files = array();
		$require_names = array();

		foreach ($this->in->getCleanValue('options.ticket') as $name => $value) {
			if (!$value) continue;
			if ($name == 'blank') {
				$with_blank = true;
				$js_files[] = "Ticket/TicketContext";
				$require_files[] = $package->name . "/js/Ticket/TicketContext";
				$require_names[] = "Ticket_TicketContext";
			} else if ($name == 'properties.tab.title') {
				$prop_tab_title = $value;
			} else {
				$js_name = ucfirst(Strings::underscoreToCamelCase(str_replace('.', '_', $name)));

				$locations[]     = array('location' => $name, 'js_class' => 'Ticket_' . $js_name, 'html_file' => "Ticket/" . $js_name . '.html');
				$js_files[]      = "Ticket/" . $js_name . 'Controller';
				$html_files[]    = "Ticket/" . $js_name;
				$require_files[] = $package->name . "/js/$js_name";
				$require_names[] = str_replace($js_name, "_", $js_name);
			}
		}

		#------------------------------
		# Create JS files
		#------------------------------

		foreach ($js_files as $file) {
			if ($file == 'Ticket/TicketContext') {
				$js = "define(function() {\n\treturn {\n\t\tinit: function() {\n\t\t\t// TODO\n\t\t}\n\t};\n\n});";
			} else {
				$js = "define(function() {\n\treturn function() {\n\t\t// TODO\n\t};\n\n});";
			}

			$blob = $blob_storage->createBlobRecordFromString(
				$js,
				basename($file) . '.js',
				'text/javascript'
			);

			$asset = $package->addAssetFromBlob($blob, $file.'.js');
			$asset->tag = "js";
			$this->em->persist($asset);
		}

		#------------------------------
		# Create HTML files
		#------------------------------

		foreach ($html_files as $file) {
			$html = "Your HTML goes here";

			$blob = $blob_storage->createBlobRecordFromString(
				$html,
				basename($file) . '.html',
				'text/html'
			);

			$asset = $package->addAssetFromBlob($blob, $file.'.html');
			$asset->tag = "html";
			$this->em->persist($asset);
		}

		#------------------------------
		# Get app icons
		#------------------------------

		$sizes = array(16, 24, 32, 48, 64, 96, 128, 192, 256);
		$have_sizes = array();
		$largest = null;

		$path = DP_ROOT.'/src/Application/DeskPRO/App/Package/Resources/no-icon.png';
		$size = 256;
		$blob = $this->blob_storage->createBlobRecordFromFile(
			$path,
			"app_$size.png",
			'image/png'
		);

		$asset = $package->addAssetFromBlob($blob);
		$asset->tag = "icons.app.$size";
		$this->em->persist($asset);

		$largest = array($path, $size, $blob);
		$have_sizes[$size] = array($path, $size, $blob);

		// Missing sizes we'll just scale whatever
		// the largest icon we have
		foreach ($sizes as $size) {
			if (isset($have_sizes[$size])) {
				continue;
			}

			$image = $this->container->getImagine()->open($largest[0]);
			$image->resize(new ImageBox($size, $size));

			$blob = $blob_storage->createBlobRecordFromString(
				$image->get('png'),
				"app_$size.png",
				'image/png'
			);

			unset($image);

			$asset = $package->addAssetFromBlob($blob);
			$asset->tag = "icons.app.$size";
			$this->em->persist($asset);

			$largest = array($path, $size, $blob);
			$have_sizes[$size] = $blob;
		}

		#------------------------------
		# Main app.js
		#------------------------------

		$require_files = "'" . implode("', '", $require_files) . "'";
		$require_names = implode(', ', $require_names);

		$app_js = "define([$require_files], function($require_names) {\n\n";

		if ($with_blank) {
			$app_js .= "\tthis.register(\"ticket\", \"Ticket_TicketContext\");\n";
		}

		foreach ($locations as $loc) {
			if ($loc == 'properties.tab') {
				$prop_tab_title = addslashes($prop_tab_title);
				$app_js .= "\tthis.registerWidgetTab(\"ticket\", \"@{$loc['location']}\", $prop_tab_title, \"{$loc['html_file']}\", \"{$loc['js_class']}\");\n";
			} else {
				$app_js .= "\tthis.registerWidget(\"ticket\", \"@{$loc['location']}\", \"{$loc['html_file']}\", \"{$loc['js_class']}\");\n";
			}
		}

		$app_js .= "\n});";

		$blob = $blob_storage->createBlobRecordFromString(
			$app_js,
			"app.js",
			'text/javascript'
		);

		$asset = $package->addAssetFromBlob($blob);
		$asset->tag = "app_js";
		$this->em->persist($asset);

		$this->em->flush();

		#------------------------------
		# Create an instance of it too
		#------------------------------

		$app = new AppInstance();
		$app->package = $package;
		$app->title = $this->in->getString('settings.dp_app.title') ?: $package->title;

		$this->em->persist($app);
		$this->em->flush();

		return $this->createApiCreateResponse(
			array('id' => $app->id),
			$this->generateUrl('api_apps_instance', array('id' => $app->id))
		);
	}
}