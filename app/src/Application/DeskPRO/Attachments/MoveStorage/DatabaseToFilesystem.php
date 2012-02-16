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

class DatabaseToFilesystem
{
	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $db;

	/**
	 * @var \Application\DeskPRO\FileStorage\Filesystem
	 */
	protected $fs;


	/**
	 * @param \Application\DeskPRO\DBAL\Connection $db
	 * @param \Application\DeskPRO\FileStorage\Filesystem $fs
	 */
	public function __construct(Connection $db, FilesystemStorage $fs)
	{
		$this->db = $db;
		$this->fs = $fs;
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
			$taken = time() - $time_start;
			if ($max_time && $taken > $max_time) {
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
		$desc = $this->fs->getFileDescriptor($blob['id']);
		$desc->blob_info = $blob;

		$data = implode('', $this->db->fetchAllCol("SELECT data FROM blobs_storage WHERE blob_id = ? ORDER BY id DESC", array($blob['id'])));
		$desc->write($data);

		$this->db->delete('blobs_storage', array('blob_id' => $blob['id']));
	}
}
