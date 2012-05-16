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
 * @subpackage WorkerProcess
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
		$runner->setPhpTimeLimit(60);

		if ($this->options->get('run_source_id')) {
			$sid = $this->options->get('run_source_id');
			$source = App::getOrm()->find('DeskPRO:EmailSource', $sid);
			if (!$source) {
				$this->getLogger()->log("No source with ID $sid", 'NOTICE');
				return;
			}

			$runner->executeSource($source);

		} elseif ($this->options->get('run_gateway_id')) {
			$gid = $this->options->get('run_gateway_id');
			$this->getLogger()->log("Running specific gateway: $gid", 'DEBUG');

			$gateway = App::getOrm()->find('DeskPRO:EmailGateway', $this->options->get('run_gateway_id'));
			if (!$gateway) {
				$this->getLogger()->log("No gateway with ID $gid", 'NOTICE');
				return;
			}

			$runner->setGateways(array($gateway));
			$runner->execute(180);

		} else {
			$runner->loadGatewaysFromDb(false);
			$runner->execute(180);
		}

		// The PHP time limit would've been set above while processing messages,
		// reset it to disabled so other cron tasks can finish in this same execution
		@set_time_limit(0);
	}
}
