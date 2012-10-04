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
 */

namespace Application\DeskPRO\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Application\DeskPRO\App;

use Orb\Util\Arrays;
use Orb\Util\Strings;

use Application\DeskPRO\Attachments\MoveStorage\DatabaseToFilesystem;
use Application\DeskPRO\Attachments\MoveStorage\FilesystemToDatabase;
use Application\DeskPRO\FileStorage\Filesystem as FilesystemStorage;

class MoveBlobsFsCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setDefinition(array(
		))->setName('dp:move-blobs-fs');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		set_time_limit(0);
		App::getContainer()->getSettingsHandler()->setSetting('core.filestorage_method', 'fs');
		App::getDb()->delete('settings', array('name' => 'core.filesystem_move_from_id'));

		$fs = App::getSystemService('filestorage');

		$mover = new DatabaseToFilesystem(App::getDb(), $fs);
		$blob_fetcher = 'getNonFilesystemBlobs';

		$count = 0;
		$last_id = -1;
		while (1) {
			echo "Processing batch ($last_id) ... ";
			$blobs = $this->$blob_fetcher($last_id);

			if (!$blobs) {
				$last_id = 0;
				break;
			}

			$batch_count = $mover->processBatch($blobs, 0, $last_id);
			$count += $batch_count;

			echo "Moved $batch_count\n";
		}

		echo "\nDone. Moved $count blobs to filesystem.\n";

		$is_blob_data = App::getDb()->fetchColumn("SELECT COUNT(*) FROM blobs_storage LIMIT 1");
		if ($is_blob_data) {
			echo "Could not recreate blobs_storage, still has data in it.\n";
		} else {
			echo "Recreating blobs_storage to reclaim disk space...\n";

			App::getDb()->exec("SET FOREIGN_KEY_CHECKS = 0");
			App::getDb()->exec("DROP TABLE IF EXISTS blobs_storage");
			App::getDb()->exec("
				CREATE TABLE `blobs_storage` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `blob_id` int(11) NOT NULL,
				  `data` longblob NOT NULL,
				  PRIMARY KEY (`id`),
				  KEY `blob_id_idx` (`blob_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			");
			App::getDb()->exec("SET FOREIGN_KEY_CHECKS = 1");

			echo "Done.\n";
		}

		echo "All done\n";
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
}
