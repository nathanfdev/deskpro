<?php

namespace DeskPRO\Bundle\AppBundle\Features;

class NewSearchFeature extends AbstractBetaFeature
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'new_search';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'New Search';
    }

    /**
     * {@inheritdoc}
     */
    public function getShortDescription()
    {
        return 'Improved searching in the agent interface';
    }

    /**
     * {@inheritdoc}
     */
    public function getEnableDescription()
    {
        return 'The new searching system improves searching features with a token based search and a new result tab.';
    }

    /**
     * {@inheritdoc}
     */
    public function getDisableDescription()
    {
        return 'Disabling this feature will return the agent interface to using the previous searching system.';
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
    public function getAvailability()
    {
        return [self::AVAILABLE_AT_QA];
    }
}
