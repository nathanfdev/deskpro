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

use DeskPRO\Component\Util\AbstractCollection;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class FeaturesCollection.
 */
class FeaturesCollection extends AbstractCollection
{
    /**
     * @var bool
     */
    private $debug;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var bool
     */
    private $isCloud;

    /**
     * FeaturesCollection constructor.
     *
     * @param bool         $debug
     * @param RequestStack $requestStack
     * @param bool         $isCloud
     */
    public function __construct($debug, RequestStack $requestStack = null, $isCloud = false)
    {
        $this->debug        = $debug;
        $this->isCloud      = $isCloud;
        $this->requestStack = $requestStack;
    }

    /**
     * @param BetaFeatureInterface $feature
     *
     * @return $this
     */
    public function addFeature(BetaFeatureInterface $feature)
    {
        if (!$this->hasFeature($feature)) {
            $this->collection[$feature->getId()] = $feature;
        }

        return $this;
    }

    /**
     * @param BetaFeatureInterface|string $feature
     *
     * @return bool
     */
    protected function hasFeature($feature)
    {
        return array_key_exists(
            $feature instanceof BetaFeatureInterface
                ? $feature->getId()
                : (string) $feature,
            $this->collection);
    }

    /**
     * @return mixed
     */
    public function getAvailableFeatures()
    {
        return array_filter($this->collection, [$this, 'filterAvailable']);
    }

    /**
     * @param $feature
     *
     * @return BetaFeatureInterface|null
     */
    public function getFeature($feature)
    {
        return $this->hasFeature($feature) ? $this->collection[(string) $feature] : null;
    }

    /**
     * @param string $feature
     *
     * @return bool
     */
    public function isFeatureEnabled($feature)
    {
        if (!$this->hasFeature($feature)) {
            return false;
        }

        return $this->getFeature($feature)->isEnabled();
    }

    /**
     * @param BetaFeatureInterface $feature
     *
     * @return bool
     */
    private function filterAvailable(BetaFeatureInterface $feature)
    {
        foreach ($feature->getAvailability() as $availableAt) {
            if ($this->availableEverywhere($availableAt)
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
        return $availableAt === BetaFeatureInterface::AVAILABLE_EVERYWHERE;
    }

    /**
     * @param $availableAt
     *
     * @return bool
     */
    private function availableAtCloud($availableAt)
    {
        return $availableAt === BetaFeatureInterface::AVAILABLE_AT_CLOUD && $this->isCloud;
    }

    /**
     * @param $availableAt
     *
     * @return bool
     */
    private function availableAtOnprem($availableAt)
    {
        return $availableAt === BetaFeatureInterface::AVAILABLE_AT_ONPREM && !$this->isCloud;
    }

    /**
     * @param $availableAt
     *
     * @return bool
     */
    private function availableAtQa($availableAt)
    {
        return
            $availableAt === BetaFeatureInterface::AVAILABLE_AT_QA
            && ($this->debug
                 || ($this->requestStack && $this->requestStack->getMasterRequest()
                      && strpos($this->requestStack->getMasterRequest()->getHost(), 'deskprodemo.com') !== false
                    )
                );
    }
}
