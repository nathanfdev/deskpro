<?php

namespace DpTestSrc\TestBundle\Mock\Features;

use DeskPRO\Bundle\AppBundle\Features\BetaFeatureInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EnabledFeature.
 */
class EnabledFeature implements BetaFeatureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'enabled_feature';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'This feature is enabled forever';
    }

    /**
     * {@inheritdoc}
     */
    public function getShortDescription()
    {
        return 'This feature is enabled forever';
    }

    /**
     * {@inheritdoc}
     */
    public function getEnableDescription()
    {
        return 'This feature is enabled forever';
    }

    /**
     * {@inheritdoc}
     */
    public function getDisableDescription()
    {
        return 'This feature is enabled forever';
    }

    /**
     * {@inheritdoc}
     */
    public function beforeEnable(ContainerInterface $container)
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeDisable(ContainerInterface $container)
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return true;
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
    public function getAvailability()
    {
        return [self::AVAILABLE_EVERYWHERE];
    }

    /**
     * {@inheritdoc}
     */
    public function needAgentReload()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutePath()
    {
    }
}
