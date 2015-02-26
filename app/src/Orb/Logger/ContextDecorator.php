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
 * Orb
 *
 * @package Orb
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
     * @param array $context
     * @param null $context_fn
     */
    public function __construct(LoggerInterface $logger, array $context = null, $context_fn = null)
    {
        $this->logger     = $logger;
        $this->context    = $context;
        $this->context_fn = $context_fn;
    }

    /**
     * @param array $context
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
                $context = array();
            }
        }

        return $context;
    }

    /**
     * {@inheritDoc}
     */
    public function log($level, $message, array $context = array())
    {
        return $this->logger->log($level, $message, $this->getContext($context));
    }

    /**
     * {@inheritDoc}
     */
    public function debug($message, array $context = array())
    {
        return $this->logger->debug($message, $this->getContext($context));
    }

    /**
     * {@inheritDoc}
     */
    public function info($message, array $context = array())
    {
        return $this->logger->info($message, $this->getContext($context));
    }

    /**
     * {@inheritDoc}
     */
    public function notice($message, array $context = array())
    {
        return $this->logger->notice($message, $this->getContext($context));
    }

    /**
     * {@inheritDoc}
     */
    public function warning($message, array $context = array())
    {
        return $this->logger->warning($message, $this->getContext($context));
    }

    /**
     * {@inheritDoc}
     */
    public function error($message, array $context = array())
    {
        return $this->logger->error($message, $this->getContext($context));
    }

    /**
     * {@inheritDoc}
     */
    public function critical($message, array $context = array())
    {
        return $this->logger->critical($message, $this->getContext($context));
    }

    /**
     * {@inheritDoc}
     */
    public function alert($message, array $context = array())
    {
        return $this->logger->alert($message, $this->getContext($context));
    }

    /**
     * {@inheritDoc}
     */
    public function emergency($message, array $context = array())
    {
        return $this->logger->emergency($message, $this->getContext($context));
    }
}