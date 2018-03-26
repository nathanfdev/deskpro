<?php

/**
 * DeskPRO.
 */

namespace Application\InstallBundle\Data;

use Application\DeskPRO\People\AgentPermissions\AgentPermissions;

/**
 * Scans the permtable template to extract the names of permissions so we can dynamically create
 * the "all" permission group.
 */
class AgentGroupPermScanner
{
    /**
     * @var array
     */
    protected $perm_names = null;

    /**
     * @var array
     */
    protected $perm_safe_names = null;

    protected function load()
    {
        if ($this->perm_names !== null) {
            return;
        }

        $perms = new AgentPermissions();

        $unsafe = [];

        $set_perms = [];
        foreach (AgentPermissions::$prefix_map as $real_name => $coll_name) {
            $obj = $perms->$coll_name;
            foreach ($obj->getNames() as $prop) {
                $set_perms[] = $real_name.'.'.$prop;
            }
            foreach ($obj->getDestructiveNames() as $prop) {
                $unsafe[] = $real_name.'.'.$prop;
            }
        }

        $this->perm_names      = $set_perms;
        $this->perm_safe_names = array_values(array_diff($this->perm_names, $unsafe));
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

    /**
     * @return array
     */
    public function getSafeNames()
    {
        $this->load();

        return $this->perm_safe_names;
    }
}
