<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\JobQueue;

use Application\DeskPRO\DBAL\Connection;
use DpSys\LowError\SystemErrorHandler;

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
    public function __construct(Connection $connection, array $rules = [])
    {
        $this->connection = $connection;
        $this->rules      = $rules;
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
                    $this->reportUnresolvedViolation($e);
                } else {
                    $this->reportFixedViolation($e);
                }
            } catch (\Exception $e) {
                // something terribly wrong happened because we shouldn't be here, we should probably do something now
                // because this is a problem with the job supervising system! Probably DB query issues.
                SystemErrorHandler::logException($e);
            }
        }
    }

    public function reportUnresolvedViolation(JobSupervisorException $e)
    {
        SystemErrorHandler::logException($e);
    }

    public function reportFixedViolation(JobSupervisorException $e)
    {
        if ($e->canReport()) {
            SystemErrorHandler::logException($e);
        }
    }

    public function addRule(JobSupervisorRuleInterface $rule)
    {
        $this->rules[] = $rule;
    }
}
