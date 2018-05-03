<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\CRM;

use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\AbstractAppSettings;
use JMS\Serializer\Annotation as JMS;

/**
 * Class CRMSettings.
 */
class CRMSettings extends AbstractAppSettings
{
    /**
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\CRM\CRMPermissionSettings")
     *
     * @var CRMPermissionSettings
     */
    private $permissions;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->permissions = new CRMPermissionSettings();
    }

    /**
     * @return CRMPermissionSettings
     */
    public function getPermissions()
    {
        return $this->permissions;
    }
}
