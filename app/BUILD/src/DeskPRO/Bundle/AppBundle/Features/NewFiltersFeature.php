<?php

namespace DeskPRO\Bundle\AppBundle\Features;

class NewFiltersFeature extends AbstractBetaFeature
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'new_filters';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'New Filters';
    }

    /**
     * {@inheritdoc}
     */
    public function getShortDescription()
    {
        return 'Improved filtering in the agent interface';
    }

    /**
     * {@inheritdoc}
     */
    public function getEnableDescription()
    {
        return 'The new filtering system improves filtering features (such as filter sharing, filter sets) and improves accuracy and real-time functions.';
    }

    /**
     * {@inheritdoc}
     */
    public function getDisableDescription()
    {
        return 'Disabling this feature will return the agent interface to using the previous filtering system.';
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
