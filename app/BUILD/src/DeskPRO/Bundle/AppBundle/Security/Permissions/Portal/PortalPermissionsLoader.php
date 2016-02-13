<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Security\Permissions\Portal;

use Application\DeskPRO\Cache\Adapter\SimpleArrayCache;
use Application\DeskPRO\Cache\ConvenientCache;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Permission;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Helper\ArbitraryHasher;

/**
 * This is an adapter into the "old" permissions storage system.
 */
class PortalPermissionsLoader
{
    /**
     * @var ArbitraryHasher
     */
    protected $hash_generator;

    /**
     * @var ConvenientCache
     */
    protected $cache;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $connection;

    /**
     * Constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param array $usergroupIds
     *
     * @return mixed
     */
    public function loadPermissions(array $usergroupIds)
    {
        return $this->generateAndCache(
            [
                'loadPermissionsForGroupSet',
                $usergroupIds,
            ],
            function () use ($usergroupIds) {
                $perms = $this->getUsergroupsPermissions($usergroupIds);

                $result = [];
                foreach ($perms as $permissionGroup) {
                    foreach ($permissionGroup as $p) {
                        $result[] = $p;
                    }
                }

                return Permission::getEffectivePermissions($result);
            }
        );
    }

    /**
     * @param Person $person
     *
     * @return mixed
     */
    public function loadAllowedDepartments(Person $person)
    {
        return $this->generateAndCache(
            [
                'loadAllowedDepartments',
                $person,
            ],
            function () use ($person) {
                return $person->getPermissionsManager()->Departments->getAllAllowed();
            }
        );
    }

    /**
     * @param Person $person
     *
     * @return mixed
     */
    public function loadAllowedFeedbackCategories(Person $person)
    {
        return $this->generateAndCache(
            [
                'loadAllowedFeedbackCategories',
                $person,
            ],
            function () use ($person) {
                return $person->getPermissionsManager()->FeedbackCategories->getAllowedCategories();
            }
        );
    }

    /**
     * @param Person $person
     *
     * @return mixed
     */
    public function loadAllowedNewsCategories(Person $person)
    {
        return $this->generateAndCache(
            [
                'loadAllowedNewsCategories',
                $person,
            ],
            function () use ($person) {
                return $person->getPermissionsManager()->NewsCategories->getAllowedCategories();
            }
        );
    }

    /**
     * @param Person $person
     *
     * @return mixed
     */
    public function loadAllowedArticleCategories(Person $person)
    {
        return $this->generateAndCache(
            [
                'loadAllowedArticleCategories',
                $person,
            ],
            function () use ($person) {
                return $person->getPermissionsManager()->ArticleCategories->getAllowedCategories();
            }
        );
    }

    /**
     * @param Person $person
     *
     * @return mixed
     */
    public function loadAllowedDownloadCategories(Person $person)
    {
        return $this->generateAndCache(
            [
                'loadAllowedDownloadCategories',
                $person,
            ],
            function () use ($person) {
                return $person->getPermissionsManager()->DownloadCategories->getAllowedCategories();
            }
        );
    }

    /**
     * @param $usergroupIds
     *
     * @return mixed|null
     */
    protected function getUsergroupsPermissions($usergroupIds)
    {
        return $this->generateAndCache(
            [
                'getUsergroupsPermissions',
                $usergroupIds,
            ],
            function () use ($usergroupIds) {
                $usergroupIds = array_fill_keys($usergroupIds, true);

                $result = [];
                foreach ($this->getAllPermissions() as $id => $permission) {
                    if (isset($usergroupIds[$id])) {
                        $result[$id] = $permission;
                    }
                }

                return $result;
            }
        );
    }

    /**
     * @return mixed|null
     */
    protected function getAllPermissions()
    {
        return $this->generateAndCache(
            [
                'getAllPermissions',
            ],
            function () {
                return $this->connection->fetchAllGrouped(
                    '
                    SELECT usergroup_id, name, value
                    FROM permissions
                    WHERE person_id IS NULL
                    ',
                    [],
                    'usergroup_id'
                );
            }
        );
    }

    /**
     * @param mixed $params   the "ArbitraryHasher" input to create cache key for this callable
     * @param mixed $callable doesn't need to be a callable, can be any default value, but usually is a callable
     *
     * @return mixed|null
     */
    protected function generateAndCache($params, $callable)
    {
        return $this->getCache()->get($this->generateHash($params), $callable);
    }

    /**
     * @return ConvenientCache
     */
    protected function getCache()
    {
        if (null === $this->cache) {
            $this->cache = new ConvenientCache(new SimpleArrayCache());
        }

        return $this->cache;
    }

    /**
     * @param mixed $input
     *
     * @return string
     */
    protected function generateHash($input)
    {
        if (null === $this->hash_generator) {
            $this->hash_generator = new ArbitraryHasher();
        }

        return $this->hash_generator->generateHash($input);
    }
}
