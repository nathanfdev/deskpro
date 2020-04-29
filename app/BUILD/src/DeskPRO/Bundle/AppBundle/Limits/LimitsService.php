<?php

namespace DeskPRO\Bundle\AppBundle\Limits;

use Application\DeskPRO\Entity\ApiKey;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Limits\Adapter\LimitAdapterInterface;
use DeskPRO\Bundle\AppBundle\Limits\Exception\LimitExhaustedException;
use DeskPRO\Bundle\AppBundle\Limits\Model\AbstractLimit;
use DeskPRO\Bundle\AppBundle\Limits\Model\KeyLimit;
use DeskPRO\Bundle\AppBundle\Limits\Model\LimitInterface;

/**
 * Class LimitsService.
 */
class LimitsService
{
    /**
     * @var SettingsResolver
     */
    protected $settingsResolver;

    /**
     * @var LimitAdapterInterface
     */
    protected $limitAdapter;

    /**
     * Constructor.
     *
     * @param SettingsResolver      $settingsResolver
     * @param LimitAdapterInterface $limitAdapter
     */
    public function __construct(SettingsResolver $settingsResolver, LimitAdapterInterface $limitAdapter)
    {
        $this->settingsResolver = $settingsResolver;
        $this->limitAdapter     = $limitAdapter;
    }

    /**
     * @param ApiKey $key
     *
     * @return \DeskPRO\Bundle\AppBundle\Limits\Model\LimitInterface[]
     */
    public function getKeyLimits(ApiKey $key)
    {
        return $this->limitAdapter->getKeyLimits($key);
    }

    /**
     * @param ApiKey         $key
     * @param LimitInterface $limit
     */
    public function saveLimit(ApiKey $key, LimitInterface $limit)
    {
        if ($limit->getType() === AbstractLimit::TYPE_GLOBAL) {
            $this->limitAdapter->saveGlobalLimit($limit);
        } elseif ($limit->getType() === AbstractLimit::TYPE_KEY) {
            $this->limitAdapter->saveKeyLimit($limit, $key);
        }
    }

    /**
     * @param ApiKey $apiKey
     *
     * @throws LimitExhaustedException
     */
    public function checkLimits(ApiKey $apiKey)
    {
        $limits = $this->collectLimits($apiKey);
        foreach ($limits as $limit) {
            if (!$limit->replenish() && !$limit->hasLimit()) {
                throw new LimitExhaustedException();
            }
        }
    }

    /**
     * @param ApiKey $apiKey
     */
    public function reduceLimits(ApiKey $apiKey)
    {
        $limits = $this->collectLimits($apiKey);
        foreach ($limits as $limit) {
            $limit->replenish();
            $limit->reduceLimit();

            $this->saveLimit($apiKey, $limit);
        }
    }

    /**
     * @param ApiKey $apiKey
     *
     * @return LimitInterface|null
     */
    public function getMinLimit(ApiKey $apiKey)
    {
        /** @var LimitInterface $minLimit */
        $minLimit = null;
        $limits   = $this->collectLimits($apiKey);

        foreach ($limits as $limit) {
            if (!$minLimit || $minLimit->getCurrentLimit() > $limit->getCurrentLimit()) {
                $minLimit = $limit;
            }
        }

        return $minLimit;
    }

    /**
     * @param int $interval
     *
     * @return KeyLimit
     */
    public function createLimit($interval = AbstractLimit::INTERVAL_HOUR)
    {
        switch ($interval) {
            case 3600:
                $limitHit = $this->settingsResolver->getGlobalSettings()->get('api_limits.key.hour');
                break;
            case 86400:
                $limitHit = $this->settingsResolver->getGlobalSettings()->get('api_limits.key.day');
                break;
            default:
                $defaultLimit = $this->settingsResolver->getGlobalSettings()->get('api_limits.key.default', 0);
                $limitHit     = $this->settingsResolver->getGlobalSettings()->get('api_limits.key.day', $defaultLimit);
                break;
        }

        $limit = new KeyLimit();
        $limit
            ->setCurrent($limitHit)
            ->setLimit($limitHit)
            ->setInterval(new \DateInterval(sprintf('PT%dS', $interval)))
        ;

        return $limit;
    }

    /**
     * Collection all limits.
     *
     * @param ApiKey $apiKey
     *
     * @return LimitInterface[]
     */
    private function collectLimits(ApiKey $apiKey)
    {
        $limits = [];

        foreach ($this->limitAdapter->getGlobalLimits() as $globalLimit) {
            $limits[] = $globalLimit;
        }
        foreach ($this->getKeyLimits($apiKey) as $keyLimit) {
            $limits[] = $keyLimit;
        }

        return $limits;
    }
}
