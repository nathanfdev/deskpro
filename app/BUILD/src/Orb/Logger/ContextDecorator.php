<?php

/**
 * Orb.
 *
 * @category Logger
 */

namespace Orb\Logger;

use Psr\Log\LoggerInterface;

/**
 * Wraps a logger so you can easily apply a certain context to all log messages.
 */
class ContextDecorator implements LoggerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array|null
     */
    private $context = null;

    /**
     * @var callable|null
     */
    private $context_fn = null;

    /**
     * @param LoggerInterface $logger
     * @param array           $context
     * @param null            $context_fn
     */
    public function __construct(LoggerInterface $logger, array $context = null, $context_fn = null)
    {
        $this->logger     = $logger;
        $this->context    = $context;
        $this->context_fn = $context_fn;
    }

    /**
     * @param array $context
     *
     * @return array
     */
    private function getContext(array $context)
    {
        if ($this->context) {
            $context = array_merge($context, $this->context);
        }
        if ($this->context_fn) {
            $context = call_user_func($this->context_fn, $context);
            if (!$context) {
                $context = [];
            }
        }

        return $context;
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = [])
    {
        return $this->logger->log($level, $message, $this->getContext($context));
    }

    /**
     * {@inheritdoc}
     */
    public function debug($message, array $context = [])
    {
        return $this->logger->debug($message, $this->getContext($context));
    }

    /**
     * {@inheritdoc}
     */
    public function info($message, array $context = [])
    {
        return $this->logger->info($message, $this->getContext($context));
    }

    /**
     * {@inheritdoc}
     */
    public function notice($message, array $context = [])
    {
        return $this->logger->notice($message, $this->getContext($context));
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message, array $context = [])
    {
        return $this->logger->warning($message, $this->getContext($context));
    }

    /**
     * {@inheritdoc}
     */
    public function error($message, array $context = [])
    {
        return $this->logger->error($message, $this->getContext($context));
    }

    /**
     * {@inheritdoc}
     */
    public function critical($message, array $context = [])
    {
        return $this->logger->critical($message, $this->getContext($context));
    }

    /**
     * {@inheritdoc}
     */
    public function alert($message, array $context = [])
    {
        return $this->logger->alert($message, $this->getContext($context));
    }

    /**
     * {@inheritdoc}
     */
    public function emergency($message, array $context = [])
    {
        return $this->logger->emergency($message, $this->getContext($context));
    }
}
