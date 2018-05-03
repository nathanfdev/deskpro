<?php

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
    public function backupDatabase($targetPath, array $dbInfo, array $options = [])
    {
        $t = Timer::start();
        $this->logger->debug('backupDatabase -- begin', ['keyEvent' => LogKeyEvent::create('DbBackup.start')]);

        try {
            $this->doBackupDatabase($targetPath, $dbInfo, $options);
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
     * @param array $options
     *
     * @throws DbBackupException
     * @throws \Exception
     */
    private function doBackupDatabase($targetPath, array $dbInfo, array $options)
    {
        $targetDir = dirname($targetPath);
        $this->fs->mkdir($targetDir);

        $cmd = $this->cmdBuilder->getDumpCmd($targetPath, $dbInfo, $options);

        $this->logger->info(sprintf('Backup target:  %s', $targetPath));
        $this->logger->info(sprintf('Backup command: %s', str_replace($dbInfo['password'], '***', $cmd)));

        $freeSpace = disk_free_space(dirname($targetPath));
        if (!$freeSpace) {
            $this->logger->critical(sprintf('Could not determine free disk space'));
            throw new DbBackupException(
                'Could not determine free disk space',
                DbBackupException::DISK_SPACE_UNKNOWN
            );
        }

        if (!isset($options['skip_diskspace_check']) || !$options['skip_diskspace_check']) {
            try {
                $pdo    = \DpRun\LowUtil::getPdoFromMysqlInfo($dbInfo);
                $dbSize = $pdo->query("
                    SELECT SUM(data_length + index_length) AS 'size'
                    FROM information_schema.TABLES
                    WHERE table_schema = '{$dbInfo['dbname']}'
                ")->fetchColumn(0);

                if (!$dbSize || $dbSize < 9000000) {
                    $this->logger->critical(sprintf('Could not determine how much disk space is required'));
                    throw new DbBackupException(
                        'Could not determine how much disk space is required because query returned an unexpected result',
                        DbBackupException::DISK_SPACE_UNKNOWN
                    );
                }
            } catch (\Exception $e) {
                $this->logger->critical(sprintf('Could not determine how much disk space is required'));
                throw new DbBackupException(
                    'Could not determine how much disk space is required because PDO failed',
                    DbBackupException::DISK_SPACE_UNKNOWN,
                    $e
                );
            }

            if ($freeSpace < ($dbSize * 1.5)) {
                $msg = sprintf('Detected insufficient disk space. Free disk space: %s, Database size: %s', $freeSpace, $dbSize);
                $this->logger->critical($msg);
                throw new DbBackupException(
                    $msg,
                    DbBackupException::DISK_SPACE_INSUFFICIENT
                );
            }
        } else {
            $this->logger->info('Disk space check was skipped');
        }

        $t      = Timer::start();
        $logger = $this->logger;
        $buf    = new LineBuffer(function ($dat) use ($logger) {
            $logger->debug($dat);
        });

        $process = new Process($cmd);
        $process->setTimeout(null);
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
