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

namespace DeskPRO\Bundle\UpdateBundle\DbBackup;

use DeskPRO\Bundle\UpdateBundle\Logger\LogKeyEvent;
use DeskPRO\Component\Util\Buffer\LineBuffer;
use DeskPRO\Component\Util\Timer;
use DeskPRO\Component\Util\TypeUtils;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class DbBackup implements DbBackupInterface, LoggerAwareInterface
{
    /**
     * @var CmdBuilderInterface
     */
    private $cmdBuilder;

    /**
     * @var DbVerifyInterface
     */
    private $verifier;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * DbBackup constructor.
     *
     * @param CmdBuilderInterface $cmdBuilder
     * @param DbVerifyInterface   $verifier
     */
    public function __construct(CmdBuilderInterface $cmdBuilder, DbVerifyInterface $verifier)
    {
        $this->cmdBuilder = $cmdBuilder;
        $this->verifier   = $verifier;

        $this->fs = new Filesystem();
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
    public function backupDatabase($targetPath, array $dbInfo)
    {
        $t = Timer::start();
        $this->logger->debug('backupDatabase -- begin', ['keyEvent' => LogKeyEvent::create('DbBackup.start')]);

        try {
            $this->doBackupDatabase($targetPath, $dbInfo);
            $this->logger->info('Database backup OK', ['keyEvent' => LogKeyEvent::create('DbBackup.success')]);
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('[%s:%s] %s', TypeUtils::getBaseTypeName($e), $e->getCode(), $e->getMessage()),
                ['keyEvent' => LogKeyEvent::createForException('DbBackup.error', $e)]
            );
            throw $e;
        } finally {
            $this->logger->debug(sprintf('backupDatabase -- done in %s', $t->formatTotalTime()));
        }
    }

    /**
     * @param       $targetPath
     * @param array $dbInfo
     *
     * @throws DbBackupException
     * @throws \Exception
     */
    private function doBackupDatabase($targetPath, array $dbInfo)
    {
        $targetDir = dirname($targetPath);
        $this->fs->mkdir($targetDir);

        $cmd = $this->cmdBuilder->getDumpCmd($targetPath, $dbInfo);

        $this->logger->info(sprintf('Backup target:  %s', $targetPath));
        $this->logger->info(sprintf('Backup command: %s', str_replace($dbInfo['password'], '***', $cmd)));

        $t      = Timer::start();
        $logger = $this->logger;
        $buf    = new LineBuffer(function ($dat) use ($logger) {
            $logger->debug($dat);
        });

        $process = new Process($cmd);
        $process->run(function ($type, $dat) use ($buf) {
            $buf->append($dat);
        });
        $buf->flush();

        $this->logger->debug('Command done in '.$t->formatTotalTime());

        if (!$process->isSuccessful()) {
            $this->logger->critical(sprintf('Backup error: Command exited with error status: %s', $process->getErrorOutput()));
            $this->logger->critical('-> '.$process->getErrorOutput());

            throw new DbBackupException(
                sprintf('Command exited with error status: %s', $process->getExitCode()),
                DbBackupException::DUMP_ERROR
            );
        }

        $this->logger->info(sprintf('File size: %d bytes', @filesize($targetPath)));
        $this->logger->debug('Verifying dump with: '.get_class($this->verifier));
        try {
            $this->verifier->verifyBackup($targetPath);
            $this->logger->info('Backup verified OK');
        } catch (\Exception $e) {
            $this->logger->critical('Verification failed with message: '.$e->getMessage());
            $this->logger->debug('Exception: '.get_class($e));
            throw $e;
        }

        $this->logger->info('Done DB backup');
    }
}
