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

class ReportErrors extends \Orb\Log\Writer\AbstractWriter
{
	public function _write(\Orb\Log\LogItem $log_item)
	{
		try {
			$log = array();
			$log['license_id']     = \DeskPRO\Kernel\License::getLicense()->getLicenseId();
			$log['log_name']       = $log_item->getLogName();
			$log['session_name']   = $log_item->getSessionName();
			$log['message']        = $log_item->getMessage();
			$log['priority']       = $log_item->getPriority();
			$log['priority_name']  = $log_item->getPriorityName();
			$log['date_created']   = $log_item->getDatetime()->format('Y-m-d H:i:s');

			if (App::has(App::SERVICE_REQUEST)) {
				try {
					$log['url'] = App::getRequest()->getUri();
				} catch (\Exception $e) {
					$log['url'] = 'Command: ' . print_r(isset($_SERVER['argv'])?$_SERVER['argv']:'', true);
				}

                $log['server_ip'] = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'Unknown';
				$log['ref_url'] = empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'];
				$log['user_agent'] = empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'];
				$log['request_data'] = print_r($_REQUEST, 1);
			}

			if ($log_item->getFlag() !== null) {
				$log['flag'] = $log_item->getFlag();
			}

			$info = $log_item->getExtra();
			if ($info) {
				$log['data'] = print_r($info, true);
			}

			if (isset($info['subject'])) {
				$log['subject'] = $info['subject'];
			}

			$data = array('log' => $log);

			if (isset($info['summary'])) {
				$data['error_summary'] = $info['summary'];
			}

			if (isset($info['errfile'])) {
				$data['errfile'] = str_replace(DP_WEB_ROOT, '', $info['errfile']);
				$data['errline'] = $info['errline'];
			}

			\Application\DeskPRO\Service\ErrorReporter::sendReport('report-error', $data, 5);

		} catch (\Exception $e) {}
	}
}
