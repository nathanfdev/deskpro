<?php

namespace DpBehat\Api;

use DeskPRO\Bundle\AppBundle\Entity\ApiKeyLimit;
use DeskPRO\Bundle\AppBundle\Limits\Model\AbstractLimit;
use DpBehat\BaseContext;
use DpBehat\Data\DataContext;

/**
 * Class ApiLimitsContext.
 */
class ApiLimitsContext extends BaseContext
{
    /**
     * @Given my key limit almost exhausted
     */
    public function myKeyLimitAlmostExhausted()
    {
        $limit = $this->getApiKeyLimitRepository()->findOneBy(['api_key' => DataContext::getReference('apiKey')]);
        $limit->setCurrent(1);
        $limit->setLimit(1);
        $this->persistAndFlush($limit);
    }

    /**
     * @Given my key limit will be replenished
     */
    public function myKeyLimitWillBeReplenished()
    {
        $limit = $this->getApiKeyLimitRepository()->findOneBy(['api_key' => DataContext::getReference('apiKey')]);
        $date  = clone $limit->getStartTime();
        $date->modify('-'.($limit->getInterval() + 1).' second');
        $limit->setStartTime($date);

        $this->persistAndFlush($limit);
    }

    /**
     * @Given global limits are exhausted
     */
    public function globalLimitsAreExhausted()
    {
        $this->em()->getConnection()->executeUpdate(
            'REPLACE INTO settings (name, value) VALUES (:name, :value)',
            ['name' => 'api_limits.global.hour', 'value' => 10]
        );
        $limit = new ApiKeyLimit();
        $limit->setType(AbstractLimit::TYPE_GLOBAL);
        $limit->setLimit(10);
        $limit->setCurrent(0);
        $limit->setInterval(AbstractLimit::INTERVAL_HOUR);
        $this->persistAndFlush($limit);
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getApiKeyLimitRepository()
    {
        return $this->repository(ApiKeyLimit::class);
    }
}
