<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Features;

use DeskPRO\Bundle\AppBundle\AppEnv\AppEnv;

/**
 * Class FeaturesAccessChecker.
 */
class FeaturesAccessChecker
{
    /**
     * @var AppEnv
     */
    private $appEnv;

    /**
     * Constructor.
     *
     * @param AppEnv $appEnv
     */
    public function __construct(AppEnv $appEnv)
    {
        $this->appEnv = $appEnv;
    }

    /**
     * @param array $availability
     *
     * @return bool
     */
    public function isAvailable(array $availability)
    {
        foreach ($availability as $availableAt) {
            if ($this->appEnv->isDebug()
                || $this->availableEverywhere($availableAt)
                || $this->availableAtCloud($availableAt)
                || $this->availableAtOnprem($availableAt)
                || $this->availableAtQa($availableAt)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $availableAt
     *
     * @return bool
     */
    private function availableEverywhere($availableAt)
    {
        return $availableAt === FeatureInterface::AVAILABLE_EVERYWHERE;
    }

    /**
     * @param $availableAt
     *
     * @return bool
     */
    private function availableAtCloud($availableAt)
    {
        return $availableAt === FeatureInterface::AVAILABLE_AT_CLOUD && $this->appEnv->isCloud();
    }

    /**
     * @param $availableAt
     *
     * @return bool
     */
    private function availableAtOnprem($availableAt)
    {
        return $availableAt === FeatureInterface::AVAILABLE_AT_ONPREM && !$this->appEnv->isCloud();
    }

    /**
     * @param $availableAt
     *
     * @return bool
     */
    private function availableAtQa($availableAt)
    {
        return $availableAt === FeatureInterface::AVAILABLE_AT_QA && $this->appEnv->isQa();
    }
}
