<?php

namespace DeskPRO\Bundle\AppBundle\Features;

/**
 * Class VoiceFeature.
 */
class VoiceFeature extends AbstractBetaFeature
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'voice';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Voice (Beta)';
    }

    /**
     * {@inheritdoc}
     */
    public function getShortDescription()
    {
        return 'Make and accept voice calls from Deskpro';
    }

    /**
     * {@inheritdoc}
     */
    public function getEnableDescription()
    {
        return 'Enable voice feature. Please note that this is currently a BETA feature and should only be used for testing.';
    }

    /**
     * {@inheritdoc}
     */
    public function getDisableDescription()
    {
        return 'Disable voice feature.';
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailability()
    {
        return [self::AVAILABLE_AT_QA, self::AVAILABLE_AT_CLOUD];
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
        return '/voice_channel/accounts';
    }
}
