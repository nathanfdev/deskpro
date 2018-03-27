<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\JobQueue;

use Orb\Util\Strings;

/**
 * A JobSupervisorRuleInterface was violated.
 *
 * Default error code: 1550
 */
class JobSupervisorException extends \LogicException
{
    protected $report;

    public function __construct($message, $code = 1550, \Exception $previous = null)
    {
        $this->report = true;

        if (!Strings::startsWith('Job Supervisor', $message)) {
            $message = "Job Supervisor: $message";
        }
        parent::__construct($message, $code, $previous);
    }

    public function markDoNotReportIfFixed()
    {
        $this->report = false;
    }

    public function canReport()
    {
        return $this->report;
    }
}
