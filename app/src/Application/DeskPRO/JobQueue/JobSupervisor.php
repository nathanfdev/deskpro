<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @subpackage JobQueue
 */

namespace Application\DeskPRO\JobQueue;

use Application\DeskPRO\DBAL\Connection;
use DeskPRO\Kernel\KernelErrorHandler;

/**
 * The JobSupervisor maintains a list of rules that contain business logic to determine if they are violated.
 *
 * When you run() the JobSupervisor, it iterates over all of the rules and makes them check for violations.
 * In the case that a violation is encountered inside of a rule check, the rule fires a JobSupervisorException
 * with a detailed message.
 *
 * After the exception is caught, the rule has a chance to attempt to fix its problem, and if it can successfully fix
 * the violation, we silently log it, but do not fire out an admin alert.
 *
 * If the rule cannot fix the violation, the JobSupervisor will send a notification to the admin.
 */
class JobSupervisor
{
	/**
	 * @var JobSupervisorRuleInterface[]
	 */
	protected $rules;

	/**
	 * @var Connection
	 */
	private $connection;


	/**
	 * @param Connection $connection
	 * @param array      $rules
	 */
	public function __construct(Connection $connection, array $rules = array())
	{
		$this->connection = $connection;
		$this->rules = $rules;
	}


	/**
	 * Runs the supervisor instance, checking all of the registered rules, and reporting any violations that
	 * cannot be fixed.
	 */
	public function run()
	{
		foreach ($this->rules as $rule) {
			try {

				$rule->check();

			} catch (JobSupervisorException $e) {

				if (!$rule->attemptToFix()) {
					$this->reportViolation($e);
				} else {
					// fixed, silently log the violation and that it was resolved by the rule
					KernelErrorHandler::logException($e);
				}

			} catch (\Exception $e) {
				// something terribly wrong happened because we shouldn't be here, we should probably do something now
				// because this is a problem with the job supervising system! Probably DB query issues.
				KernelErrorHandler::logException($e);
			}
		}

	}


	public function reportViolation(JobSupervisorException $e)
	{
		KernelErrorHandler::logException($e);
	}

	public function addRule(JobSupervisorRuleInterface $rule)
	{
		$this->rules[] = $rule;
	}
}
