<?php

namespace DeskPRO\Bundle\AppBundle\Features;

class NewForwardFeature extends AbstractBetaFeature
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'new_fwd';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Improved Forwarding';
    }

    /**
     * {@inheritdoc}
     */
    public function getShortDescription()
    {
        return 'Improved ticket forwarding from the agent interface';
    }

    /**
     * {@inheritdoc}
     */
    public function getEnableDescription()
    {
        return <<<'HTML'
The new forwarding feature adds a "Forward" tab to the ticket reply box. You can now forward single or multiple messages,
and it's easier to preview what the recipient will see.
HTML;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisableDescription()
    {
        return <<<'HTML'
Disabling this feature will return the helpdesk to using the old forwarding interface. 
HTML;
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
        return [self::AVAILABLE_EVERYWHERE];
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabledOnInstall()
    {
        return true;
    }
}
