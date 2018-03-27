<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\App;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\Person;

class AgentAppPermissions
{
    /**
     * @var int[]
     */
    private $app_to_usergroups;

    /**
     * @var int[]
     */
    private $app_to_people;

    /**
     * @param Connection                                $db
     * @param \Application\DeskPRO\Entity\AppInstance[] $apps
     *
     * @return AgentAppPermissions
     */
    public static function newFromDb(Connection $db, array $apps)
    {
        $app_ids = [];
        foreach ($apps as $a) {
            if ($a->perm_type == 'set') {
                $app_ids[] = $a->id;
            }
        }

        if ($app_ids) {
            $perms = $db->fetchAll('SELECT * FROM app_instance_permissions WHERE app_instance_id IN (?)', [$app_ids], [Connection::PARAM_INT_ARRAY]);
        } else {
            $perms = [];
        }

        $app_to_usergroups = [];
        $app_to_people     = [];

        foreach ($perms as $p) {
            if ($p['usergroup_id']) {
                if (!isset($app_to_usergroups[$p['app_instance_id']])) {
                    $app_to_usergroups[$p['app_instance_id']] = [];
                }

                $app_to_usergroups[$p['app_instance_id']][$p['usergroup_id']] = true;
            } elseif ($p['person_id']) {
                if (!isset($app_to_people[$p['app_instance_id']])) {
                    $app_to_people[$p['app_instance_id']] = [];
                }

                $app_to_people[$p['app_instance_id']][$p['person_id']] = true;
            }
        }

        return new self($app_to_usergroups, $app_to_people);
    }

    /**
     * @param array $app_to_usergroups
     * @param array $app_to_people
     */
    public function __construct(array $app_to_usergroups, array $app_to_people)
    {
        $this->app_to_usergroups = $app_to_usergroups;
        $this->app_to_people     = $app_to_people;
    }

    /**
     * @param \Application\DeskPRO\Entity\AppInstance|int $app_or_id
     * @param Person|int                                  $person_or_id
     *
     * @return bool
     */
    public function isPersonSet($app_or_id, $person_or_id)
    {
        if (is_object($app_or_id)) {
            $app_id = $app_or_id->id;
        } else {
            $app_id = $app_or_id;
        }

        if (is_object($person_or_id)) {
            $person_id = $person_or_id->id;
        } else {
            $person_id = $person_or_id;
        }

        return isset($this->app_to_people[$app_id][$person_id]);
    }

    /**
     * @param \Application\DeskPRO\Entity\AppInstance|int $app_or_id
     * @param \Application\DeskPRO\Entity\Usergroup|int   $group_or_id
     *
     * @return bool
     */
    public function isUsergroupSet($app_or_id, $group_or_id)
    {
        if (is_object($app_or_id)) {
            $app_id = $app_or_id->id;
        } else {
            $app_id = $app_or_id;
        }

        if (is_object($group_or_id)) {
            $person_id = $group_or_id->id;
        } else {
            $person_id = $group_or_id;
        }

        return isset($this->app_to_usergroups[$app_id][$person_id]);
    }

    /**
     * @param \Application\DeskPRO\Entity\AppInstance|int $app_or_id
     * @param Person                                      $person
     *
     * @return bool
     */
    public function checkPersonPermission($app_or_id, Person $person)
    {
        if ($this->isPersonSet($app_or_id, $person)) {
            return true;
        }

        foreach ($person->usergroups as $g) {
            if ($this->isUsergroupSet($app_or_id, $g)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get a new function that can be used to filter on an array of apps.
     *
     * @param Person $person
     *
     * @return callable
     */
    public function getAgentAppFilterCallable(Person $person)
    {
        $me = $this;

        return function (AppInstance $app) use ($person, $me) {
            if ($app->perm_type != 'set') {
                return true;
            }

            return $me->checkPersonPermission($app, $person);
        };
    }
}
