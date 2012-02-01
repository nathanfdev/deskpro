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

class Database extends \Orb\FileStorage\FileDescriptor\AbstractFileDescriptor
{
	/**
	 * Database connection to use
	 * @var Application\DeskPRO\DBAL\Connection
	 */
	protected $db;

	/**
	 * The blob_id we're workin with
	 * @var int
	 */
	protected $blob_id = null;

	/**
	 * Used with exists()
	 * @var bool
	 */
	protected $blob_exists_cache = null;

	public function __construct($blob_id, \Application\DeskPRO\DBAL\Connection $db)
	{
		$this->db = $db;
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

		if ($this->blob_exists_cache !== null) {
			return $this->blob_exists_cache;
		}

		$this->blob_exists_cache = (bool)$this->db->fetchColumn("SELECT COUNT(*) FROM blobs WHERE id = ?", array($this->blob_id));

		return $this->blob_exists_cache;
	}



	/**
	 * Delete the file.
	 */
	public function delete()
	{
		$this->db->beginTransaction();
		$this->db->delete('blobs', array('id' => $this->blob_id));
		$this->db->delete('blobs_storage', array('blob_id' => $this->blob_id));
		$this->db->commit();

		$this->blob_id = null;
		$this->blob_exists_cache = null;
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
				'authcode' => Strings::random(20, Strings::CHARS_KEY)
			);
			if ($this->blob_id !== null) {
				$blob_data['id'] = $this->blob_id;
			}

			$this->db->insert('blobs', $blob_data);

			$this->blob_id = $this->db->lastInsertId();
			$this->blob_exists_cache = true;

		} else {
			// Delete previous data every time we write
			$this->db->delete('blobs_storage', array('blob_id' => $this->blob_id));
		}

		$data_len = strlen($data);

		// /2 for worst-case scenario of every character needing escape, -200 for wiggle room fo rest of query
		$max_size = ($this->db->getMaxPacketSize()/2)-200;
		$parts = ceil($data_len / $max_size);

		for ($i = 0; $i < $parts; $i++) {
			$this->db->insert('blobs_storage', array(
				'blob_id' => $this->blob_id,
				'data' => substr($data, $i * $max_size, $max_size)
			));
		}

		$metadata = array('filesize' => $data_len);
		if ($metadata) {
			if (!empty($meta[self::METADATA_CONTENT_TYPE])) $metadata['content_type'] = $meta[self::METADATA_CONTENT_TYPE];
			if (!empty($meta[self::METADATA_FILENAME]))     $metadata['filename']     = $meta[self::METADATA_FILENAME];
			if (!empty($meta[self::METADATA_FILEHASH]))     $metadata['blob_hash']    = $meta[self::METADATA_FILEHASH];
			if (!empty($meta['sys_name']))                  $metadata['sys_name']     = $meta['sys_name'];

			if (!empty($meta['is_temp']) && $meta['is_temp']) {
				$metadata['is_temp'] = 1;
			}
		}
		$this->db->update('blobs', $metadata, array('id' => $this->blob_id));

		$this->db->commit();
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

		$parts = array();
		$statement = $this->db->executeQuery("SELECT data FROM blobs_storage WHERE blob_id = ?", array($this->blob_id));

		while ($row = $statement->fetch(\PDO::FETCH_NUM)) {
			$parts[] = $row[0];
		}

		return implode('', $parts);
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

		return filesize($this->real_path);
	}



	/**
	 * Not supported
	 */
	public function getMetaData()
	{
		if (!$this->exists()) {
			return null;
		}

		$metadata_read = $this->db->fetchAssoc("SELECT filesize, content_type, filename, blob_hash FROM blobs WHERE id = ?", array($this->blob_id));

		$metadata = array(
			self::METADATA_FILESIZE => $metadata_read['filesize'],
			self::METADATA_FILENAME => $metadata_read['filename'],
			self::METADATA_FILEHASH => $metadata_read['blob_hash'],
			self::METADATA_CONTENT_TYPE => $metadata_read['content_type'],
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
