<?php

namespace DeskPRO\Bundle\AppBundle\Features;

/**
 * Class VoiceFeature.
 */
class GuidesFeature extends AbstractBetaFeature
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'guides';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Guides';
    }

    /**
     * {@inheritdoc}
     */
    public function getShortDescription()
    {
        return 'New Generation manuals.';
    }

    /**
     * {@inheritdoc}
     */
    public function getEnableDescription()
    {
        return 'Enable access to guides.';
    }

    /**
     * {@inheritdoc}
     */
    public function getDisableDescription()
    {
        return 'Disable access to guides.';
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
}
