<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage WorkerProcess
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\Log\Logger;

/**
 * Goes through each gateway and processes email
 */
class ProcessEmailGateways extends AbstractJob
{
	const DEFAULT_INTERVAL = 60;

	public function run()
	{
		$logger = $this->getLogger();

		$runner = new \Application\DeskPRO\EmailGateway\Runner();
		$runner->setLogger($logger);
		$runner->loadGatewaysFromDb(false);
		$runner->execute();
	}
}
