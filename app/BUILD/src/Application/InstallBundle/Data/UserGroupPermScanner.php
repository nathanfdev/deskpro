<?php

/**
 * DeskPRO.
 */

namespace Application\InstallBundle\Data;

use Application\DeskPRO\People\UserPermissions\UserPermissions;

class UserGroupPermScanner
{
    /**
     * @var array
     */
    protected $perm_names = null;

    protected function load()
    {
        if ($this->perm_names !== null) {
            return;
        }

        $perms = new UserPermissions();

        $set_perms = [];
        foreach (UserPermissions::$prefix_map as $real_name => $coll_name) {
            $obj = $perms->$coll_name;
            foreach ($obj->getNames() as $prop) {
                $set_perms[] = $real_name.'.'.$prop;
            }
        }

        $this->perm_names = $set_perms;
    }

    /**
     * Get the names of all the permissions.
     *
     * @return array
     */
    public function getNames()
    {
        $this->load();

        return $this->perm_names;
    }
}
