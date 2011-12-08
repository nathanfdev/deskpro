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

/**
 * Automatically load the body from a QI item
 */
class Message extends \Zend\Queue\Message
{
	protected $_has_init_qi = false;

	public function __get($key)
	{
		if ($this->_has_init_qi) {
			return parent::__get($key);
		}

		if ($key == 'body') {
			$this->_has_init_qi = true;
			$match = null;
			if (preg_match('#^<QueueItem:([0-9]+)>$#', $this->_data['body'])) {
				$db = $this->getAdapter()->getDb();
				$this->_data['body'] = $db->fetchColumn("SELECT data FROM queue_item WHERE id = ?", array($match[1]));
			}
		}

		return parent::__get($key);
	}
}
