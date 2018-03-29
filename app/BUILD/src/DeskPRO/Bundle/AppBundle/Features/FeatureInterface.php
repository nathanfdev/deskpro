<?php

namespace DeskPRO\Bundle\AppBundle\Features;

/**
 * Interface FeatureInterface.
 */
interface FeatureInterface
{
    const AVAILABLE_AT_CLOUD   = 'cloud';
    const AVAILABLE_AT_ONPREM  = 'onpremise';
    const AVAILABLE_AT_QA      = 'qa';
    const AVAILABLE_EVERYWHERE = 'all';

    /**
     * @return array
     */
    public function getAvailability();
}
