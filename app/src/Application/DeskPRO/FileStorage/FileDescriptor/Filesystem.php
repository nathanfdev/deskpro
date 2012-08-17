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
 * @subpackage FileStorage
 */

namespace Application\DeskPRO\FileStorage\FileDescriptor;

use Application\DeskPRO\App;

use Orb\Util\Util;
use Orb\Util\Arrays;
use Orb\Util\Strings;

/**
 * Store files in the filesystem as well as the descriptor in the database
 */
class Filesystem extends \Orb\FileStorage\FileDescriptor\AbstractFileDescriptor
{
	/**
	 * The path prepended to paths stored in the database. This is the path the user
	 * enters in settings.
	 *
	 * @var string
	 */
	protected $base_path;

	/**
	 * Database connection to use
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $db;

	/**
	 * The blob_id we're workin with
	 * @var int
	 */
	protected $blob_id = null;

	/**
	 * @var array
	 */
	public $blob_info = null;

	/**
	 * @var bool
	 */
	protected $is_pre_s3 = false;

	/**
	 * @param int|array|null $blob_id Blob ID or an array of existing blob info
	 * @param string $base_path
	 * @param \Application\DeskPRO\DBAL\Connection $db
	 */
	public function __construct($blob_id, $base_path, \Application\DeskPRO\DBAL\Connection $db)
	{
		if (is_array($blob_id)) {
			$this->blob_info = $blob_id;
			$blob_id = $blob_id['id'];
		}

		$this->db = $db;
		$this->base_path = rtrim($base_path, '/\\');
		$this->blob_id = $blob_id;
	}


	/**
	 * Mark the file as pre-S3 storage. That is, a file is saved locally before offloading on to S3.
	 */
	public function enableIsPreS3()
	{
		$this->is_pre_s3 = true;
	}


	/**
	 * Does the file exist?
	 *
	 * @return bool
	 */
	public function exists()
	{
		if ($this->blob_id === null) {
			return false;
		}

		if ($this->blob_info !== null) {
			if ($this->blob_info) {
				return true;
			}
			return false;
		}

		$this->blob_info = $this->db->fetchAssoc("SELECT * FROM blobs WHERE id = ?", array($this->blob_id));

		return !empty($this->blob_info);
	}


	/**
	 * Delete the file.
	 */
	public function delete()
	{
		$path = $this->getRealPath();
		if (!$path) {
			return;
		}

		$affected_blobs = $this->db->fetchAll("SELECT id, save_path FROM blobs WHERE id = ? OR original_blob_id = ?", array($this->blob_id, $this->blob_id));
		foreach ($affected_blobs as $b) {
			$this->db->delete('blobs', array('id' => $b['id']));
			$this->db->delete('blobs_storage', array('blob_id' => $b['id']));

			$path = $this->base_path . DIRECTORY_SEPARATOR . $b['save_path'];
			@unlink($path);
		}

		$this->blob_id = null;
		$this->blob_info = array();
	}


	/**
	 * Write data to the file at the path.
	 *
	 * @param string $data The data to write
	 */
	public function write($data, $meta = null)
	{
		if (!isset($meta[self::METADATA_FILEHASH])) {
			$meta[self::METADATA_FILEHASH] = sha1($data);
		}

		if (!$this->exists()) {
			$blob_data = array(
				'date_created' => date('Y-m-d H:i:s'),
			);
			if ($this->blob_id !== null) {
				$blob_data['id'] = $this->blob_id;
			}

			$this->db->insert('blobs', $blob_data);

			$this->blob_id = $this->db->lastInsertId();
			$this->blob_exists_cache = true;

		}

		if (!isset($meta[self::METADATA_FILENAME])) {
			$meta[self::METADATA_FILENAME] = 'file';
		}

		if (!isset($meta[self::METADATA_CONTENT_TYPE]) || !$meta[self::METADATA_CONTENT_TYPE]) {
			$meta[self::METADATA_CONTENT_TYPE] = 'application/octet-stream';
		}

		if (!Strings::getExtension($meta[self::METADATA_FILENAME])) {
			$ext = \Orb\Data\ContentTypes::findExtensionForContentType($meta[self::METADATA_CONTENT_TYPE]);
			if ($ext) {
				$meta[self::METADATA_FILENAME] = $meta[self::METADATA_FILENAME] . '.' . $ext;
			}
		}

		$meta[self::METADATA_FILENAME] = preg_replace('#[^a-zA-Z0-9\-_\.]#', '-', $meta[self::METADATA_FILENAME]);
		$meta[self::METADATA_FILENAME] = preg_replace('#\-{2,}#', '-', $meta[self::METADATA_FILENAME]);

		$namehash = strtoupper(substr(sha1($meta[self::METADATA_FILENAME] . $this->blob_id), 0, 3));
		$namehash .= strtoupper(substr(md5($meta[self::METADATA_FILENAME] . $this->blob_id), 0, 3));

		$batch = (int)(($this->blob_id-1) / 1000) + 1;
		$authcode = $batch  . Strings::random(10, Strings::CHARS_KEY_ALPHA) . $this->blob_id . $namehash;

		$dir_path = $batch;
		$dir_path_full = $this->base_path . DIRECTORY_SEPARATOR . $dir_path;

		$filename = $authcode;
		$file_path = $dir_path . DIRECTORY_SEPARATOR . $filename;
		$file_path_full = $dir_path_full . DIRECTORY_SEPARATOR . $filename;

		if (!is_dir($dir_path_full)) {
			$old_umask = umask(0);
			$mkdir_success = @mkdir($dir_path_full, 0777, true);
			umask($old_umask);

			if (!$mkdir_success) {
				throw new \Application\DeskPRO\FileStorage\Exception\PermissionException("Could not create filesystem storage directory");
			}
		}
		if ($data) {
			if (!@file_put_contents($file_path_full, $data)) {
				throw new \Application\DeskPRO\FileStorage\Exception\PermissionException("Could not write file to storage directory: $file_path_full");
			}
			@chmod($file_path_full, 0777);
		} else {
			if (!@touch($file_path_full)) {
				throw new \Application\DeskPRO\FileStorage\Exception\PermissionException("Could not write file to storage directory: $file_path_full");
			}
		}

		$metadata = array(
			'filesize'    => strlen($data),
			'storage_loc' => $this->is_pre_s3 ? 's3fs' : 'fs',
			'save_path'   => $file_path,
			'authcode'    => $authcode
		);
		if ($metadata) {
			if (!empty($meta[self::METADATA_CONTENT_TYPE])) $metadata['content_type']       = $meta[self::METADATA_CONTENT_TYPE];
			if (!empty($meta[self::METADATA_FILENAME]))     $metadata['filename']           = $meta[self::METADATA_FILENAME];
			if (!empty($meta[self::METADATA_FILEHASH]))     $metadata['blob_hash']          = $meta[self::METADATA_FILEHASH];
			if (!empty($meta['sys_name']))                  $metadata['sys_name']           = $meta['sys_name'];
			if (!empty($meta['original_blob_id']))          $metadata['original_blob_id']   = $meta['original_blob_id'];

			if (!empty($meta['is_temp']) && $meta['is_temp']) {
				$metadata['is_temp'] = 1;
			}
		}

		$this->db->update('blobs', $metadata, array('id' => $this->blob_id));

		$this->blob_info = null;
	}


	/**
	 * Write data from a file pointer.
	 *
	 * @param resource $fp_data The file pointer
	 * @param array $meta Any metadata (Content-Type is sometimes important)
	 */
	public function writeFromFile($fp_data, $meta = null)
	{
		if (!is_resource($fp_data)) {
			throw new \Orb\FileStorage\Exception('$fp_data must be a file pointer', 5);
		}

		$this->write(stream_get_contents($fp_data), $meta);
	}


	/**
	 * Read data from the file at the path.
	 */
	public function get()
	{
		if (!$this->exists()) {
			return '';
		}

		return file_get_contents($this->getRealPath());
	}


	/**
	 * Get the filesize of the file.
	 *
	 * @return int
	 */
	public function getLength()
	{
		if (!$this->exists()) {
			return null;
		}

		return filesize($this->getRealPath());
	}


	/**
	 * @return string
	 */
	public function getRealPath()
	{
		if (!$this->exists()) {
			return null;
		}

		return $this->base_path . DIRECTORY_SEPARATOR . $this->blob_info['save_path'];
	}


	/**
	 * Not supported
	 */
	public function getMetaData()
	{
		if (!$this->exists()) {
			return null;
		}

		$metadata = array(
			self::METADATA_FILESIZE => $this->blob_info['filesize'],
			self::METADATA_FILENAME => $this->blob_info['filename'],
			self::METADATA_FILEHASH => $this->blob_info['blob_hash'],
			self::METADATA_CONTENT_TYPE => $this->blob_info['content_type'],
		);

		$metadata = Arrays::removeEmptyString($metadata);

		return $metadata;
	}


	/**
	 * Close the resource once you're done with it.
	 */
	public function release()
	{

	}


	/**
	 * Get the path that can be used to re-create this descriptor.
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->blob_id;
	}
}
