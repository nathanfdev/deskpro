<?php

namespace DeskPRO\Bundle\AppBundle\Features;

class ContentEditor extends AbstractBetaFeature
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'content_editor';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Improved content editor';
    }

    /**
     * {@inheritdoc}
     */
    public function getShortDescription()
    {
        return 'Improved content editor in the agent interface';
    }

    /**
     * {@inheritdoc}
     */
    public function getEnableDescription()
    {
        return 'The new content editor allows better experience when editing content.';
    }

    /**
     * {@inheritdoc}
     */
    public function getDisableDescription()
    {
        return 'Disabling this feature will return the agent interface to using the previous content editor.';
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
