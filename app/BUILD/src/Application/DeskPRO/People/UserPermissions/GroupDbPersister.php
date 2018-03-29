<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\People\UserPermissions;

use Application\DeskPRO\People\AbstractGroupDbPersister;

class GroupDbPersister extends AbstractGroupDbPersister
{
    protected function getPermissionsProperties()
    {
        return UserPermissions::$prefix_map;
    }
}
