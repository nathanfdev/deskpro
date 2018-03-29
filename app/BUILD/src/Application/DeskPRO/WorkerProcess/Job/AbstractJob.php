<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Log\Logger;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;

/**
 * A job completes some specific processing task.
 */
abstract class AbstractJob
{
    const DEFAULT_INTERVAL = 3600;

    /**
     * @var int
     */
    protected $startTs;

    /**
     * @var \Orb\Util\OptionsArray
     */
    protected $options;

    /**
     * @var \Application\DeskPRO\Log\Logger
     */
    protected $logger;

    public function __construct(Logger $logger, array $options = null)
    {
        $this->startTs = time();
        $this->options = new \Orb\Util\OptionsArray($options);
        $this->logger  = $logger;
        $this->init();
    }

    /**
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    public function getContainer()
    {
        return App::$container;
    }

    protected function init()
    {
    }

    /**
     * Run the task.
     */
    abstract public function run();

    /**
     * Log a status message. These should include information about how many records
     * processed etc.
     *
     * @param string $message
     * @param array  $details
     */
    public function logStatus($message, array $details = [])
    {
        $details['flag'] = 'status';
        $this->logger->log($message, Logger::INFO, $details);
    }

    /**
     * Get the logger for this job.
     *
     * @return \Application\DeskPRO\Log\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param string $setting
     *
     * @return bool
     */
    public function getCrossBrandSetting($setting)
    {
        static $brands   = [];
        static $settings = [];

        if (isset($settings[$setting])) {
            return $settings[$setting];
        }
        if (empty($brands)) {
            /** @var Brand[] $brands */
            $brands = $this->getContainer()->getEm()->getRepository(Brand::class)->findAll();
        }

        $brandStack = $this->getContainer()->getBrandStack();

        /** @var BrandAwareSettingsResolver $brandSettingsResolver */
        $brandSettingsResolver = $this->getContainer()->get('brand_aware_settings_resolver');

        $value = false;

        foreach ($brands as $brand) {
            $brandStack->push($brand);
            $value = $value || $brandSettingsResolver->getSetting($setting);
            $brandStack->pop();
        }

        $settings[$setting] = $value;

        return $value;
    }

    /**
     * @return bool
     */
    public function isPastTimeLimit()
    {
        $timeLimit = $this->getTimeLimit();
        if (!$timeLimit) {
            return false;
        }

        $timeTaken = time() - $this->getStartTime();

        return $timeTaken > $timeLimit;
    }

    /**
     * @return int|null
     */
    public function getTimeLimit()
    {
        // no time limit
        if (!isset($this->options['time_limit']) || !$this->options['time_limit'] || $this->options['time_limit'] < 1) {
            return null;
        }

        return $this->options['time_limit'];
    }

    /**
     * @return int|null
     */
    public function getRemainingTime()
    {
        $timeLimit = $this->getTimeLimit();
        if (!$timeLimit) {
            return null;
        }

        $timeTaken  = time() - $this->getStartTime();
        $timeRemain = max($timeLimit - $timeTaken, 0);

        return $timeRemain;
    }

    /**
     * @return float|int
     */
    public function getStartTime()
    {
        return $this->startTs;
    }
}
