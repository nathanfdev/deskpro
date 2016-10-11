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

use DeskPRO\Component\Filesystem\BigFile;
use DeskPRO\Component\Util\Timer;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class DbVerify.
 */
class DbVerify implements DbVerifyInterface, LoggerAwareInterface
{
    /**
     * The min size of any DB dump is 8 MB which is just the schema and
     * initial records.
     */
    const MIN_SIZE = 8388608;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->logger = new NullLogger();
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
    public function verifyBackup($filename)
    {
        $t = Timer::start();

        if (!file_exists($filename)) {
            $this->logger->critical(sprintf('Database dump not found, path %s', $filename));
            throw new DbBackupException('Database dump not found', DbBackupException::DUMP_ERROR_NOTFOUND);
        }

        $this->logger->debug('Verify: File does exist');

        $size = BigFile::getFileSize($filename);
        if ($size < self::MIN_SIZE) {
            $this->logger->critical(sprintf('Database dump filesize is too small, path: %s. Got: %d, expected at least: %d.', $filename, $size, self::MIN_SIZE));
            throw new DbBackupException('Database dump too small to be successful', DbBackupException::DUMP_ERROR_TOOSMALL);
        }

        $this->logger->debug('Verify: File size OK');

        $fp = @fopen($filename, 'r');
        if (!$fp) {
            $this->logger->critical(sprintf('Could not open dump file for reading, path %s', $filename));
            throw new DbBackupException('Could not open dump file for reading', DbBackupException::DUMP_ERROR_OPEN_FAILED);
        }

        $this->logger->debug('Verify: File open OK');

        $filesize = fstat($fp)[7];
        $chunk    = @stream_get_contents($fp, 250000, $filesize - 250000);
        @fclose($fp);

        if (!$chunk) {
            $this->logger->critical(sprintf('Could not read dump file for reading, path %s', $filename));
            throw new DbBackupException('Could not read dump file', DbBackupException::DUMP_ERROR_READ_FAILED);
        }

        $this->logger->debug('Verify: File read OK');

        $chunk = str_replace('`', '', $chunk);

        if (strpos($chunk, 'CREATE TABLE worker_jobs') === false) {
            $this->logger->critical('Database dump seems invalid');
            throw new DbBackupException('Database dump seems invalid', DbBackupException::DUMP_ERROR_MISSING_TABLE);
        }

        $this->logger->debug('Verify: File table check OK');

        $this->logger->debug('Verify: Done in '.$t->getTotalTime());

        return true;
    }
}
