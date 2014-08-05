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
 */

namespace Application\DeskPRO\ServerErrorLogs;

use Application\DeskPRO\App;
use Application\DeskPRO\Log\ErrorLog\ErrorLogReader;
use Doctrine\ORM\EntityManager;
use Orb\Util\Util;

class ServerErrorLogs
{
	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */

	protected $em;

	/**
	 * @var string
	 */

	protected $config_hash;

	public function __construct(EntityManager $em)
	{
		$this->em          = $em;
		$this->config_hash = md5_file(DP_CONFIG_FILE);
	}

	/**
	 * @return array
	 */

	public function getAll()
	{
		$log_reader = new ErrorLogReader(dp_get_log_dir() . '/error.log');
		$log_reader->setDateTimezone(App::getSession()->getPerson()->getDateTimezone());

		return array(
			'logs'                  => array_values($log_reader->getAll()),
			'deskpro_error_log_url' => $this->_generateUrl('_sys=errorlog'),
			'web_error_log_url'     => $this->_generateUrl('_sys=errorlog&web'),
			'cli_error_log_url'     => $this->_generateUrl('_sys=errorlog&cli')
		);
	}


	/**
	 * @param int $id
	 *
	 * @return array|null
	 */

	public function getById($id)
	{
		$log_reader = new ErrorLogReader(dp_get_log_dir() . '/error.log');
		$log_reader->setDateTimezone(App::getSession()->getPerson()->getDateTimezone());
		$log_reader->enableRawLog();
		$log_reader->setIdFilter($id);

		$log = $log_reader->current();

		return $log;
	}

	/**
	 * @return bool
	 */

	public function clearAllErrors()
	{
		if (!is_writable(dp_get_log_dir() . '/error.log')) {

			return false;
		}

		@file_put_contents(dp_get_log_dir() . '/error.log', '');

		return true;
	}

	/**
	 * @param string $url
	 *
	 * @return string
	 */

	protected function _generateUrl($url)
	{
		$result = App::getSetting('core.deskpro_url') . '?' . $url;
		$result .= '&_=' . Util::generateStaticSecurityToken($this->config_hash . 'errorlog', 86400);

		return $result;
	}
}