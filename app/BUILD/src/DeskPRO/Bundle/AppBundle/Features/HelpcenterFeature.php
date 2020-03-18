<?php

namespace DeskPRO\Bundle\AppBundle\Features;

class HelpcenterFeature extends AbstractBetaFeature
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'helpcenter';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Helpcenter theme';
    }

    /**
     * {@inheritdoc}
     */
    public function getShortDescription()
    {
        return 'Modern looking and improved theme for the portal';
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
    public function getEnableDescription()
    {
        return <<<'HTML'
Enable Helpcenter theme<br /><br />

!This theme should not be enabled on production as it is still under development!

This will allow you to select the helpcenter theme in the admin interface.
HTML;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisableDescription()
    {
        return <<<'HTML'
Disable helpcenter theme.
HTML;
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
    public function getDateReleased()
    {
        return false;
    }
}
