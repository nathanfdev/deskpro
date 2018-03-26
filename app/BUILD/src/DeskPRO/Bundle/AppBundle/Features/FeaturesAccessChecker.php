<?php

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
