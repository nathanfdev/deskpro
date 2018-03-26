<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\People\PermissionLoader;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\Permission;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Groups\PermissionsLoader;

/**
 * Loads general usergroup permissions likes flags and the like.
 */
class Usergroups extends AbstractLoader implements \Application\DeskPRO\People\PersonContextInterface
{
    /**
     * @var array
     */
    protected $perms = null;

    /**
     * @var array
     */
    protected $dynamic_perms = null;

    /**
     * @var Person
     */
    protected $person;

    /**
     * @var int
     */
    protected $person_id = 0;

    /**
     * @var bool
     */
    protected $with_overrides = false;

    /**
     * @param Person $person
     */
    public function setPersonContext(Person $person)
    {
        $this->person    = $person;
        $this->person_id = $person->id;
    }

    public function getSubkey()
    {
        if ($this->person && $this->person->isAgent()) {
            return 'person-'.$this->person->getId();
        }
    }

    /**
     * Get a permission value.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getPermission($name)
    {
        $this->getAllPermissions();

        switch ($name) {
            case 'core.tickets_submit_check':
                if ($this->getPermission('tickets.use')) {
                    return true;
                } else {
                    return false;
                }
                break;

            case 'core.feedback_submit_check':
                if ($this->getPermission('feedback.use')) {
                    return true;
                } else {
                    return false;
                }
                break;
        }

        $val = false;
        if (isset($this->dynamic_perms[$name]) && $this->dynamic_perms[$name]) {
            $val = true;
        } elseif (isset($this->perms[$name]) && $this->perms[$name]) {
            $val = true;
        }

        return $val;
    }

    /**
     * Get an array of all effective permissions.
     *
     * @return array
     */
    public function getAllPermissions()
    {
        if ($this->perms === null) {
            if (!$this->usergroup_ids && !$this->person_id) {
                $this->perms = [];
            } else {
                /** @var PermissionsLoader $permissionLoader */
                $permissionLoader = App::getSystemService('PermissionsLoader');

                // calc effective permissions for usergroups
                $this->perms = $permissionLoader->getEffectivePermissionsForUsergroups($this->usergroup_ids);

                // calc effective permissions for overrides
                if ($this->person_id) {
                    if ($this->person && $this->person->isAgent()) {
                        $overrides = $permissionLoader->getAgentOverridePermissions($this->person_id);
                        if ($overrides) {
                            $this->with_overrides = true;
                            $this->perms          = Permission::getEffectivePermissions($overrides, $this->perms);
                        }
                    }
                }
            }
        }

        if ($this->dynamic_perms === null) {
            $this->dynamic_perms = [];
            $agent_groups        = App::$container->getAgentGroups();
            foreach ($this->usergroup_ids as $ugid) {
                if ($agent_groups->groupExists($ugid)) {
                    $g = $agent_groups->getGroup($ugid);
                    if ($set_perms = self::loadDynamicPerms($g)) {
                        foreach ($set_perms as $n) {
                            $this->dynamic_perms[$n] = true;
                        }
                    }
                }
            }
        }

        return $this->perms;
    }

    /**
     * Get an array of data we'll serialize.
     *
     * @return array
     */
    protected function serializeData()
    {
        return ['perms' => $this->perms];
    }

    /**
     * Initialize this object with an array of saved data.
     *
     * @param array $data
     */
    protected function unserializeData(array $data)
    {
        $this->perms = $data['perms'];
    }

    /**
     * @param Entity\Usergroup $usergroup
     *
     * @return null|array
     */
    protected static function loadDynamicPerms(Entity\Usergroup $usergroup)
    {
        static $set_perms_by_group = [];

        $sysName = $usergroup->getSysName();
        if (!$sysName) {
            return;
        }

        if (isset($set_perms_by_group[$sysName])) {
            return $set_perms_by_group[$sysName];
        }

        $set_perms = [];
        if ($sysName == 'agent_all_perms' || $sysName == 'agent_all_safe_perms') {
            $loader = App::$container->getSystemService('AgentPermissionNamesLoader');
            if ($sysName == 'agent_all_perms') {
                $set_perms = $loader->getNames();
            } else {
                $set_perms = $loader->getSafeNames();
            }
        }

        $set_perms_by_group[$sysName] = $set_perms;

        return $set_perms_by_group[$sysName];
    }
}
