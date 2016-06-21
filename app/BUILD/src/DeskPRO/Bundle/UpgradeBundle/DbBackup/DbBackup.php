<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\UpgradeBundle\DbBackup;

use Eloquent\Pathogen\Path;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

class DbBackup implements DbBackupInterface, LoggerAwareInterface
{
    /**
     * @var CmdBuilderInterface
     */
    private $cmd_builder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param CmdBuilderInterface $cmd_builder
     */
    public function __construct(CmdBuilderInterface $cmd_builder)
    {
        $this->cmd_builder = $cmd_builder;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function backupDatabase(Path $filename)
    {
        $this->logDebug(sprintf('Backup directory:  %s', $filename->getOriginalPath()));
        $this->logDebug(sprintf('Backup command:    %s', $this->cmd_builder->getDumpLogCmd($filename)));

        $process = new Process($this->cmd_builder->getDumpCmd($filename));
        $process->run();

        if (!$process->isSuccessful()) {
            $this->logCritical(sprintf('Backup error: Command exited with error status: %s', $process->getErrorOutput()));
            $this->logCritical('-> '.$process->getErrorOutput());

            throw new DbBackupException(
                sprintf('Command exited with error status: %s', $process->getExitCode()),
                DbBackupException::DUMP_ERROR
            );
        }
    }

    /**
     * Logs info messages.
     *
     * @param string $message
     */
    private function logDebug($message)
    {
        if ($this->logger) {
            $this->logger->debug($message);
        }
    }

    /**
     * Logs critical messages.
     *
     * @param string $message
     */
    private function logCritical($message)
    {
        if ($this->logger) {
            $this->logger->critical($message);
        }
    }
}
