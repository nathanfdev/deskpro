<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

use BigFileTools\BigFileTools;
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
     * The min size of any DB dump is ~6 MB which is just the schema and
     * initial records.
     */
    const MIN_SIZE = 6000000;

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

        try {
            /* @var $size \Brick\Math\BigInteger */
            $size = BigFileTools::createDefault()->getFile($filename)->getSize();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            throw $e;
        }

        if (false === $size) {
            $this->logger->critical(sprintf('Can\'t read dump file located in %s, please check the file manually', $filename));
            throw new DbBackupException('Database dump too small to be successful', DbBackupException::DUMP_ERROR_READ_FAILED);
        }

        if ($size->compareTo(self::MIN_SIZE) === -1) {
            $this->logger->critical(sprintf('Database dump filesize is too small, path: %s. Got: %d, expected at least: %d.', $filename, (string) $size, self::MIN_SIZE));
            throw new DbBackupException('Database dump too small to be successful', DbBackupException::DUMP_ERROR_TOOSMALL);
        }

        $this->logger->debug('Verify: File size OK');

        $this->logger->debug('Verify: Done in '.$t->getTotalTime());

        return true;
    }
}
