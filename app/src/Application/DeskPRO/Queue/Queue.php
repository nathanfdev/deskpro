<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Queue
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Queue;

use Orb\Util\Strings;
use Orb\Util\Util;
use Application\DeskPRO\Entity\QueueItem;

use \Zend\Queue\Queue as ZendQueue;
use \Zend\Queue\Exception as QueueException;
use \Zend\Queue\Message;

/**
 * Some changes to Queue to seamlessly handle messages that point to QueueItem datas.
 * With the database-driven adapter, nothing is done. But with others when data
 * exceeds a certain amount, the jobs contain a pointer to a QueueItem.
 */
class Queue extends ZendQueue
{
	protected $_messageClass = 'Application\DeskPRO\Queue\Message';

	public function send($message)
	{
		$max_size = $this->getOption('queueitem_threshold');
		if (!$max_size) {
			$max_size = 500;
		}

		#------------------------------
		# If the message is not too big, we can just store it in the queue store
		#------------------------------

		if ((is_string($message) && strlen($message) < $max_size) OR $this->getAdapter() instanceof \Application\DeskPRO\Queue\Adapter\QueueItemEntity) {
			return $this->getAdapter()->send($message);
		}


		#------------------------------
		# Otherwise we'll go and create a QueueItem, and change the message
		# to point to it.
		# - The Message item will correctly decode these and load the real data later
		#------------------------------

		// Note: important NOT to use the Em for creating QueueItems!
		// Sometimes the queue is used onFlush event, which means
		// persisting isn't so simple

		$db = $this->getOption('em')->getConnection();

		$item = array();
		$item['created_at'] = date('Y-m-d H:i:s');
		$item['is_dataonly'] = true;
		$item['data'] = $message;

		try {
			$db->insert('queue_items', $item);
			$item['id'] = $db->lastInsertId();

			$message = '<QueueItem:' . $item['id'] . '>';

			$success = $this->getAdapter()->send($message);
			$e = null;
		} catch (\Exception $e) {
			$success = false;
		}

		if (!$success) {
			try {
				$db->delete('queue_items', array('id' => $item['id']));
			} catch (\Exception $e) {}

			if ($e) {
				throw $e;
			}
		}

		return $success;
	}


	public function deleteMessage(Message $message)
	{
		if ($this->getAdapter() instanceof \Application\DeskPRO\Queue\Adapter\QueueItemEntity) {
			return $this->getAdapter()->deleteMessage($message);
		}

		$db->beginTransaction();
		if (isset($message->qi_id)) {
			try {
				$db = $this->getOption('em')->getConnection();
				$db->delete('queue_items', array('id' => $message->qi_id));
				$this->getAdapter()->deleteMessage($message);
				$db->commit();
			} catch (\Exception $e) {
				$db->rollback();
				throw $e;
			}
		}

		return true;
	}
}
