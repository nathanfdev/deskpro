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

	public function __construct($blob_id, $base_path, \Application\DeskPRO\DBAL\Connection $db)
	{
		$this->db = $db;
		$this->base_path = rtrim($base_path, '/\\');
		$this->blob_id = $blob_id;
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

		$key = sha1($this->blob_id . mt_rand(10000,99999) . microtime());
		$segs = str_split($key, 2);
		$dirs[] = array_shift($segs);
		$dirs[] = array_shift($segs);

		$dir_path = implode(DIRECTORY_SEPARATOR, $dirs);
		$dir_path_full = $this->base_path . DIRECTORY_SEPARATOR . $dir_path;

		$filename = $this->blob_id . '-' . implode('', $segs);
		$file_path = $dir_path . DIRECTORY_SEPARATOR . $filename;
		$file_path_full = $dir_path_full . DIRECTORY_SEPARATOR . $filename;

		if (!is_dir($dir_path_full)) {
			if (!mkdir($dir_path_full, 0755, true)) {
				throw new \RuntimeException("Could not create filesystem storage directory");
			}
		}
		if ($data) {
			if (!file_put_contents($file_path_full, $data)) {
				throw new \RuntimeException("Could not write file to storage directory: $file_path_full");
			}
		} else {
			if (!touch($file_path_full)) {
				throw new \RuntimeException("Could not write file to storage directory: $file_path_full");
			}
		}

		$metadata = array(
			'filesize'    => strlen($data),
			'storage_loc' => 'fs',
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
