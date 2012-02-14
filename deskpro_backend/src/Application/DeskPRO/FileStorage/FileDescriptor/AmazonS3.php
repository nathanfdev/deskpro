<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage FileStorage
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\FileStorage\FileDescriptor;

use Application\DeskPRO\App;

use Zend\Service\Amazon\S3\S3;

use Orb\Util\Util;
use Orb\Util\Arrays;
use Orb\Util\Strings;

/**
 * The S3 adapter is a sort of hybrid adapter. It stores files in the filesystem first,
 * and then on cron they are moved off-site to S3.
 *
 * So this descriptor is a S3 descriptor unless it starts with file://, which means it's
 * currently on the local filesystem.
 */
class AmazonS3 extends \Orb\FileStorage\FileDescriptor\AbstractFileDescriptor
{
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
	 * @var
	 */
	protected $s3;

	/**
	 * @var string
	 */
	protected $prefix;

	/**
	 * @var string
	 */
	protected $bucket;

	/**
	 * @param int|array|null $blob_id Blob ID or an array of existing blob info
	 * @param string $base_path
	 * @param \Application\DeskPRO\DBAL\Connection $db
	 */
	public function __construct($blob_id, \Application\DeskPRO\DBAL\Connection $db, S3 $s3, $bucket, $prefix = '')
	{
		if (is_array($blob_id)) {
			$this->blob_info = $blob_id;
			$blob_id = $blob_id['id'];
		}

		$this->db       = $db;
		$this->blob_id  = $blob_id;
		$this->s3       = $s3;
		$this->bucket   = $bucket;
		$tihs->prefix   = $prefix;
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
	 * Like exists() but actually makes a request to amazon to see if the object exists
	 *
	 * @return bool
	 */
	public function existsReal()
	{
		if (!$this->exists()) {
			return false;
		}

		return $this->s3->isObjectAvailable($this->bucket . '/' . $this->prefix . $this->blob_info['save_path']);
	}


	/**
	 * Delete the file.
	 */
	public function delete()
	{
		if (!$this->exists()) {
			return;
		}

		$this->s3->removeObject($this->bucket . '/' . $this->prefix . $this->blob_info['save_path']);
		$this->db->delete('blobs', array('id' => $this->blob_id));
		$this->db->delete('blobs_storage', array('blob_id' => $this->blob_id));

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
		$this->db->beginTransaction();

		if (!isset($meta[self::METADATA_FILEHASH])) {
			$meta[self::METADATA_FILEHASH] = sha1($data);
		}

		if (!$this->exists()) {
			$blob_data = array(
				'date_created' => date('Y-m-d H:i:s'),
				'authcode' => Strings::random(20, Strings::CHARS_KEY_ALPHA)
			);
			if ($this->blob_id !== null) {
				$blob_data['id'] = $this->blob_id;
			}

			$this->db->insert('blobs', $blob_data);

			$this->blob_id = $this->db->lastInsertId();
			$this->blob_exists_cache = true;

		}

		$dirs = array();
		$dirs[] = (int)($this->blob_id / 1000);
		$dirs[] = $this->blob_id;

		$dir_path = implode('/', $dirs);
		$dir_path_full = $this->prefix . '/' . $dir_path;

		if (!empty($meta[self::METADATA_FILENAME])) {
			$filename = $meta[self::METADATA_FILENAME];
		} elseif ($this->blob_info && $this->blob_info['filename']) {
			$filename = $this->blob_info['filename'];
		} else {
			$filename = 'file';
		}

		$filename = preg_replace('#[^a-zA-Z0-9\-_\.]#', '-', $filename);
		$filename = preg_replace('#\-{2,}#', '-', $filename);

		$file_path = $dir_path . '/' . $filename;
		$file_path_full = $dir_path_full . '/' . $filename;

		$content_type = empty($meta[self::METADATA_CONTENT_TYPE]) ? null : $meta[self::METADATA_CONTENT_TYPE];
		if (!$content_type && $this->blob_info && $this->blob_info['content_type']) {
			$content_type = $this->blob_info['content_type'];
		}

		$this->s3->putFile($this->bucket . '/' . $file_path_full, $data, array(
			S3::S3_ACL_HEADER => S3::S3_ACL_PUBLIC_READ,
			S3::S3_CONTENT_TYPE_HEADER => $content_type
		));

		$metadata = array(
			'filesize'    => strlen($data),
			'storage_loc' => 's3',
			'save_path'   => $file_path,
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

		$this->db->commit();

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

		return $this->s3->getObject($this->bucket . '/' . $this->prefix . $this->blob_info['save_path']);
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

		return $this->blob_info['filesize'];
	}


	/**
	 * @return string
	 */
	public function getRealPath()
	{
		if (!$this->exists()) {
			return null;
		}

		return $this->prefix . $this->blob_info['save_path'];
	}


	/**
	 * @return string
	 */
	public function getDirectLink()
	{
		if (!$this->exists()) {
			return null;
		}

		return 'http://' . $this->bucket . '.s3.amazonaws.com/' . $this->getRealPath();
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
