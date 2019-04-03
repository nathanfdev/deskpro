<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\UserPermissions;

use Application\DeskPRO\DBAL\Connection;
use Doctrine\ORM\EntityManager;

class GroupsDbLoader
{
    /**
     * @var int[]
     */
    private $group_ids;

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
    private $group_perms;

    /**
     * @param int[]         $groups Group IDs or Usergroup objects
     * @param EntityManager $em
     */
    public function __construct(array $groups, EntityManager $em)
    {
        $this->group_ids = [];

        foreach ($groups as $g) {
            if (is_object($g)) {
                $this->group_ids[] = $g->id;
            } elseif (is_int($g) || ctype_digit($g)) {
                $this->group_ids[] = (int) $g;
            }
        }

        $this->em = $em;
        $this->db = $em->getConnection();
    }

    /**
     * @param int $group_id
     *
     * @return array
     */
    private function getPermissions($group_id)
    {
        if ($this->group_perms !== null) {
            return isset($this->group_perms[$group_id]) ? $this->group_perms[$group_id] : [];
        }

        if (!$this->group_ids) {
            $this->group_perms = [];

            return [];
        }

        $perm_recs = $this->db->fetchAll('
            SELECT name, usergroup_id, value
            FROM permissions
            WHERE usergroup_id IN (?)
        ', [$this->group_ids], [Connection::PARAM_INT_ARRAY]);

        $this->group_perms = [];

        foreach ($perm_recs as $rec) {
            // ignore false values (false - means no permissions)
            if (!$rec['value']) {
                continue;
            }
            if (!isset($this->group_perms[$rec['usergroup_id']])) {
                $this->group_perms[$rec['usergroup_id']] = [];
            }

            $this->group_perms[$rec['usergroup_id']][$rec['name']] = $rec['value'];
        }

        return isset($this->group_perms[$group_id]) ? $this->group_perms[$group_id] : [];
    }

    /**
     * @param $group_id
     *
     * @return UserPermissions
     */
    public function getGroupPermissions($group_id)
    {
        $perms = $this->getPermissions($group_id);

        return $this->createUserPermissions($perms);
    }

    /**
     * @param array $perm_array
     *
     * @return UserPermissions
     */
    private function createUserPermissions(array $perm_array)
    {
        $normalized = [];
        foreach ($perm_array as $key => $value) {
            list($type, $name) = explode('.', $key, 2);
            if (!$value || !$type || !$name || !isset(UserPermissions::$prefix_map[$type])) {
                continue;
            }
            $type = UserPermissions::$prefix_map[$type];
            if (!isset($normalized[$type])) {
                $normalized[$type] = [];
            }
            $normalized[$type][$name] = $value;
        }

        $user_perms = new UserPermissions();
        $user_perms->fromArray($normalized);

        return $user_perms;
    }
}
