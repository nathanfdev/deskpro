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
use Imagine\Image\Box as ImageBox;
use Imagine\Image\Point as ImagePoint;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Exception as ImageException;

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

	/**
	 * @var \Imagine\Image\ImagineInterface
	 */
	private $imagine;

	public function __construct(EntityManager $em, DeskproBlobStorage $blob_storage, ImagineInterface $imagine)
	{
		$this->em = $em;
		$this->blob_storage = $blob_storage;
		$this->imagine = $imagine;
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
		# Get app icons
		#------------------------------

		$sizes = array(16, 24, 32, 48, 64, 96, 128, 192, 256);
		$have_sizes = array();
		$largest = null;

		foreach ($sizes as $size) {
			$path = $package->getIconFilePath($size);
			if (!$path) {
				continue;
			}

			$blob = $this->blob_storage->createBlobRecordFromFile(
				$path,
				"app_$size.png",
				'image/png'
			);

			$asset = $def->addAssetFromBlob($blob);
			$asset->tag = "icons.app.$size";
			$this->em->persist($asset);

			$largest = array($path, $size, $blob);
			$have_sizes[$size] = $blob;
		}

		// No icon, we need a default
		if (!$largest) {
			$path = DP_ROOT.'/src/Application/DeskPRO/App/Package/Resources/no-icon.png';
			$size = 256;
			$blob = $this->blob_storage->createBlobRecordFromFile(
				$path,
				"app_$size.png",
				'image/png'
			);

			$asset = $def->addAssetFromBlob($blob);
			$asset->tag = "icons.app.$size";
			$this->em->persist($asset);

			$largest = array($path, $size, $blob);
			$have_sizes[$size] = array($path, $size, $blob);
		}

		// Missing sizes we'll just scale whatever
		// the largest icon we have
		foreach ($sizes as $size) {
			if (isset($have_sizes[$size])) {
				continue;
			}

			$image = $this->imagine->open($largest[0]);
			$image->resize(new ImageBox($size, $size));

			$blob = $this->blob_storage->createBlobRecordFromString(
				$image->get('png'),
				"app_$size.png",
				'image/png'
			);

			unset($image);

			$asset = $def->addAssetFromBlob($blob);
			$asset->tag = "icons.app.$size";
			$this->em->persist($asset);

			$largest = array($path, $size, $blob);
			$have_sizes[$size] = $blob;
		}

		#------------------------------
		# README file
		#------------------------------

		$path = $package->getReadmeFilePath();
		if ($path) {
			$readme = file_get_contents($path);

			$blob = $this->blob_storage->createBlobRecordFromString(
				$readme,
				'README',
				'text/plain'
			);
			$asset = $def->addAssetFromBlob($blob);
			$asset->tag = 'readme.text';
			$this->em->persist($asset);

			$readme_html = \Parsedown::instance()->parse($readme);
			$blob = $this->blob_storage->createBlobRecordFromString(
				$readme_html,
				'README.html',
				'text/html'
			);
			$asset = $def->addAssetFromBlob($blob);
			$asset->tag = 'readme.html';
			$this->em->persist($asset);
		}

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