<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Log
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Log\Writer;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Strings;
use Orb\Util\Arrays;

class LogItemEntity extends \Orb\Log\Writer\AbstractWriter
{
	public function _write(\Orb\Log\LogItem $log_item)
	{
		$log = new Entity\LogItem();
		$log['log_name']       = $log_item->getLogName();
		$log['session_name']   = $log_item->getSessionName();
		$log['message']        = $log_item->getMessage();
		$log['priority']       = $log_item->getPriority();
		$log['priority_name']  = $log_item->getPriorityName();
		$log['date_created']   = $log_item->getDatetime();

		if ($log_item->getFlag() !== null) {
			$log['flag'] = $log_item->getFlag();
		}

		$info = $log_item->getExtra();
		if ($info) {
			$log['data'] = $info;
		}
		
		App::getOrm()->persist($log);
		App::getOrm()->flush();
	}
}
