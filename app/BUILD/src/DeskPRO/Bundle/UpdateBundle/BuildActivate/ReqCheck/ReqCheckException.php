<?php

namespace DeskPRO\Bundle\UpdateBundle\BuildActivate\ReqCheck;

use DeskPRO\Bundle\UpdateBundle\BuildActivate\BuildActivatorException;
use Exception;
use Symfony\Component\Process\Process;

class ReqCheckException extends BuildActivatorException
{
    const CMD_ERROR   = 100;
    const FAILED_REQS = 200;

    /**
     * @var array
     */
    private $failedReqs = [];

    /**
     * @param $msg
     *
     * @return ReqCheckException
     */
    public static function createCommandException($msg)
    {
        return new self($msg, self::CMD_ERROR);
    }

    /**
     * @param Process $proc
     *
     * @return ReqCheckException
     */
    public static function createProcessException(Process $proc)
    {
        return new self(
            'Command returned error status code: '.$proc->getExitCode().': '.$proc->getExitCodeText(),
            self::CMD_ERROR,
            null,
            [['description' => 'Requirements checker', 'help' => 'The requirements checker command failed with an error status']]
        );
    }

    /**
     * @param array $failedReqs
     *
     * @return ReqCheckException
     */
    public static function createFailedRequirementsException(array $failedReqs)
    {
        return new self(
            sprintf('Requirements checker reported %d failure(s)', count($failedReqs)),
            self::FAILED_REQS,
            null,
            $failedReqs
        );
    }

    /**
     * {@inheritdoc}
     */
    public function __construct($message, $code, Exception $previous = null, array $failedReqs = null)
    {
        parent::__construct($message, $code, $previous);
        if ($failedReqs) {
            $this->failedReqs = $failedReqs;
        }
    }

    /**
     * @return array Array of ['description' => 'xxx', 'help' => 'xxx'] for each failure
     */
    public function getFailedRequirements()
    {
        return $this->failedReqs;
    }
}
