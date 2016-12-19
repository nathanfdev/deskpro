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
