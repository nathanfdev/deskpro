<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Mail
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Mail\QueueProcessor;

use \Application\DeskPRO\App;

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
				$db->delete('sendmail_queue', array('id' => $queue_id));
				$db->delete('sendmail_queue_part', array('sendmail_queue_id' => $queue_id));
			}

			if ($ret & self::PROCESS_FAILURE) {
				$db->execUpdate("UPDATE sendmail_queue SET attempts = attempts + 1 WHERE id = ?", array($queue_id));
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
			ORDER BY id ASC
			LIMIT 1
		");

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
			'to_address' => Util::coalesce(implode(', ', (array)$message->getTo()), ''),
			'date_created' => date('Y-m-d H:m:s')
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