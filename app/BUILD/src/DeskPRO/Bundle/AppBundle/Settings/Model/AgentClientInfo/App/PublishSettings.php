<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App;

use JMS\Serializer\Annotation as JMS;

/**
 * Class PublishSettings.
 */
class PublishSettings extends AbstractAppSettings
{
    /**
     * @var PublishPermissionSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\PublishPermissionSettings")
     */
    private $permissions;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->permissions = new PublishPermissionSettings();
    }

    /**
     * @return PublishPermissionSettings
     */
    public function getPermissions()
    {
        return $this->permissions;
    }
}
