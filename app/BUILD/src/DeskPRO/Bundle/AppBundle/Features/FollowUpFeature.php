<?php

namespace DeskPRO\Bundle\AppBundle\Features;

class FollowUpFeature extends AbstractBetaFeature
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'follow_up';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Ticket Follow Up';
    }

    /**
     * {@inheritdoc}
     */
    public function getShortDescription()
    {
        return 'Allow to plan an action in the future on a ticket';
    }

    /**
     * {@inheritdoc}
     */
    public function getEnableDescription()
    {
        return <<<'HTML'
The follow up feature adds a "Follow Ups" tab to the ticket reply box. You can add action in the future and see later if they have been performed.
HTML;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisableDescription()
    {
        return <<<'HTML'
Disabling this feature will remove the Follow Ups tabs and disable any action planned in the future. 
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
