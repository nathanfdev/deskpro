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

use Application\DeskPRO\App;

class CleanupDaily extends AbstractJob
{
	const DEFAULT_INTERVAL = 86400;

	public function run()
	{
		#------------------------------
		# email sources
		#------------------------------

		if (App::getSetting('core.email_source_storetime')) {
			$snip = date('Y-m-d H:i:s', time() - App::getSetting('core.email_source_storetime'));
			$email_sources = App::getDb()->fetchAllCol("
				SELECT email_sources.id
				FROM email_sources
				WHERE email_sources.date_created < ? AND email_sources.status = 'complete'
				ORDER BY email_sources.id ASC
				LIMIT 1000
			", array($snip));

			$num = 0;
			foreach ($email_sources as $source) {
				$desc = App::getApi('filestorage')->getFileDescriptor($source->blob->id);
				$desc->delete();

				App::getOrm()->detach($source);
				App::getOrm()->flush();

				$num++;
			}

			if ($num) {
				$this->logStatus("Cleaned up $num stale email sources");
			}
		}

		#------------------------------
		# sendmail log
		#------------------------------

		$days = App::getSetting('core.store_sent_mail_days');

		if (!$days) {
			$num = App::getDb()->executeUpdate("
				DELETE FROM sendmail_queue
				WHERE has_sent = 1
			");
		} else {
			$datetime = date('Y-m-d H:i:s', strtotime("-$days days"));
			$datetime2 = date('Y-m-d H:i:s', strtotime("-" .($days * 5) ." days"));

			$num = App::getDb()->executeUpdate("
				DELETE FROM sendmail_queue
				WHERE (has_sent = 1 AND date_sent < ?) OR date_sent < ?",
			array($datetime, $datetime2));
		};

		if ($num) {
			$this->logStatus("Cleaned up $num sent emails");
		}

		#------------------------------
		# Remove old email process logs
		#------------------------------

		$datecut = date('Y-m-d H:i:s', time() - 1728000); // 20 days
		$num = App::getDb()->executeUpdate("
			UPDATE email_sources
			SET source_info = NULL
			WHERE date_created < ?
		", array($datecut));

		if ($num) {
			$this->logStatus("Cleaned up $num email source process logs");
		}

		#------------------------------
		# log_items
		#------------------------------

		$last_id = App::getDb()->fetchColumn("SELECT id FROM log_items ORDER BY id DESC LIMIT 1");
		if ($last_id) {
			$delete_before_id = $last_id - 25000; // approx 10 days worth of cron logs
			$num = App::getDb()->executeUpdate("DELETE FROM log_items WHERE id < $delete_before_id");

			if ($num) {
				$this->logStatus("Cleaned up $num cron log items");
			}
		}

		#------------------------------
		# Log Items
		#------------------------------

		$datecut = date('Y-m-d H:i:s', time() - 259200);
		$num = App::getDb()->executeUpdate("
			DELETE FROM ticket_changetracker_logs
			WHERE date_created < ?
		", array($datecut));

		if ($num) {
			$this->logStatus("Cleaned up $num old ticket change tracker logs");
		}

		#------------------------------
		# Task queue logs Items
		#------------------------------

		$cutoff = 86400 * 14; // 15 days
		$datecut = date('Y-m-d H:i:s', time() - $cutoff);
		$num = App::getDb()->executeUpdate("
			DELETE FROM task_queue
			WHERE status = 'completed' AND date_completed < ?
		", array($datecut));

		if ($num) {
			$this->logStatus("Cleaned up $num task queue logs");
		}
	}
}
