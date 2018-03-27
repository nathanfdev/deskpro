<?php

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
