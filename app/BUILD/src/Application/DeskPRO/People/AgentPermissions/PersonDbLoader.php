<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\AgentPermissions;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;

class PersonDbLoader
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    private $person;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;

    /**
     * @var array
     */
    private $perms;

    /**
     * @param Person        $person
     * @param EntityManager $em
     */
    public function __construct(Person $person, EntityManager $em)
    {
        $this->person = $person;
        $this->em     = $em;
        $this->db     = App::getDbRead('perms');
    }

    /**
     * @return array
     */
    private function getPermissions()
    {
        if ($this->perms !== null) {
            return $this->perms;
        }

        $has_all_perms      = false;
        $has_all_safe_perms = false;

        $agent_group_ids = [];
        foreach ($this->person->usergroups as $ug) {
            if ($ug->is_agent_group) {
                $agent_group_ids[] = $ug->id;

                if ($ug->sys_name) {
                    if ($ug->sys_name == 'agent_all_perms') {
                        $has_all_perms = $ug->id;
                    } elseif ($ug->sys_name == 'agent_all_safe_perms') {
                        $has_all_safe_perms = $ug->id;
                    }
                }
            }
        }

        if (!$agent_group_ids) {
            $agent_group_ids[] = 0;
        }

        $perm_recs = $this->db->fetchAll('
            SELECT name, usergroup_id, person_id
            FROM permissions
            WHERE (usergroup_id IN (?) OR (person_id = ?))
                AND value = 1 AND is_active = 1
        ', [$agent_group_ids, $this->person['id']], [Connection::PARAM_INT_ARRAY, \PDO::PARAM_INT]);

        if ($has_all_perms || $has_all_safe_perms) {
            $names_loader = new PermissionNamesLoader(); //TODO inject

            if ($has_all_perms) {
                $add      = $names_loader->getNames();
                $add_ugid = $has_all_perms;
            } else {
                $add      = $names_loader->getSafeNames();
                $add_ugid = $has_all_safe_perms;
            }

            foreach ($add as $n) {
                $perm_recs[] = [
                    'name'         => $n,
                    'usergroup_id' => $add_ugid,
                    'person_id'    => null,
                ];
            }
        }

        $this->perms = [
            'effective' => [],
            'group'     => [],
            'person'    => [],
        ];

        foreach ($perm_recs as $rec) {
            $this->perms['effective'][$rec['name']] = true;

            if ($rec['usergroup_id']) {
                $this->perms['group'][$rec['name']] = true;
            } else {
                $this->perms['person'][$rec['name']] = true;
            }
        }

        return $this->perms;
    }

    /**
     * Get effective permissions (group and overrides combined).
     *
     * @return AgentPermissions
     */
    public function getEffectivePermissions()
    {
        $perms = $this->getPermissions();

        return $this->createAgentPermissions($perms['effective']);
    }

    /**
     * Get permissions defined just through overrides.
     *
     * @return AgentPermissions
     */
    public function getOverridePermissions()
    {
        $perms = $this->getPermissions();

        return $this->createAgentPermissions($perms['person']);
    }

    /**
     * Get just group permissions (no overrides).
     *
     * @return AgentPermissions
     */
    public function getGroupPermissions()
    {
        $perms = $this->getPermissions();

        return $this->createAgentPermissions($perms['group']);
    }

    /**
     * @param array $perm_array
     *
     * @return AgentPermissions
     */
    private function createAgentPermissions(array $perm_array)
    {
        $agent_perms = new AgentPermissions();

        foreach ($perm_array as $k => $v) {
            if (!$v) {
                continue;
            } // disabled
            if (strpos($k, '.') === false) {
                continue;
            } // invalid

            list($type, $name) = explode('.', $k, 2);
            if (!isset(AgentPermissions::$prefix_map[$type])) {
                continue;
            } // unknown type

            $obj_name = AgentPermissions::$prefix_map[$type];
            $obj      = $agent_perms->$obj_name;
            if (!isset($obj->$name)) {
                continue;
            } // invalid;

            $obj->$name = true;
        }

        return $agent_perms;
    }
}
