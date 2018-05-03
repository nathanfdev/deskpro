<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\CRM;

use JMS\Serializer\Annotation as JMS;

/**
 * Class CRMPermissionSettings.
 */
class CRMPermissionSettings
{
    /**
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\CRM\CRMPeoplePermissionSettings")
     *
     * @var CRMPeoplePermissionSettings
     */
    private $person;

    /**
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\CRM\CRMOrganizationPermissionSettings")
     *
     * @var CRMOrganizationPermissionSettings
     */
    private $organization;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->person       = new CRMPeoplePermissionSettings();
        $this->organization = new CRMOrganizationPermissionSettings();
    }

    /**
     * @return CRMPeoplePermissionSettings
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @return CRMOrganizationPermissionSettings
     */
    public function getOrganization()
    {
        return $this->organization;
    }
}
