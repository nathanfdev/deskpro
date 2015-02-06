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


use Application\AuthBundle\Permissions\PermissionsBag;
use Application\DeskPRO\App;
use Application\DeskPRO\Cache\CacheAdapterInterface;
use Application\DeskPRO\Cache\ConvenientCache;
use Application\DeskPRO\Entity\Permission;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Usergroup;
use Application\DeskPRO\NewSettings\SettingsResolver;
use Doctrine\DBAL\Connection;
use Application\DeskPRO\ORM\EntityManager;

/**
 * The PortalPermissionsManager is the gatekeeper between you (developing on the portal) and the permissions system.
 *
 * Using this, you can do both low-level and high-ish-level optations on the portal permissions.
 *
 * Low level: To signal a change in permissions, you can fetch this service from the container and call
 * invalidatePortalPermissionsCaches(). After this call, all permission maps will be regenerated upon next time they
 * are needed. Invalidate frequently as data changes in admin/agent areas.
 *
 * At a higher level, you can get the permissions map of any person with the getPermissionsBagForPerson(Person)
 * method. This is cached with the adapter given to this service's constructor in the container. But a new key
 * is used after the invalidatePortalPermissionsCaches() method is called, effectivly invalidaing all permission caches.
 *
 * Note: agent permissions have nothing to do with this class.
 */
class PortalPermissionsManager
{
    const CACHE_TIMESTAMP_SETTING_NAME = 'portal.global_cache_timestamp';

    /**
     * @var \Application\DeskPRO\NewSettings\SettingsResolver
     */
    private $settingsResolver;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $conn;

    /**
     * @var \Application\DeskPRO\Cache\ConvenientCache
     */
    private $cache;

    /**
     * @var array
     */
    protected $ug_perms;

    /**
     * @var PortalUsergroupDecider
     */
    private $usergroupDecider;

    /**
     * @var PortalPermissionsLoader
     */
    protected $permissionsLoader;

    public function __construct(
        SettingsResolver $settingsResolver,
        EntityManager $em,
        CacheAdapterInterface $cacheAdapter,
        PortalUsergroupDecider $usergroupDecider,
        PortalPermissionsLoader $permissionsLoader
    )
    {
        $this->settingsResolver = $settingsResolver;
        $this->usergroupDecider = $usergroupDecider;
        $this->permissionsLoader = $permissionsLoader;
        $this->cache = new ConvenientCache($cacheAdapter);
        $this->conn = $em->getConnection();
        $this->em = $em;
    }

    /**
     * Returns the PermissionBag for the given person
     *
     * @param Person $person
     * @return PermissionsBag
     */
    public function getPermissionsBagForPerson(Person $person)
    {
        if ($this->isCacheDisabled()) {
            return $this->generatePermissionsMapForPerson($person);
        }

        $that = $this;
        return new PermissionsBag(
            $this->cache->get(
                $this->getCacheKeyForPerson($person),
                function () use ($that, $person) {
                    return $that->generatePermissionsMapForPerson($person);
                }
            )
        );
    }

    /**
     * Returns the PermissionBag for a guest
     *
     * @return PermissionsBag
     */
    public function getPermissionsBagForGuest()
    {
        if ($this->isCacheDisabled()) {
            return $this->generatePermissionsMapForGuest();
        }

        $usergoupIds = $this->usergroupDecider->getUsergroupIdsForGuest();

        $that = $this;
        return new PermissionsBag(
            $this->cache->get(
                $this->getCacheKeyForUsergroupIds($usergoupIds),
                function() use ($that) {
                    return $that->generatePermissionsMapForGuest();
                }
            )
        );
    }

    /**
     * Updates the settings for the portal permissions timestamp and then forces a reload of global settings
     *
     * @return null
     */
    public function invalidatePortalPermissionsCaches()
    {
        // execute a SQL statement to update the portal.global_cache_timestamp setting
        $this->conn->executeQuery('REPLACE INTO settings SET name = "'.static::CACHE_TIMESTAMP_SETTING_NAME.'", value = '.time());

        // force a reload of global settings
        $this->settingsResolver->getGlobalSettings(true)->get(static::CACHE_TIMESTAMP_SETTING_NAME);
    }

    public function getAllowedDepartmentIds(Person $person)
    {
        $cache_key = $this->getCacheTimestamp().'-'.'deps'.'-'.$this->getCacheKeyForPerson($person);

        // TODO: this is just returning back all departments. add logic in the closure for actual permission logic.
        $that = $this;
        return $this->cache->get($cache_key, function() use ($person, $that) {
            $ids = $that->getEm()->createQuery('SELECT d.id FROM DeskPRO:Department d')->getScalarResult();
            return array_map(function($val) {
                return $val['id'];
            }, $ids);
        });
    }

    public function getEm()
    {
        return $this->em;
    }

    /**
     * Gets the portal permissions timestamp from the global SettingsBag
     *
     * @return int
     */
    public function getCacheTimestamp()
    {
        return $this->settingsResolver->getGlobalSettings()->get(static::CACHE_TIMESTAMP_SETTING_NAME);
    }

    /**
     * Given a set of ints, combines a hash of them with the current cache timestamp to get the cache key
     *
     * @param array $usergroupIds
     * @return string
     */
    public function getCacheKeyForUsergroupIds(array $usergroupIds)
    {
        return $this->getCacheTimestamp().'-'.Usergroup::generateUsergroupSetKey($usergroupIds);
    }

    /**
     * Given a person, uses IDs from usergroups and does getCacheKeyForUsergroupIds
     *
     * @param Person $person
     * @return string
     */
    public function getCacheKeyForPerson(Person $person)
    {
        // cache THIS for a request. It won't change during a single request!

        // cache this below as well..... it does lots of querying etc
        $usergroup_ids = $this->usergroupDecider->getUsergroupIdsForPerson($person);

        return $this->getCacheKeyForUsergroupIds($usergroup_ids);
    }

    /**
     * Get IDs from usergroups and does getCacheKeyForUsergroupIds
     *
     * @param array $usergroups
     * @return string
     */
    public function getCacheKeyForUsergroups(array $usergroups)
    {
        $usergroupIds = array();

        foreach ($usergroups as $usergroup) {
            $usergroupIds[] = $usergroup['id'];
        }

        return $this->getCacheKeyForUsergroupIds($usergroupIds);
    }

    /**
     * Generates the permissions map
     *
     * @param Person $person
     * @return array
     */
    public function generatePermissionsMapForPerson(Person $person)
    {
        $usergoupIds = $this->usergroupDecider->getUsergroupIdsForPerson($person);

        return $this->permissionsLoader->loadPermissionsForGroupSet($usergoupIds);
    }

    public function generatePermissionsMapForGuest()
    {
        $usergoupIds = $this->usergroupDecider->getUsergroupIdsForGuest();

        return $this->permissionsLoader->loadPermissionsForGroupSet($usergoupIds);
    }

    public function isCacheDisabled()
    {
        return $this->settingsResolver->getGlobalSettings()->get('portal.disable_permissions_cache', false);
    }
}
