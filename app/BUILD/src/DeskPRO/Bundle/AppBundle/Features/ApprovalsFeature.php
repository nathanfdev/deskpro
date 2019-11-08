<?php

namespace DeskPRO\Bundle\AppBundle\Features;

/**
 * Class ApprovalsFeature
 */
class ApprovalsFeature extends AbstractBetaFeature
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'approvals';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Ticket Approvals';
    }

    /**
     * {@inheritdoc}
     */
    public function getShortDescription()
    {
        return 'Ticket approvals';
    }

    /**
     * {@inheritdoc}
     */
    public function getEnableDescription()
    {
        return 'Approvals can be used to empower agents by letting them manually generate requests.';
    }

    /**
     * {@inheritdoc}
     */
    public function getDisableDescription()
    {
        return 'Disabling this feature will remove the ability to approve or reject requests';
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
