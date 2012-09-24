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
 * @subpackage Attachments
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

		// The file is invalid, so dont update the blob
		// Not deleting the blob either in case the file can be put back in place/restored
		if (!is_file($filepath)) {
			return;
		}

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
