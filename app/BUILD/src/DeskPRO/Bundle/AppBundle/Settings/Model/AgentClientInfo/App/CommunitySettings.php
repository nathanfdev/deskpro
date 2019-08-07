<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App;

use JMS\Serializer\Annotation as JMS;

/**
 * Class CommunitySettings.
 */
class CommunitySettings extends AbstractAppSettings
{
    /**
     * @var CommunityPermissionSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\CommunityPermissionSettings")
     */
    private $permissions;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->permissions = new CommunityPermissionSettings();
    }

    /**
     * @return CommunityPermissionSettings
     */
    public function getPermissions()
    {
        return $this->permissions;
    }
}
