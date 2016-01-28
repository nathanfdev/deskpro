<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\ImportBundle\Generator;

use DeskPRO\Kernel\KernelErrorHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Abstract generator methods
 * Split into traits when php version >= 5.4.
 *
 * Class AbstractGenerator
 */
abstract class AbstractGenerator
    implements GeneratorConfigAwareInterface, LoggerAwareInterface, ProgressBarAwareInterface
{
    /**
     * @var GeneratorConfig
     */
    protected $config;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $debug_timers = array();

    /**
     * @var ProgressBar
     */
    protected $progress_bar;

    /**
     * {@inheritdoc}
     */
    public function setConfig(GeneratorConfig $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return GeneratorConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * {@inheritdoc}
     */
    public function setProgressBarHelper(ProgressBar $progress_bar)
    {
        $this->progress_bar = $progress_bar;

        return $this;
    }

    /**
     * @return ProgressBar
     */
    public function getProgressBarHelper()
    {
        return $this->progress_bar;
    }

    /**
     * Advance progress bar if it's defined.
     */
    protected function advanceProgressBar()
    {
        if ($this->progress_bar) {
            if ($this->progress_bar->getStep() < $this->progress_bar->getMaxSteps()) {
                $this->progress_bar->advance();
            }
        }
    }

    /**
     * Log info message if logger is defined.
     *
     * @param string $message
     */
    protected function logInfo($message)
    {
        if ($this->logger) {
            $this->logger->info($message);
        }
    }

    /**
     * @param string $id
     * @param string $message
     */
    protected function logDebugTimeStart($id, $message)
    {
        $this->debug_timers[$id] = microtime(true);
        if ($this->logger) {
            $this->logger->debug($message);
        }
    }

    /**
     * @param string $id
     * @param string $message
     */
    protected function logDebugTimeEnd($id, $message)
    {
        $time = sprintf('%.3fs', microtime(true) - $this->debug_timers[$id]);
        if ($this->logger) {
            $this->logger->debug($message." -- $time");
        }
    }

    /**
     * Log debug message if logger is defined.
     *
     * @param string $message
     */
    protected function logDebug($message)
    {
        if ($this->logger) {
            $this->logger->debug($message);
        }
    }

    /**
     * Logs a message with some array of data.
     *
     * @param string      $message
     * @param mixed|array $info
     */
    protected function logDebugInfo($message, $info)
    {
        if ($this->logger) {
            if ($message) {
                $this->logger->debug($message);
            }
            if ($info) {
                foreach (explode("\n", KernelErrorHandler::varToString($info, 3)) as $l) {
                    $this->logger->debug('  [info] '.$l);
                }
            }
        }
    }

    /**
     * Logs debug message with exception and optionally array of data.
     *
     * @param string      $message
     * @param \Exception  $e
     * @param mixed|array $info
     */
    protected function logDebugException($message, \Exception $e, $info = null)
    {
        if ($this->logger) {
            if ($message) {
                $this->logger->debug($message);
            }

            $einfo = KernelErrorHandler::getExceptionInfo($e);
            $this->logger->debug($einfo['summary']);
            foreach (explode("\n", $einfo['trace']) as $l) {
                $this->logger->debug('  -> '.$l);
            }

            if ($info) {
                foreach (explode("\n", KernelErrorHandler::varToString($info, 3)) as $l) {
                    $this->logger->debug('  [info] '.$l);
                }
            }
        }
    }

    /**
     * Log notice message if logger is defined.
     *
     * @param string $message
     */
    protected function logNotice($message)
    {
        if ($this->logger) {
            $this->logger->notice($message);
        }
    }

    /**
     * Log warning message if logger is defined.
     *
     * @param string $message
     */
    protected function logWarning($message)
    {
        if ($this->logger) {
            $this->logger->warning($message);
        }
    }

    /**
     * Log alert message if logger is defined.
     *
     * @param string $message
     */
    protected function logAlert($message)
    {
        if ($this->logger) {
            $this->logger->alert($message);
        }
    }

    /**
     * Log error message if logger is defined.
     *
     * @param string $message
     */
    protected function logError($message)
    {
        if ($this->logger) {
            $this->logger->error($message);
        }
    }

    /**
     * Log critical message if logger is defined.
     *
     * @param string $message
     */
    protected function logCritical($message)
    {
        if ($this->logger) {
            $this->logger->critical($message);
        }
    }
}
