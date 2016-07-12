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

namespace Application\ImportBundle\Reader\ZenDesk\Fixtures;

use DateTime;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractFixtureHelper.
 */
abstract class AbstractFixtureHelper
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
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
     * Returns a random datetime.
     *
     * @param DateTime $initial_time
     * @param DateTime $end_time
     *
     * @return DateTime
     */
    protected function getRandomDateTime(DateTime $initial_time, DateTime $end_time)
    {
        $time = new DateTime();
        $time->setTimestamp(rand($initial_time->getTimestamp(), $end_time->getTimestamp()));

        return $time;
    }

    /**
     * Returns random boolean value.
     *
     * @return mixed
     */
    protected function getRandomBool()
    {
        $rand   = rand(0, 1);
        $values = [true, false];

        return $values[$rand];
    }

    /**
     * Returns random boolean string value.
     *
     * @return mixed
     */
    protected function getRandomBoolString()
    {
        $rand   = rand(0, 1);
        $values = ['true', 'false'];

        return $values[$rand];
    }

    /**
     * Returns a random local file to upload.
     *
     * @param bool $as_curl
     *
     * @return mixed
     */
    protected function getRandomUploadFile($as_curl = true)
    {
        $dp_root = str_replace('/app', '/', DP_ROOT);
        $files   = [
            '/web/images/big-tick.png',
        ];

        $file = $files[rand(0, count($files) - 1)];
        $file = $dp_root.$file;

        if (!file_exists($file)) {
            throw new \RuntimeException(sprintf('File `%s` not found', $file));
        }
        if ($as_curl) {
            return sprintf('@%s;filename=%s', realpath($file), basename($file));
        }

        return $file;
    }

    /**
     * Converts stdClass to array.
     *
     * @param \stdClass $object
     *
     * @return array
     */
    protected function toArray($object)
    {
        return json_decode(json_encode($object), true);
    }
}
