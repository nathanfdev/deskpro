<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\AuthBundle\Permissions\Portal;


use Application\AppBundle\Helper\ArbitratyHasher;
use Application\DeskPRO\Cache\Adapter\SimpleArrayCache;
use Application\DeskPRO\Cache\ConvenientCache;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Permission;

class PortalPermissionsLoader
{
    /**
     * @var ArbitratyHasher
     */
    protected $hash_generator;

    /**
     * @var ConvenientCache
     */
    protected $cache;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $conn;

    public function __construct(Connection $connection)
    {
        $this->conn = $connection;
    }

    public function loadPermissionsForGroupSet(array $usergroupIds)
    {
        $that = $this;

        return $this->generateAndCache(
            array(
                'loadPermissionsForGroupSet',
                $usergroupIds
            ),
            function() use ($that, $usergroupIds) {
                $perms = $that->getUsergroupsPermissions($usergroupIds);

                $result = array();
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
     * @return mixed|null
     * @internal
     */
    public function getAllPermissions()
    {
        $conn = $this->conn;

        return $this->generateAndCache(
            array(
                'getAllPermissions'
            ),
            function() use ($conn) {
                return $conn->fetchAllGrouped(
                    '
                    SELECT usergroup_id, name, value
                    FROM permissions
                    WHERE person_id IS NULL
                    ',
                    array(),
                    'usergroup_id'
                );
            }
        );
    }

    /**
     * @param $usergroupIds
     * @return mixed|null
     * @internal
     */
    public function getUsergroupsPermissions($usergroupIds)
    {
        $that = $this;

        return $this->generateAndCache(
            array(
                'getUsergroupsPermissions',
                $usergroupIds
            ),
            function () use ($that, $usergroupIds) {
                $usergroupIds = array_fill_keys($usergroupIds, true);

                $result = array();
                foreach ($that->getAllPermissions() as $id => $permission) {
                    if (isset($usergroupIds[$id])) {
                        $result[$id] = $permission;
                    }
                }

                return $result;
            }
        );
    }

    /**
     * @param mixed $params   the "ArbitraryHasher" input to create cache key for this callable
     * @param mixed $callable doesn't need to be a callable, can be any default value, but usually is a callable
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
     * @return string
     */
    protected function generateHash($input)
    {
        if (null === $this->hash_generator) {
            $this->hash_generator = new ArbitratyHasher();
        }

        return $this->hash_generator->generateHash($input);
    }
}