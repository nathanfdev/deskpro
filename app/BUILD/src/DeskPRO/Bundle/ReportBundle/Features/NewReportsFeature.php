<?php

namespace DeskPRO\Bundle\ReportBundle\Features;

use DeskPRO\Bundle\AppBundle\Features\AbstractBetaFeature;

class NewReportsFeature extends AbstractBetaFeature
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'new_reports';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'New Reports';
    }

    /**
     * {@inheritdoc}
     */
    public function getShortDescription()
    {
        return 'Improved reports interface.';
    }

    /**
     * {@inheritdoc}
     */
    public function getEnableDescription()
    {
        return <<<'HTML'
The new reporting interface introduces dashboards, PDF downloads, and scheduled reports. Enabling the beta will show
two Reports Interface icons in the app bar. You can continue to use the old Reports Interface while you test the new one.
HTML;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisableDescription()
    {
        return <<<'HTML'
Disabling the new reporting system will hide the icon from your app bar.
HTML;
    }

    public function isEnabledOnInstall()
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

    /**
     * {@inheritdoc}
     */
    public function needAgentReload()
    {
        return true;
    }
}
