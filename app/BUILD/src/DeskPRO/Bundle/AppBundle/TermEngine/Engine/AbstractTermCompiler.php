<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\TermCompilerHelperInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\Util\TermTypeCodes;
use DeskPRO\Bundle\AppBundle\Util\SimpleTimer;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractTermCompiler.
 */
abstract class AbstractTermCompiler
{
    /**
     * @var TermCompilerHelperPool
     */
    protected $helperPool;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var SimpleTimer
     */
    protected $stateful_timer;

    /**
     * @var string
     */
    protected $short_name;

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param TermInterface $term
     */
    public function logStartingCompile(TermInterface $term)
    {
        // logEndingCompile method relies on $this->stateful_timer here
        $this->stateful_timer = new SimpleTimer();
        $this->logDebug('START', [
            'term_name' => TermTypeCodes::getTermTypeCode($term),
            'op'        => $term->getOp(),
            'options'   => $term->getOptions(),
        ]);
    }

    /**
     * @param TermInterface $term
     */
    public function logEndingCompile(TermInterface $term)
    {
        $this->logDebug('END', [
            'term_name'  => TermTypeCodes::getTermTypeCode($term),
            'op'         => $term->getOp(),
            'time_in_ms' => $this->stateful_timer->getElapsedTime(),
        ]);
    }

    /**
     * @param int    $level
     * @param string $message
     * @param array  $context
     */
    public function log($level, $message, array $context = [])
    {
        $message = sprintf('%s: %s', $this->getShortName(), $message);

        $this->getLogger()->log($level, $message, $context);
    }

    /**
     * @param string $message
     * @param array  $context
     */
    public function logDebug($message, array $context = [])
    {
        if (!$this->getLogger()) {
            return;
        }

        $this->log(Logger::DEBUG, $message, $context);
    }

    /**
     * @param string $message
     * @param array  $context
     */
    public function logInfo($message, array $context = [])
    {
        if (!$this->getLogger()) {
            return;
        }

        $this->log(Logger::INFO, $message, $context);
    }

    /**
     * @param string $message
     * @param array  $context
     */
    public function logNotice($message, array $context = [])
    {
        if (!$this->getLogger()) {
            return;
        }

        $this->log(Logger::NOTICE, $message, $context);
    }

    /**
     * @param string $message
     * @param array  $context
     */
    public function logWarning($message, array $context = [])
    {
        if (!$this->getLogger()) {
            return;
        }

        $this->getLogger()->log(Logger::WARNING, $message, $context);
    }

    /**
     * @param string $message
     * @param array  $context
     */
    public function logError($message, array $context = [])
    {
        if (!$this->getLogger()) {
            return;
        }

        $this->log(Logger::ERROR, $message, $context);
    }

    /**
     * @param string $message
     * @param array  $context
     */
    public function logCritical($message, array $context = [])
    {
        if (!$this->getLogger()) {
            return;
        }

        $this->log(Logger::CRITICAL, $context);
    }

    /**
     * @param string $message
     * @param array  $context
     */
    public function logAlert($message, array $context = [])
    {
        if (!$this->getLogger()) {
            return;
        }

        $this->log(Logger::ALERT, $message, $context);
    }

    /**
     * @param string $message
     * @param array  $context
     */
    public function logEmergency($message, array $context = [])
    {
        if (!$this->getLogger()) {
            return;
        }

        $this->log(Logger::EMERGENCY, $message, $context);
    }

    /**
     * @return string
     */
    public function getShortName()
    {
        if (!$this->short_name) {
            $ref              = new \ReflectionClass($this);
            $this->short_name = $ref->getShortName();
        }

        return $this->short_name;
    }

    /**
     * @param TermCompilerHelperPool $helperPool
     */
    public function setHelperPool(TermCompilerHelperPool $helperPool)
    {
        $this->helperPool = $helperPool;
    }

    /**
     * Get a registered helper by ID (TermCompilerHelperInterface::getId()).
     *
     * @param $id
     *
     * @return TermCompilerHelperInterface
     */
    public function getHelper($id)
    {
        return $this->helperPool->getHelper($id);
    }

    /**
     * @param TermInterface $term
     *
     * @return DbalQueryPart
     */
    public function compile(TermInterface $term)
    {
        $this->logStartingCompile($term);

        $return = $this->doCompile($term);

        $this->logEndingCompile($term);

        return $return;
    }

    /**
     * Use this shortcut to see if two op codes are the same.
     *
     * This normalizes the codes and then does the comparrison in a safe way.
     *
     * @param string $op
     * @param string $code
     *
     * @return bool
     */
    protected function isOp($op, $code)
    {
        return strtolower($op) === strtolower($code);
    }

    /**
     * Take a term and return a DbalQueryPart representing the term's query conditions.
     *
     * @param TermInterface $term
     *
     * @return DbalQueryPart
     */
    abstract protected function doCompile(TermInterface $term);
}
