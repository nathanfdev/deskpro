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
				$log['url'] = App::getRequest()->getUri();
				$log['request_data'] = print_r($_REQUEST);
			}

			if ($log_item->getFlag() !== null) {
				$log['flag'] = $log_item->getFlag();
			}

			$info = $log_item->getExtra();
			if ($info) {
				$log['data'] = print_r($info, true);
			}

			$log['build'] = App::getBuildTime();

			$client = new \Zend\Http\Client(null, array('timeout' => 10));
			$client->setMethod(\Zend\Http\Request::METHOD_POST);
			$client->getRequest()->post()->set('log', $log);
			$client->setUri(DP_LIC_SERVER . '/report-error.json');
			$client->send();
		} catch (\Exception $e) {}
	}
}
