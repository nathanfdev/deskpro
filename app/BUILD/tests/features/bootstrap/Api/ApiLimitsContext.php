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
        $limits = $this->getApiKeyLimitRepository()->findBy(['limit_type' => AbstractLimit::TYPE_GLOBAL]);
        foreach ($limits as $limit) {
            $limit->setCurrent(0);
            $this->persistAndFlush($limit);
        }
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getApiKeyLimitRepository()
    {
        return $this->repository(ApiKeyLimit::class);
    }
}
