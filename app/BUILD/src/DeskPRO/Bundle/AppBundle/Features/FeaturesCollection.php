<?php

namespace DeskPRO\Bundle\AppBundle\Features;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Component\Util\AbstractCollection;

/**
 * Class FeaturesCollection.
 */
class FeaturesCollection extends AbstractCollection
{
    /**
     * @var FeaturesAccessChecker
     */
    private $accessChecker;

    /**
     * @var SettingsResolver
     */
    private $settingsResolver;

    /**
     * Constructor.
     *
     * @param FeaturesAccessChecker $accessChecker
     */
    public function __construct(FeaturesAccessChecker $accessChecker, SettingsResolver $settingsResolver)
    {
        $this->accessChecker    = $accessChecker;
        $this->settingsResolver = $settingsResolver;
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
    public function hasFeature($feature)
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
     * Feature released before installation and enabled by default should not be editable (turned off)
     *
     * @return mixed
     */
    public function getAvailableAndEditableFeatures()
    {
        $features = $this->getAvailableFeatures();

        $this->installTimestamp = $this->settingsResolver->getGlobalSettings()->get('core.install_timestamp');
        if (!$this->installTimestamp) {
            return $features;
        }

        return array_filter($features, [$this, 'filterEditable']);
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
        return $this->accessChecker->isAvailable($feature->getAvailability());
    }

    /**
     * @param BetaFeatureInterface $feature
     *
     * @return bool
     */
    private function filterEditable(BetaFeatureInterface $feature)
    {
        if (!$this->installTimestamp) {
            $this->installTimestamp = $this->settingsResolver->getGlobalSettings()->get('core.install_timestamp');
        }

        return !($feature->isEnabled()
                && $this->installTimestamp
                && $feature->getDateReleased()
                && $feature->getDateReleased()->getTimestamp() < $this->installTimestamp);
    }
}
