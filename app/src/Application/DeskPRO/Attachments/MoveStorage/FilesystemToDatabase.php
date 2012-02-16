<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Attachments
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Attachments\MoveStorage;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\FileStorage\Filesystem as FilesystemStorage;

class FilesystemToDatabase
{
	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $db;

	/**
	 * @var string
	 */
	protected $base_path;


	/**
	 * @param \Application\DeskPRO\DBAL\Connection $db
	 * @param string $base_path
	 */
	public function __construct(Connection $db, $base_path)
	{
		$this->db = $db;
		$this->base_path = $base_path;
	}


	/**
	 * Process a batch of blobs
	 *
	 * @param  array $blobs
	 * @param  int   $max_time The max amount of time in seconds to spend on the batch before breaking early
	 * @return int   The number of blobs processed
	 */
	public function processBatch(array $blobs, $max_time = 0, &$last_id)
	{
		$count = 0;
		$time_start = time();

		foreach ($blobs as $b) {
			if ($max_time && time() - $time_start > $max_time) {
				break;
			}

			$this->processBlob($b);
			$last_id = $b['id'];
			$count++;
		}

		return $count;
	}


	/**
	 * Process a blob
	 *
	 * @param array $blob
	 */
	public function processBlob(array $blob)
	{
		$filepath = $this->base_path . $blob['save_path'];

		// /2 for worst-case scenario of every character needing escape, -200 for wiggle room fo rest of query
		$data = file_get_contents($filepath);
		$data_len = strlen($data);
		$max_size = ($this->db->getMaxPacketSize()/2)-200;
		$parts = ceil($data_len / $max_size);

		for ($i = 0; $i < $parts; $i++) {
			$this->db->insert('blobs_storage', array(
				'blob_id' => $blob['id'],
				'data' => substr($data, $i * $max_size, $max_size)
			));
		}

		$this->db->update('blobs', array(
			'save_path' => null,
			'storage_loc' => null,
		), array('id' => $blob['id']));

		unlink($filepath);
	}
}
