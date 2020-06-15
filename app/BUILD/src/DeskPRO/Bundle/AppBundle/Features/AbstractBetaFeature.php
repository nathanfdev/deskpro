<?php

namespace DeskPRO\Bundle\AppBundle\Features;

use Application\DeskPRO\NewSettings\SettingsResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AbstractFeature.
 */
abstract class AbstractBetaFeature implements BetaFeatureInterface
{
    /**
     * @var SettingsResolver
     */
    protected $settingsResolver;

    /**
     * AbstractFeature constructor.
     *
     * @param SettingsResolver $settingsResolver
     */
    public function __construct(SettingsResolver $settingsResolver)
    {
        $this->settingsResolver = $settingsResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeEnable(ContainerInterface $container, $newInstall = false)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function beforeDisable(ContainerInterface $container)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getExtraInfoContent(ContainerInterface $container)
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        $key = sprintf('%s.%s', BetaFeatureInterface::BETA_FEATURES_KEY, $this->getId());

        return $this->settingsResolver->getGlobalSettings()->getBool($key, false);
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabledOnInstall()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function canBeDisabled()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getDueDate()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getDateReleased()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutePath()
    {
    }
}
