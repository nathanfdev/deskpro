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
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

use Application\DeskPRO\App;

class Build1366896573 extends AbstractBuild
{
	public function run()
	{
		$this->out("Add sendmail_queue.blob_id field");
		$this->execMutateSql("ALTER TABLE sendmail_queue ADD blob_id INT DEFAULT NULL");
		$this->execMutateSql("ALTER TABLE sendmail_queue ADD CONSTRAINT FK_DDB369C2ED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id) ON DELETE CASCADE");
		$this->execMutateSql("CREATE INDEX IDX_DDB369C2ED3E8EA5 ON sendmail_queue (blob_id)");

		// Delete all success logs
		$this->execMutateSql("
			DELETE FROM sendmail_queue
			WHERE has_sent = 1 OR date_created < " . date('Y-m-d H:i:s', time() - 604800) . "
		");

		// Process all sendmail_queue_part to blobs
		$sm_ids = $this->container->getDb()->fetchAllCol("
			SELECT id
			FROM sendmail_queue
			ORDER BY id ASC
		");

		$st = $this->container->getDb()->prepare("
			SELECT data
			FROM sendmail_queue_part
			WHERE sendmail_queue_id = ?
			ORDER BY id ASC
		");
		foreach ($sm_ids as $id) {
			$st->execute(array($id));
			$data = '';
			while ($x = $st->fetchColumn(0)) {
				$data .= $x;
			}
			unset($x);
			$st->closeCursor();

			$blob = App::getContainer()->getBlobStorage()->createBlobRecordFromString(
				$data,
				'sendmail.eml',
				'message/rfc822'
			);

			$this->container->getDb()->update('sendmail_queue', array(
				'blob_id' => $blob->id
			), array('id' => $id));
		}

		// Drop old sendmail_queue_part table
		$this->execMutateSql("DROP TABLE sendmail_queue_part");
	}
}