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
		$desc->write($data, $blob);

		$this->db->delete('blobs_storage', array('blob_id' => $blob['id']));
	}
}
