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
        return 'Voice';
    }

    /**
     * {@inheritdoc}
     */
    public function getShortDescription()
    {
        return 'Voice integration.';
    }

    /**
     * {@inheritdoc}
     */
    public function getEnableDescription()
    {
        return 'Enable voice feature.';
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
        return [self::AVAILABLE_AT_QA];
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
