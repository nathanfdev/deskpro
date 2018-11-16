<?php

namespace DeskPRO\Bundle\AppBundle\Features;

/**
 * Class MessengerFeature.
 */
class MessengerFeature extends AbstractBetaFeature
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'messenger';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Messenger widget v2';
    }

    /**
     * {@inheritdoc}
     */
    public function getShortDescription()
    {
        return 'Improved widget messenger for customers.';
    }

    /**
     * {@inheritdoc}
     */
    public function getEnableDescription()
    {
        return <<<'HTML'
Enabling Messenger v2 will switch on Messenger API on your helpdesk.

HTML;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisableDescription()
    {
        return <<<'HTML'
Disabling Messenger v2 will switch off Messenger API on your helpdesk.
HTML;
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
    public function isEnabledOnInstall()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function needAgentReload()
    {
        return false;
    }
}
