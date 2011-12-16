<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Mail
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
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

			$queue_info = $db->fetchAssoc("SELECT * FROM sendmail_queue WHERE id = ?", $queue_id);
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

			$ret = call_user_func($callback, $message);
			if ($ret & self::PROCESS_SUCCESS) {
				$queue_info['date_sent'] = date('Y-m-d H:m:s');
				$db->execUpdate("
					UPDATE sendmail_queue
					SET attempts = ?, date_sent = ?, has_sent = 1, date_next_attempt = null
					WHERE id = ?", array($queue_info['attempts'], $queue_info['date_sent'], $queue_id)
				);
			}

			if ($ret & self::PROCESS_FAILURE) {
				switch ($queue_info['attemps']) {
					case 1:
						$queue_info['date_next_attempt'] = date('Y-m-d H:m:s', strtotime('+5 minutes'));
						break;

					case 2:
						$queue_info['date_next_attempt'] = date('Y-m-d H:m:s', strtotime('+15 minutes'));
						break;

					case 3:
					case 4:
						$queue_info['date_next_attempt'] = date('Y-m-d H:m:s', strtotime('+45 minutes'));
						break;

					case 5:
						$queue_info['date_next_attempt'] = date('Y-m-d H:m:s', strtotime('+1.5 hours'));
						break;

					case 6:
					case 7:
						$queue_info['date_next_attempt'] = date('Y-m-d H:m:s', strtotime('+3 hours'));
						break;

					case 8:
						$queue_info['date_next_attempt'] = date('Y-m-d H:m:s', strtotime('+5 hours'));
						break;

					case 9:
						$queue_info['date_next_attempt'] = date('Y-m-d H:m:s', strtotime('+12 hours'));
						break;

					default:
						// Give up as abandoned
						$queue_info['date_next_attempt'] = null;
				}

				$db->execUpdate("
					UPDATE sendmail_queue
					SET attempts = ?, date_next_attempt = ?
					WHERE id = ?", array($queue_info['attempts'], $queue_info['date_next_attempt'], $queue_id)
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
		", array(date('Y-m-d H:m:s')));

		return $queue_id;
	}



	/**
	 * Add a message to the queue
	 *
	 * @param Orb\Mail\Message $message
	 */
	public function addQueuedMessage(\Orb\Mail\Message $message)
	{
		$db = App::getDb();

		$db->beginTransaction();

		$db->insert('sendmail_queue', array(
			'subject' => Util::coalesce($message->getSubject(), ''),
			'to_address' => Util::coalesce(implode(', ', (array)$message->getTo()), ''), // this is really just for info purposes, easier to grep the db
			'date_created' => date('Y-m-d H:m:s'),
			'date_next_attempt' => date('Y-m-d H:m:s'),
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
	 * @param Orb\Mail\Message $message
	 */
	public function addLoggedMessage(\Orb\Mail\Message $message)
	{
		$db = App::getDb();

		$db->beginTransaction();

		$db->insert('sendmail_queue', array(
			'subject' => Util::coalesce($message->getSubject(), ''),
			'to_address' => Util::coalesce(implode(', ', (array)$message->getTo()), ''),
			'date_created' => date('Y-m-d H:m:s'),
			'date_sent' => date('Y-m-d H:m:s'),
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

		return true;
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
