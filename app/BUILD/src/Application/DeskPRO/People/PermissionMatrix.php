<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\People;

class PermissionMatrix
{
    /**
     * @var \Application\DeskPRO\Entity\Person[]
     */
    protected $agents = [];

    /**
     * @var \Application\DeskPRO\Entity\Usergroup[]
     */
    protected $agent_groups = [];

    /**
     * @var \Application\DeskPRO\Entity\Usergroup[]
     */
    protected $user_groups = [];

    /**
     * @var array
     */
    private $agent_to_groups = [];

    /**
     * @var array
     */
    private $agentgroup_to_agents = [];

    /**
     * @var array
     */
    private $agent_perms = [];

    /**
     * @var array
     */
    private $agentgroup_perms = [];

    /**
     * @var array
     */
    private $usergroup_perms = [];

    /**
     * @param \Application\DeskPRO\Entity\Person[]    $agents
     * @param \Application\DeskPRO\Entity\Usergroup[] $groups
     */
    public function __construct($agents, $groups)
    {
        $this->agent_to_agentgroups = [];
        $this->agentgroup_to_agents = [];

        foreach ($groups as $g) {
            if ($g->is_agent_group) {
                $this->agentgroup_to_agents[$g->id] = [];
                $this->agent_groups[$g->id]         = $g;
            } else {
                $this->user_groups[$g->id] = $g;
            }
        }

        foreach ($agents as $a) {
            $this->agents[$a->id]          = $a;
            $this->agent_to_groups[$a->id] = [];

            foreach ($this->agent_groups as $g) {
                if ($a->hasUsergroup($g)) {
                    $this->agent_to_agentgroups[$a->id][$g->id] = $g->id;
                    $this->agentgroup_to_agents[$g->id][$a->id] = $a->id;
                }
            }
        }
    }

    /**
     * Set permissions from a "permission" array (eg including raw department_permissions records from the db).
     *
     * <code>
     * array('usergroup_id' => 1, 'name' => 'use', 'value' => 1),
     * array('usergroup_id' => 2, 'name' => 'use', 'value' => 1),
     * array('usergroup_id' => 3, 'name' => 'use', 'value' => 1),
     * array('person_id' => 55, 'name' => 'use', 'value' => 1),
     * </code>
     *
     * @param array|\Doctrine\Common\Collections\ArrayCollection $records
     */
    public function setPermArray($records)
    {
        $this->agent_perms      = [];
        $this->agentgroup_perms = [];
        $this->usergroup_perms  = [];

        foreach ($records as $rec) {
            if (empty($rec['value']) || !$rec['value']) {
                continue;
            }
            if (empty($rec['name']) || !$rec['name']) {
                $rec['name'] = 'use';
            }

            if (!empty($rec['usergroup_id']) && $rec['usergroup_id']) {
                if (isset($this->agent_groups[$rec['usergroup_id']])) {
                    $g = $this->agent_groups[$rec['usergroup_id']];

                    if (!isset($this->agentgroup_perms[$g->id])) {
                        $this->agentgroup_perms[$g->id] = [];
                    }
                    $this->agentgroup_perms[$g->id][$rec['name']] = 1;
                } elseif (isset($this->user_groups[$rec['usergroup_id']])) {
                    $g = $this->user_groups[$rec['usergroup_id']];

                    if (!isset($this->usergroup_perms[$g->id])) {
                        $this->usergroup_perms[$g->id] = [];
                    }
                    $this->usergroup_perms[$g->id][$rec['name']] = 1;
                }
            } elseif (!empty($rec['person_id']) && $rec['person_id'] && isset($this->agents[$rec['person_id']])) {
                if (!isset($this->agent_perms[$rec['person_id']])) {
                    $this->agent_perms[$rec['person_id']] = [];
                }

                $this->agent_perms[$rec['person_id']][$rec['name']] = 1;
            }
        }
    }

    /**
     * Set permission from an array of permission objects (e.g., DepartmentPermission).
     *
     * @param $records
     */
    public function setPermRecords($records)
    {
        $array = [];

        foreach ($records as $rec) {
            $array[] = [
                'usergroup_id' => !empty($rec->usergroup) ? $rec->usergroup->id : null,
                'person_id'    => !empty($rec->person) ? $rec->person->id : null,
                'name'         => $rec->name,
                'value'        => $rec->value,
            ];
        }
    }

    /**
     * Re-writes the set permissions into a reduced set of permissions based on
     * agent group membership.
     *
     * Returns an array:
     *
     * <code>
     * array('usergroup_id' => 1, 'name' => 'use', 'value' => 1),
     * array('usergroup_id' => 2, 'name' => 'use', 'value' => 1),
     * array('usergroup_id' => 3, 'name' => 'use', 'value' => 1),
     * array('person_id' => 55, 'name' => 'use', 'value' => 1),
     * </code>
     *
     * @return array
     */
    public function getPermsArray()
    {
        $set_perms = [];

        foreach ($this->agentgroup_perms as $ugid => $perms) {
            foreach ($perms as $perm_name => $perm_value) {
                $set_perms[] = ['usergroup_id' => $ugid, 'name' => $perm_name, 'value' => 1];
            }
        }
        foreach ($this->usergroup_perms as $ugid => $perms) {
            foreach ($perms as $perm_name => $perm_value) {
                $set_perms[] = ['usergroup_id' => $ugid, 'name' => $perm_name, 'value' => 1];
            }
        }

        foreach ($this->agent_perms as $aid => $perms) {
            foreach ($perms as $perm_name => $perm_value) {
                $has = false;
                if (isset($this->agent_to_agentgroups[$aid])) {
                    foreach ($this->agent_to_agentgroups[$aid] as $ugid) {
                        if (isset($this->agentgroup_perms[$ugid][$perm_name])) {
                            $has = true;
                            break;
                        }
                    }
                }

                if (!$has) {
                    $set_perms[] = ['person_id' => $aid, 'name' => $perm_name, 'value' => 1];
                }
            }
        }

        return $set_perms;
    }
}
