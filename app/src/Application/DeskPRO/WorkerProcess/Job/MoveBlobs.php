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
 * @subpackage WorkerProcess
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\Attachments\MoveStorage\DatabaseToFilesystem;
use Application\DeskPRO\Attachments\MoveStorage\FilesystemToDatabase;
use Application\DeskPRO\FileStorage\Filesystem as FilesystemStorage;

use Application\DeskPRO\App;
use Application\DeskPRO\Log\Logger;

/**
 * Goes through blobs that need to be moved from one storage mechanism to another
 */
class MoveBlobs extends AbstractJob
{
	const DEFAULT_INTERVAL = 60;

	public function run()
	{
		$last_id = App::getSetting('core.filesystem_move_from_id');
		if (!$last_id) {
			return;
		}

		$fs = App::getSystemService('filestorage');

		if ($fs instanceof FilesystemStorage) {
			$mover = new DatabaseToFilesystem(App::getDb(), $fs);
			$blob_fetcher = 'getNonFilesystemBlobs';
		} else {
			$mover = new FilesystemToDatabase(App::getDb(), App::getContainer()->getBlobDir());
			$blob_fetcher = 'getNonDatabaseBlobs';
		}

		$time_start = time();
		$max_time = 15;
		$count = 0;
		while ((time() - $time_start) < $max_time) {
			$remain = $max_time - (time() - $time_start);
			$blobs = $this->$blob_fetcher($last_id);

			if (!$blobs) {
				$last_id = 0;
				break;
			}

			$count += $mover->processBatch($blobs, $remain, $last_id);
		}

		if ($last_id) {
			App::getDb()->replace('settings', array(
				'name' => 'core.filesystem_move_from_id',
				'value' => $last_id,
			));
		} else {
			App::getDb()->delete('settings', array('name' => 'core.filesystem_move_from_id'));
		}

		$this->logStatus("Moved $count blobs to new mechanism");
	}

	protected function getNonFilesystemBlobs($start_id, $limit = 1000)
	{
		return App::getDb()->fetchAll("
			SELECT * FROM blobs
			WHERE storage_loc != ? OR storage_loc IS NULL AND id > ?
			ORDER BY id ASC
			LIMIT $limit
		", array('fs', $start_id));
	}

	protected function getNonDatabaseBlobs($start_id, $limit = 1000)
	{
		return App::getDb()->fetchAll("
			SELECT * FROM blobs
			WHERE storage_loc IS NOT NULL AND id > ?
			ORDER BY id ASC
			LIMIT $limit
		", array($start_id));
	}
}
