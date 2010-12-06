<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Queue
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\DeskPRO\Queue;

use \Orb\Util\Strings;
use \Orb\Util\Util;
use \Application\CoreBundle\Entity\QueueItem;


use \Zend\Queue\Queue;
use \Zend\Queue\Exception as QueueException;
use \Zend\Queue\Message;

/**
 * Some changes to Queue to seamlessly handle messages that point to QueueItem datas.
 * With the database-driven adapter, nothing is done. But with others when data
 * exceeds a certain amount, the jobs contain a pointer to a QueueItem.
 */
class Queue extends \Zend\Queue\Queue
{
	protected $_messageClass = '\DeskPRO\Queue\Message';

	public function send($message)
	{
		$max_size = $this->getOption('queueitem_threshold');
		if (!$max_size) {
			$max_size = 500;
		}

		#------------------------------
		# If the message is not too big, we can just store it in the queue store
		#------------------------------

		if (strlen($message) < $max_size OR $this->getAdapter() instanceof \Application\DeskPRO\Queue\Adapter\QueueItemEntity) {
			return $this->getAdapter()->send($message);
		}


		#------------------------------
		# Otherwise we'll go and create a QueueItem, and change the message
		# to point to it.
		# - The Message item will correctly decode these and load the real data later
		#------------------------------
		
		$em = $this->getOption('em');
		$item = new \Application\CoreBundle\Entity\QueueItem();
		$item['is_dataonly'] = true;
		$item['data'] = $message;
		$em->persist($item);
		$em->flush();

		$message = '<QueueItem:' . $item['id'] . '>';
		try {
			$success = $this->getAdapter()->send($message);
			$e = null;
		} catch (Exception $e) {
			$success = false;
		}

		if (!$success) {
			$em->delete($item);
			$em->flush();

			if ($e) {
				throw $e;
			}
		}

		return $success;
	}
}