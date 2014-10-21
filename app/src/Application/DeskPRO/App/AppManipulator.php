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
 * @subpackage
 */

namespace Application\DeskPRO\App;


use Application\DeskPRO\App\Native\InstallerHandler\InstallerContext;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\AppPackage;
use Application\DeskPRO\ORM\EntityManager;
use Orb\Util\Arrays;

/**
 * Manipulates apps - it knows how to update an app instance, how to properly install an app, etc
 *
 * This was largely moved out of the AppsController (ApiBunele) with some added features
 */
class AppManipulator
{
	/**
	 * @var AppManager
	 */
	private $manager;
	/**
	 * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	private $container;
	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */
	private $em;


	public function __construct(AppManager $manager, EntityManager $em, DeskproContainer $container)
	{
		$this->manager = $manager;
		$this->container = $container;
		$this->em = $em;
	}


	public function installInstance(AppPackage $package, AppManipulatorContext $context)
	{
		$instance_installer = new InstanceInstaller($this->manager, $package, $this->em);

		$app = $instance_installer->install(
			$context->getInputTitle(),
			$context->getSettings(),
			$this->container,
			$context->getUsersourceType()
		);

		return $app;
	}


	/**
	 * @param AppInstance           $app
	 * @param AppManipulatorContext $context
	 */
	public function updateInstance(AppInstance $app, AppManipulatorContext $context)
	{
		$settings = $context->getSettings();
		$inputTitle = $context->getInputTitle();
		$saveAssets = $context->getSaveAssets();

		$instance_updater = new InstanceUpdater($this->manager, $app, $this->em);
		$instance_updater->update($inputTitle, $settings, $this->container);


		// If this is a custom app, we can update assets from here as well
		if ($app->package->is_custom) {
			$blob_storage = $this->container->getBlobStorage();
			$assets       = $app->package->assets;
			$assets       = Arrays::keyFromData($assets, 'id');
			$save_assets  = $saveAssets;

			$remove_blobs = array();

			foreach ($save_assets as $asset_info) {
				if (!isset($assets[$asset_info['id']])) {
					continue;
				}

				$asset    = $assets[$asset_info['id']];
				$old_blob = $asset->blob;

				$asset->blob = $blob_storage->createBlobRecordFromString(
					$asset_info['content'],
					$old_blob->filename,
					$old_blob->content_type
				);

				$this->em->persist($asset);
				$this->em->flush($asset);
				$remove_blobs[] = $old_blob;
			}

			$this->em->flush();

			foreach ($remove_blobs as $blob) {
				$blob_storage->deleteBlobRecord($blob);
			}
		}
	}


	public function uninstallInstance(AppInstance $app)
	{
		$instance_uninstaller = new InstanceUninstaller($this->manager, $app, $this->em);
		$instance_uninstaller->uninstall($this->container);

		// If the package is a custom package, then uninstalling the app
		// ininstalls the package too
		if ($app->package->is_custom) {
			$package = $app->package;
			// Remove all assets from blob storage
			$blob_storage = $this->container->getBlobStorage();
			foreach ($package->assets as $asset) {
				try {
					$blob_storage->deleteBlobRecord($asset->blob);
				} catch (\Exception $e) {
				}
			}

			$this->em->remove($package);
			$this->em->flush();
		}
	}


	public function disableSso(AppInstance $app)
	{
		$instance_updater = new InstanceUpdater($this->manager, $app, $this->em);
		$instance_updater->disableSso();
	}
}
 