<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage WorkerProcess
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\Attachments\MoveStorage\DatabaseToFilesystem;
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
			$base_path = $container->getSetting('core.filestorage_fs_basepath');
			if (!$base_path) {
				$base_path = DP_WEB_ROOT . '/data_files';
			}
			$mover = new DatabaseToFilesystem(App::getDb(), $base_path);
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
				'groupname' => 'core',
				'value' => $last_id,
				'created_at' => date('Y-m-d H:i:s')
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
		", array('fs', $start_id));
	}
}
