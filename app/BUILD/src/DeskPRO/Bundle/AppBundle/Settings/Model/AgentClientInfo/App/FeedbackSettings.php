<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App;

use JMS\Serializer\Annotation as JMS;

/**
 * Class FeedbackSettings.
 */
class FeedbackSettings extends AbstractAppSettings
{
    /**
     * @var FeedbackPermissionSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\FeedbackPermissionSettings")
     */
    private $permissions;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->permissions = new FeedbackPermissionSettings();
    }

    /**
     * @return FeedbackPermissionSettings
     */
    public function getPermissions()
    {
        return $this->permissions;
    }
}
