<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\SystemAlerts\Bridge;

use DpSys\LowError\SystemErrorHandler;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SystemAlertsMonologHandler.
 */
class SystemAlertsMonologHandler extends AbstractProcessingHandler
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array Last logged error
     */
    private $lastRecordHash;

    /**
     * @param ContainerInterface $container
     * @param bool|int           $level
     * @param bool               $bubble
     */
    public function __construct(ContainerInterface $container, $level = Logger::NOTICE, $bubble = false)
    {
        $this->container = $container;
        parent::__construct($level, $bubble);
    }

    /**
     * @param array $record
     *
     * @throws \Exception
     */
    protected function write(array $record)
    {
        if (!empty($record['context']['exception']) && $record['context']['exception'] instanceof \Exception) {
            SystemErrorHandler::logException($record['context']['exception']);
        } else {
            $context = array_key_exists('context', $record) ? $record['context'] : [];
            $code    = array_key_exists('code', $context) ? $context['code'] : null;
            $message = array_key_exists('message', $context) ? $context['message'] : null;
            $file    = array_key_exists('file', $context) ? $context['file'] : null;
            $line    = array_key_exists('line', $context) ? $context['line'] : null;

            // To prevent Monolog from logging a PHP error with both error and fatal handlers
            // we compare record hash with hash of the previously logged one
            $hash = $this->recordHash($code, $message, $file, $line);
            if ($this->lastRecordHash === $hash) {
                return;
            }
            $this->lastRecordHash = $hash;
            SystemErrorHandler::logErrorInfo(SystemErrorHandler::getErrorInfo(E_ERROR, $message, $file, $line));
        }
    }

    /**
     * Get record hash.
     *
     * @param int    $code
     * @param string $message
     * @param string $file
     * @param int    $line
     *
     * @return string
     */
    private function recordHash($code, $message, $file, $line)
    {
        $message = md5($message);

        return "{$code}-{$file}-{$line}-{$message}";
    }
}
