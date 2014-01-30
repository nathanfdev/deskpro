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
 * @category Entities
 */

namespace Application\DeskPRO\App\Package;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Doctrine\ORM\EntityManager;
use Orb\Data\ContentTypes;

class PackageInstaller
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \Application\DeskPRO\BlobStorage\DeskproBlobStorage
	 */
	private $blob_storage;

	public function __construct(EntityManager $em, DeskproBlobStorage $blob_storage)
	{
		$this->em = $em;
		$this->blob_storage = $blob_storage;
	}


	/**
	 * @param Package $package
	 * @return \Application\DeskPRO\Entity\AppPackage
	 */
	public function installPackage(Package $package)
	{
		$def = $package->createAppPackage();
		$this->em->persist($def);

		#------------------------------
		# Main icon
		#------------------------------

		$blob = $this->blob_storage->createBlobRecordFromFile(
			$package->getIconFilePath(),
			'app.png',
			'image/png'
		);

		$asset = $def->addAssetFromBlob($blob);
		$asset->tag = 'icons.app';

		$this->em->persist($asset);

		#------------------------------
		# Main app.js
		#------------------------------

		$appjs_path = $package->getAppJsFilePath();
		if ($appjs_path) {
			$blob = $this->blob_storage->createBlobRecordFromFile(
				$appjs_path,
				'app.js',
				'text/javascript'
			);

			$asset = $def->addAssetFromBlob($blob);
			$asset->tag = 'app_js';
			$this->em->persist($asset);
		}

		#------------------------------
		# Save assets
		#------------------------------

		$blob_storage = $this->blob_storage;
		$em = $this->em;
		$fn_proc_asset = function($asset_info, $tag) use ($blob_storage, $def, $em) {
			$mimetype = ContentTypes::getContentTypeFromFilename($asset_info['name']);

			$blob = $blob_storage->createBlobRecordFromFile(
				$asset_info['real_path'],
				$asset_info['name'],
				$mimetype
			);

			$asset = $def->addAssetFromBlob($blob, $asset_info['path']);
			$asset->tag = $tag;

			$em->persist($asset);
		};

		foreach ($package->getJsAssets() as $asset_info) {
			$fn_proc_asset($asset_info, 'js');
		}
		foreach ($package->getHtmlAssets() as $asset_info) {
			$fn_proc_asset($asset_info, 'html');
		}
		foreach ($package->getCssAssets() as $asset_info) {
			$fn_proc_asset($asset_info, 'css');
		}
		foreach ($package->getResAssets() as $asset_info) {
			$fn_proc_asset($asset_info, 'res');
		}

		#------------------------------
		# Save
		#------------------------------

		$this->em->flush();

		return $def;
	}
}