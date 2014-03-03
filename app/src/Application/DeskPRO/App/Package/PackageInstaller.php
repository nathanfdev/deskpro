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
use Application\DeskPRO\Entity\AppPackage;
use Application\DeskPRO\Entity\Blob;
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
	 * Install or update a package.
	 *
	 * @param Package    $package The package to installl
	 * @param AppPackage $def     Existing package record. It will be updated. Otherwise, a new AppPackage is created instead.
	 * @return AppPackage
	 */
	public function installPackage(Package $package, AppPackage $def = null)
	{
		$def = $package->createAppPackage($def);

		$this->em->persist($def);
		$old_blobs = array();

		#------------------------------
		# Get app icons
		#------------------------------

		$sizes = array(16, 24, 32, 48, 64, 96, 128, 192, 256, 512);
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

			$asset = $this->_addAssetBlob($def, $blob, "icons.app.$size", null, $old_blobs);
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

			$asset = $this->_addAssetBlob($def, $blob, "icons.app.$size", null, $old_blobs);
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

			$asset = $this->_addAssetBlob($def, $blob, "icons.app.$size", null, $old_blobs);
			$this->em->persist($asset);

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
			$asset = $this->_addAssetBlob($def, $blob, 'readme.text', null, $old_blobs);
			$this->em->persist($asset);

			$readme_html = \Parsedown::instance()->parse($readme);
			$blob = $this->blob_storage->createBlobRecordFromString(
				$readme_html,
				'README.html',
				'text/html'
			);
			$asset = $this->_addAssetBlob($def, $blob, 'readme.html', null, $old_blobs);
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

			$asset = $this->_addAssetBlob($def, $blob, 'app_js', null, $old_blobs);
			$this->em->persist($asset);
		}

		#------------------------------
		# Save assets
		#------------------------------

		foreach ($package->getJsAssets() as $asset_info) {
			$asset = $this->_addAssetFromInfo($def, $asset_info, 'js', $old_blobs);
			$this->em->persist($asset);
		}
		foreach ($package->getHtmlAssets() as $asset_info) {
			$asset = $this->_addAssetFromInfo($def, $asset_info, 'html', $old_blobs);
			$this->em->persist($asset);
		}
		foreach ($package->getCssAssets() as $asset_info) {
			$asset = $this->_addAssetFromInfo($def, $asset_info, 'css', $old_blobs);
			$this->em->persist($asset);
		}
		foreach ($package->getResAssets() as $asset_info) {
			$asset = $this->_addAssetFromInfo($def, $asset_info, 'res', $old_blobs);
			$this->em->persist($asset);
		}

		#------------------------------
		# Save
		#------------------------------

		$this->em->flush();

		// Need to delete old blobs at the end after AppAsset.blob has been overwritten
		// because AppAsset.blob has a delete cascade relation.
		foreach ($old_blobs as $b) {
			$this->blob_storage->deleteBlobRecord($b);
		}

		return $def;
	}


	/**
	 * @param AppPackage $def
	 * @param array $asset_info
	 * @param string $tag
	 * @return \Application\DeskPRO\Entity\AppAsset
	 */
	private function _addAssetFromInfo(AppPackage $def, array $asset_info, $tag, array &$old_blobs)
	{
		$mimetype = ContentTypes::getContentTypeFromFilename($asset_info['name']);

		$blob = $this->blob_storage->createBlobRecordFromFile(
			$asset_info['real_path'],
			$asset_info['name'],
			$mimetype
		);

		$asset = $this->_addAssetBlob($def, $blob, $tag, $asset_info['path'], $old_blobs);
		return $asset;
	}


	/**
	 * @param AppPackage $def
	 * @param Blob $blob
	 * @param string $tag
	 * @param string $filename
	 * @return \Application\DeskPRO\Entity\AppAsset
	 */
	private function _addAssetBlob(AppPackage $def, Blob $blob, $tag = null, $filename = null, array &$old_blobs)
	{
		if ($tag) {
			if ($filename) {
				$asset = null;
				foreach ($def->getTaggedAssets($tag) as $a) {
					if ($a->name == $filename) {
						$asset = $a;
						break;
					}
				}
			} else {
				$asset = $def->getTaggedAsset($tag);
			}

			if ($asset) {
				// Asset already exists, replace it
				if ($asset->blob) {
					$old_blobs[] = $asset->blob;
				}
				$asset->blob = $blob;
			}
		}

		if (!$asset) {
			// New asset
			$asset = $def->addAssetFromBlob($blob);
		}

		$asset->name = $filename ? $filename : $blob->filename;
		$asset->tag = $tag;

		return $asset;
	}
}