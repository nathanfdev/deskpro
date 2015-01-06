<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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

namespace Application\ImportBundle\Generator;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressHelper;

/**
 * Class AbstractGenerator
 * @package Application\ImportBundle\Generator
 */
abstract class AbstractGenerator implements GeneratorInterface, LoggerAwareInterface, ProgressBarAwareInterface
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
     * @var ProgressHelper
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
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setProgressBarHelper(ProgressHelper $progress_bar)
    {
        $this->progress_bar = $progress_bar;
        return $this;
    }

    /**
     * Advance progress bar if it's defined
     *
     * @return void
     */
    protected function advanceProgressBar()
    {
        if ($this->progress_bar) {
            $this->progress_bar->advance();
        }
    }

    /**
     * Log info message if logger is defined
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
     * Log warning message if logger is defined
     *
     * @param string $message
     */
    protected function logWarning($message)
    {
        if ($this->logger) {
            $this->logger->warning($message);
        }
    }
}
