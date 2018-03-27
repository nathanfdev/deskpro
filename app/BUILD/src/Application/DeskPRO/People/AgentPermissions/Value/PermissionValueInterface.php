<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\AgentPermissions\Value;

interface PermissionValueInterface extends \Application\DeskPRO\People\UserPermissions\Value\PermissionValueInterface
{
    /**
     * @return array
     */
    public function getDestructiveNames();
}
