<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App;

use JMS\Serializer\Annotation as JMS;

/**
 * Class ChatSettings.
 */
class ChatSettings extends AbstractAppSettings
{
    /**
     * @var ChatPermissionSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\ChatPermissionSettings")
     */
    private $permissions;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->permissions = new ChatPermissionSettings();
    }

    /**
     * @return ChatPermissionSettings
     */
    public function getPermissions()
    {
        return $this->permissions;
    }
}
