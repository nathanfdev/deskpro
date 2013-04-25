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
 * Orb
 *
 * @package Orb
 * @subpackage Mail
 */

namespace Application\DeskPRO\Mail\QueueProcessor;

use Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * Stores messages in the database
 */
class Database implements \Orb\Mail\QueueProcessor\QueueProcessorInterface
{
	/**
	 * Get the next queued message
	 *
	 * @return Message
	 */
	public function processQueue($callback)
	{
		$db = App::getDb();

		while ($queue_id = $this->getNextId()) {

			$queue_info = $db->fetchAssoc("SELECT * FROM sendmail_queue WHERE id = ?", array($queue_id));
			if (!$queue_info) {
				continue;
			}

			$queue_info['attempts']++;

			$message = $db->fetchAllCol("
				SELECT data FROM sendmail_queue_part
				WHERE sendmail_queue_id = ?
				ORDER BY id ASC
			", array($queue_id));
			$message = implode('', $message);
			$message = unserialize($message);


			if (!$message) {
				throw new \RuntimeException('Failed to read or unserialize message');
			}

			if ($message instanceof \Application\DeskPRO\Mail\Message) {
				$message->setIsRetrying();
			}

			$ret = call_user_func($callback, $message);
			if ($ret & self::PROCESS_SUCCESS) {
				$queue_info['date_sent'] = date('Y-m-d H:i:s');

				if (!App::getSetting('core.store_sent_mail_days')) {
					$db->executeUpdate("
						DELETE FROM sendmail_queue
						WHERE id = ?", array($queue_id)
					);
				} else {
					$db->executeUpdate("
						UPDATE sendmail_queue
						SET attempts = ?, date_sent = ?, has_sent = 1, date_next_attempt = null
						WHERE id = ?", array($queue_info['attempts'], $queue_info['date_sent'], $queue_id)
					);
				}
			}

			if ($ret & self::PROCESS_FAILURE) {
				switch ($queue_info['attempts']) {
					case 1:
						$queue_info['date_next_attempt'] = date('Y-m-d H:i:s', strtotime('+5 minutes'));
						break;

					case 2:
						$queue_info['date_next_attempt'] = date('Y-m-d H:i:s', strtotime('+15 minutes'));
						break;

					case 3:
					case 4:
						$queue_info['date_next_attempt'] = date('Y-m-d H:i:s', strtotime('+45 minutes'));
						break;

					case 5:
						$queue_info['date_next_attempt'] = date('Y-m-d H:i:s', strtotime('+1.5 hours'));
						break;

					case 6:
					case 7:
						$queue_info['date_next_attempt'] = date('Y-m-d H:i:s', strtotime('+3 hours'));
						break;

					case 8:
						$queue_info['date_next_attempt'] = date('Y-m-d H:i:s', strtotime('+5 hours'));
						break;

					case 9:
						$queue_info['date_next_attempt'] = date('Y-m-d H:i:s', strtotime('+12 hours'));
						break;

					default:
						// Give up as abandoned
						$queue_info['date_next_attempt'] = null;
				}

				$db->executeUpdate("
					UPDATE sendmail_queue
					SET attempts = ?, date_next_attempt = ?
					WHERE id = ?", array(isset($queue_info['attempts']) ? $queue_info['attempts'] : 1, $queue_info['date_next_attempt'], $queue_id)
				);
			}

			if ($ret & self::PROCESS_STOP) {
				return;
			}
		}
	}

	public function getNextId()
	{
		$queue_id = App::getDb()->fetchColumn("
			SELECT id FROM sendmail_queue
			WHERE has_sent = 0 AND date_next_attempt < ?
			ORDER BY id ASC
			LIMIT 1
		", array(date('Y-m-d H:i:s')));

		return $queue_id;
	}



	/**
	 * Add a message to the queue
	 *
	 * @param \Orb\Mail\Message $message
	 */
	public function addQueuedMessage(\Orb\Mail\Message $message)
	{
		$db = App::getDb();

		$db->beginTransaction();

		$db->insert('sendmail_queue', array(
			'subject' => Util::coalesce($message->getSubject(), ''),
			'to_address' => Util::coalesce(implode(', ', array_keys($message->getTo())), ''), // this is really just for info purposes, easier to grep the db
			'date_created' => date('Y-m-d H:i:s'),
			'date_next_attempt' => date('Y-m-d H:i:s', time() + 120),
		));
		$queue_id = $db->lastInsertId();

		$message = serialize($message);
		$data_len = strlen($message);

		// /2 for worst-case scenario of every character needing escape, -200 for wiggle room fo rest of query
		$max_size = ($db->getMaxPacketSize()/2)-200;
		$parts = ceil($data_len / $max_size);

		for ($i = 0; $i < $parts; $i++) {
			$db->insert('sendmail_queue_part', array(
				'sendmail_queue_id' => $queue_id,
				'data' => substr($message, $i * $max_size, $max_size)
			));
		}

		$db->commit();

		return true;
	}


	/**
	 * Add a message to the database as sent
	 *
	 * @param \Orb\Mail\Message $message
	 */
	public function addLoggedMessage(\Orb\Mail\Message $message)
	{
		if (!App::getSetting('core.store_sent_mail_days')) {
			return;
		}

		$db = App::getDb();

		$db->beginTransaction();

		$db->insert('sendmail_queue', array(
			'subject' => Util::coalesce($message->getSubject(), ''),
			'to_address' => Util::coalesce(implode(', ', array_keys($message->getTo())), ''),
			'date_created' => date('Y-m-d H:i:s'),
			'date_sent' => date('Y-m-d H:i:s'),
			'has_sent' => true,
			'attempts' => 1
		));
		$queue_id = $db->lastInsertId();

		$message = serialize($message);
		$data_len = strlen($message);

		// /2 for worst-case scenario of every character needing escape, -200 for wiggle room fo rest of query
		$max_size = ($db->getMaxPacketSize()/2)-200;
		$parts = ceil($data_len / $max_size);

		for ($i = 0; $i < $parts; $i++) {
			$db->insert('sendmail_queue_part', array(
				'sendmail_queue_id' => $queue_id,
				'data' => substr($message, $i * $max_size, $max_size)
			));
		}

		$db->commit();
	}


	/**
	 * Start the queue system
	 */
	public function startQueue() {}

	/**
	 * Shutdown the queue system
	 */
	public function shutdownQueue() {}
}
