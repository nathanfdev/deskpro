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
 * @subpackage Log
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
