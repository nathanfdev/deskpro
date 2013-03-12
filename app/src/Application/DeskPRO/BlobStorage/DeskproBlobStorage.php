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
 */

namespace Application\DeskPRO\BlobStorage;

use Application\DeskPRO\BlobStorage\StorageAdapter\AbstractStorageAdapter;
use DeskPRO\Kernel\KernelErrorHandler;
use Doctrine\ORM\EntityManager;
use Application\DeskPRO\BlobStorage\Blob;
use Application\DeskPRO\Entity\Blob as BlobEntity;
use Orb\Data\ContentTypes;
use Orb\Util\Strings;

class DeskproBlobStorage
{
	/**
	 * @var \Application\DeskPRO\BlobStorage\StorageAdapter\AbstractStorageAdapter[]
	 */
	protected $adapters = array();

	/**
	 * @var string[]
	 */
	protected $disabled_adapters = array();

	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $db;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	public function __construct(EntityManager $em)
	{
		$this->adapters = array();
		$this->em = $em;
		$this->db = $em->getConnection();
	}

	/**
	 * @param StorageAdapter\AbstractStorageAdapter $adapter
	 * @param int $priority
	 */
	public function addAdapter($id, AbstractStorageAdapter $adapter, $priority = 0)
	{
		$this->adapters[$id] = $adapter;
	}


	/**
	 * Mark an adapter as disabled. Means it can still be used to read
	 * blobs, but it wont be used when persisting.
	 *
	 * @param string $id
	 */
	public function disableAdapter($id)
	{
		$this->disabled_adapters[$id] = true;
	}


	/**
	 * @param string $filename
	 * @param string $content_type
	 * @param array $props
	 * @return \Application\DeskPRO\Entity\Blob
	 */
	private function _createBlobEntity($filename, $content_type, array $props = null)
	{
		$blob_entity = new BlobEntity();
		$blob_entity->filename     = $filename;
		$blob_entity->content_type = $content_type;

		if ($props) {
			if (isset($props['original_blob'])) {
				$blob_entity->original_blob = $props['original_blob'];
			}
			if (isset($props['is_temp']) && $props['is_temp']) {
				$blob_entity->is_temp = true;
			}
			if (isset($props['date_cleanup']) && $props['date_cleanup']) {
				$blob_entity->date_cleanup = $props['date_cleanup'];
			}
			if (isset($props['sys_name']) && $props['sys_name']) {
				$blob_entity->sys_name = $props['sys_name'];
			}
		}

		return $blob_entity;
	}


	/**
	 * @return \Application\DeskPRO\Entity\Blob
	 */
	public function saveBlobRecordFromFile($source_path, $filename, $content_type, array $props = null)
	{
		$blob_entity = $this->_createBlobEntity($filename, $content_type, $props);
		$blob_entity->filesize     = filesize($source_path);
		$blob_entity->blob_hash    = md5_file($source_path);

		if (ContentTypes::isImageContentType($content_type)) {
			$imageinfo = @getimagesize($source_path);
			if ($imageinfo) {
				$blob_entity->dim_w = $imageinfo[0];
				$blob_entity->dim_h = $imageinfo[1];
			}
		}

		$this->em->persist($blob_entity);
		$this->em->flush();

		// We need the ID first to generate a proper unique filename/auth
		$batch = (int)(($blob_entity->id-1) / 1000) + 1;
		$authcode = $batch  . Strings::random(10, Strings::CHARS_KEY_ALPHA) . $blob_entity->id . $blob_entity->getNameHash();

		$blob_entity->authcode  = $authcode;
		$blob_entity->save_path = $batch . '/' . $authcode;

		// Now call the blob storages
		$blob = new Blob(
			$blob_entity->save_path,
			$blob_entity->filename,
			$blob_entity->content_type,
			array(
				'blob_id' => $blob_entity->id
			)
		);

		$prev_e = null;
		foreach ($this->adapters as $adapter_id => $adapter) {
			if (isset($this->disabled_adapters[$adapter_id])) {
				continue;
			}

			/** @var $adapter \Application\DeskPRO\BlobStorage\StorageAdapter\AbstractStorageAdapter */
			try {
				$adapter->writeBlobFromFile($blob, $source_path);
				$blob_entity->storage_loc = $adapter_id;
			} catch (\Exception $e) {
				KernelErrorHandler::logException($e);
				$prev_e = $e;
			}
		}

		// None of the succeeded, try to delete this half-inserted blob and then throw an error
		if (!$blob_entity->storage_loc) {
			$this->em->remove($blob_entity);
			$this->em->flush();

			throw new \RuntimeException("Failed to store blob, no adapters succeeded", 1, $prev_e);
		}

		$this->em->persist($blob_entity);
		$this->em->flush();

		return $blob_entity;
	}



	/**
	 * @return \Application\DeskPRO\Entity\Blob
	 */
	public function saveBlobRecordFromString($source_data, $filename, $content_type, array $props = null)
	{
		$blob_entity = $this->_createBlobEntity($filename, $content_type, $props);
		$blob_entity->filesize  = strlen($source_data);
		$blob_entity->blob_hash = md5($source_data);

		if (ContentTypes::isImageContentType($content_type)) {
			$tmpfname = @tempnam(sys_get_temp_dir(), "dpblob_");
			if ($tmpfname && @file_put_contents($tmpfname, $source_data)) {
				$imageinfo = @getimagesize($tmpfname);
				if ($imageinfo) {
					$blob_entity->dim_w = $imageinfo[0];
					$blob_entity->dim_h = $imageinfo[1];
				}
			}
			@unlink($tmpfname);
		}

		$this->em->persist($blob_entity);
		$this->em->flush();

		// We need the ID first to generate a proper unique filename/auth
		$batch = (int)(($blob_entity->id-1) / 1000) + 1;
		$authcode = $batch  . Strings::random(10, Strings::CHARS_KEY_ALPHA) . $blob_entity->id . $blob_entity->getNameHash();

		$blob_entity->authcode  = $authcode;
		$blob_entity->save_path = $batch . '/' . $authcode;

		// Now call the blob storages
		$blob = new Blob(
			$blob_entity->save_path,
			$blob_entity->filename,
			$blob_entity->content_type,
			array(
				'blob_id' => $blob_entity->id
			)
		);

		$prev_e = null;
		foreach ($this->adapters as $adapter_id => $adapter) {
			if (isset($this->disabled_adapters[$adapter_id])) {
				continue;
			}

			/** @var $adapter \Application\DeskPRO\BlobStorage\StorageAdapter\AbstractStorageAdapter */
			try {
				$adapter->writeBlobString($blob, $source_data);
				$blob_entity->storage_loc = $adapter_id;
			} catch (\Exception $e) {
				KernelErrorHandler::logException($e);
				$prev_e = $e;
			}
		}

		// None of the succeeded, try to delete this half-inserted blob and then throw an error
		if (!$blob_entity->storage_loc) {
			$this->em->remove($blob_entity);
			$this->em->flush();

			throw new \RuntimeException("Failed to store blob, no adapters succeeded", 1, $prev_e);
		}

		$this->em->persist($blob_entity);
		$this->em->flush();

		return $blob_entity;
	}


	/**
	 * @param \Application\DeskPRO\Entity\Blob $blob_entity
	 */
	public function readBlobRecord(BlobEntity $blob_entity)
	{
		if (!isset($this->adapters[$blob_entity->storage_loc])) {
			throw new \InvalidArgumentException("There is no configured storage adapter for: " . $blob_entity->storage_loc);
		}

		$adapter = $this->adapters[$blob_entity->storage_loc];

		$blob = new Blob(
			$blob_entity->save_path,
			$blob_entity->filename,
			$blob_entity->content_type,
			array(
				'blob_id' => $blob_entity->id
			)
		);

		return $adapter->readBlobString($blob);
	}
}