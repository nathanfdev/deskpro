<?php

namespace DeskPRO\Bundle\AppBundle\Security\Permissions;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Security\Permissions\Portal\PortalPermissionsManager;

/**
 * This service should be the only entry point into accessing permissions. Use this service and get the correct
 * "permissions bag" for either the portal or the agent side, whichever you need.
 */
class PermissionsManager
{
    /**
     * @var PortalPermissionsManager
     */
    private $portal_permissions;

    /**
     * Constructor.
     *
     * @param PortalPermissionsManager $portal_permissions
     */
    public function __construct(PortalPermissionsManager $portal_permissions)
    {
        $this->portal_permissions = $portal_permissions;
    }

    /**
     * Will give you a PermissionsBag for a portal user (or guest).
     *
     * @param Person|null $person
     *
     * @return PermissionsBag
     */
    public function getPortalPermissionsBag(Person $person = null)
    {
        if (!$person || $person instanceof PersonGuest) {
            return $this->portal_permissions->getPermissionsBagForGuest();
        }

        return $this->portal_permissions->getPermissionsBagForPerson($person);
    }
}
