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
use Application\DeskPRO\Monolog\Logger;
use Application\DeskPRO\Monolog\Handler\OrbLoggerAdapterHandler;
use Application\DeskPRO\Tickets\Actions\ActionApplicator;
use Application\DeskPRO\Tickets\Escalations\EscalationExecutor;
use Application\DeskPRO\Tickets\Escalations\EscalationsRunner;
use Application\DeskPRO\Tickets\Escalations\EscalationTicketMatcher;

/**
 * Executes escalations
 */
class TicketEscalations extends AbstractJob
{
	const DEFAULT_INTERVAL = 60;

	/** @var int */
	protected $count_success;
	/** @var int */
	protected $count_failed;

	public function run()
	{
		$escalations = App::$container->getEm()->createQuery("
			SELECT e
			FROM DeskPRO:TicketEscalation e
			WHERE e.is_enabled = true
			ORDER BY e.date_last_run ASC
		")->execute();

		if (!count($escalations)) {
			return;
		}

		$batch_size = 100;
		$time_limit = 200;

		$orb_adapter = new OrbLoggerAdapterHandler($this->getLogger());
		$logger = new Logger('TicketTriggers');
		$logger->pushHandler($orb_adapter);

		$matcher  = new EscalationTicketMatcher(App::$container->getEm(), App::$container->getDb());
		$matcher->setLogger($logger);

		$executor = new EscalationExecutor(App::$container->getDb(), App::$container->getTicketManager(), new ActionApplicator(App::$container));
		$executor->setLogger($logger);

		$runner = new EscalationsRunner(
			$escalations,
			$matcher,
			$executor,
			$batch_size,
			$time_limit
		);
		$runner->setLogger($logger);

		$GLOBALS['DP_ESCALATION_RUNNING'] = true;
		$runner->run();
		unset($GLOBALS['DP_ESCALATION_RUNNING']);
	}
}