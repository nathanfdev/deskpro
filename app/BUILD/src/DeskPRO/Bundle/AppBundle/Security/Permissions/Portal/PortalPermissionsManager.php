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

use Application\DeskPRO\Cache\CacheAdapterInterface;
use Application\DeskPRO\Cache\ConvenientCache;
use Application\DeskPRO\Entity\Permission;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Usergroup;
use Application\DeskPRO\NewSettings\SettingsResolver;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Security\Permissions\PermissionsBag;
use Doctrine\ORM\EntityManager;

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
    private $usergroup_decider;

    /**
     * @var PortalPermissionsLoader
     */
    protected $permissions_loader;

    /**
     * Consctructor.
     *
     * @param SettingsResolver        $settingsResolver
     * @param EntityManager           $em
     * @param CacheAdapterInterface   $cacheAdapter
     * @param PortalUsergroupDecider  $usergroupDecider
     * @param PortalPermissionsLoader $permissionsLoader
     */
    public function __construct(
        SettingsResolver        $settingsResolver,
        EntityManager           $em,
        CacheAdapterInterface   $cacheAdapter,
        PortalUsergroupDecider  $usergroupDecider,
        PortalPermissionsLoader $permissionsLoader
    ) {
        $this->settingsResolver   = $settingsResolver;
        $this->usergroup_decider  = $usergroupDecider;
        $this->permissions_loader = $permissionsLoader;
        $this->cache              = new ConvenientCache($cacheAdapter); // the cache adapter knows details of how/where we cache permissions to
        $this->conn               = $em->getConnection();
        $this->em                 = $em;
    }

    /**
     * Updates the settings for the portal permissions timestamp and then forces a reload of global settings.
     */
    public function invalidatePortalPermissionsCaches()
    {
        // execute a SQL statement to update the portal.global_cache_timestamp setting
        $this->conn->executeQuery('REPLACE INTO settings SET name = "'.static::CACHE_TIMESTAMP_SETTING_NAME.'", value = '.time());
        $this->conn->executeQuery('DELETE FROM permissions_cache');

        // force a reload of global settings so our update to the timestamp is immediately applied
        $this->settingsResolver->getGlobalSettings(true)->get(static::CACHE_TIMESTAMP_SETTING_NAME);
    }

    /**
     * Returns the PermissionBag for a guest.
     *
     * @return PermissionsBag
     */
    public function getPermissionsBagForGuest()
    {
        $that     = $this;
        $generate = function () use ($that) {
            return $that->generatePermissionsMapForGuest();
        };

        if (!$this->isCacheDisabled()) {
            $usergoupIds     = $this->usergroup_decider->getUsergroupIdsForGuest();
            $permissions_map = $this->cache->get(
                $this->getCacheKeyForUsergroupIds($usergoupIds),
                $generate
            );
        } else {
            // no cache available, always generate
            $permissions_map = $generate();
        }

        $person_guest                = new PersonGuest();
        $allowed_departments         = $this->getAllowedDepartmentIds($person_guest);
        $allowed_feedback_categories = $this->getAllowedFeedbackCategoryIds($person_guest);
        $allowed_news_categories     = $this->getAllowedNewsCategoryIds($person_guest);
        $allowed_article_categories  = $this->getAllowedArticleCategoryIds($person_guest);
        $allowed_download_categories = $this->getAllowedDownloadCategoryIds($person_guest);

        return new PermissionsBag(
            $permissions_map,
            isset($allowed_departments['tickets']) ? $allowed_departments['tickets'] : [],
            isset($allowed_departments['chat']) ? $allowed_departments['chat'] : [],
            $allowed_feedback_categories,
            $allowed_news_categories,
            $allowed_article_categories,
            $allowed_download_categories
        );
    }

    /**
     * Returns the PermissionBag for the given person.
     *
     * @param Person $person
     *
     * @return PermissionsBag
     */
    public function getPermissionsBagForPerson(Person $person)
    {
        if ($person instanceof PersonGuest) {
            // if we get here with a PersonGuest (usually in a form), grab the guest bag (faster).
            return $this->getPermissionsBagForGuest();
        }

        $that     = $this;
        $generate = function () use ($that, $person) {
            return $that->generatePermissionsMapForPerson($person);
        };

        if (!$this->isCacheDisabled()) {
            $permissions_map = $this->cache->get(
                $this->getCacheKeyForPerson($person),
                $generate
            );
        } else {
            // no cache available, always generate
            $permissions_map = $generate();
        }

        $allowed_departments         = $this->getAllowedDepartmentIds($person);
        $allowed_feedback_categories = $this->getAllowedFeedbackCategoryIds($person);
        $allowed_news_categories     = $this->getAllowedNewsCategoryIds($person);
        $allowed_article_categories  = $this->getAllowedArticleCategoryIds($person);
        $allowed_download_categories = $this->getAllowedDownloadCategoryIds($person);

        return new PermissionsBag(
            $permissions_map,
            isset($allowed_departments['tickets']) ? $allowed_departments['tickets'] : [],
            isset($allowed_departments['chat']) ? $allowed_departments['chat'] : [],
            $allowed_feedback_categories,
            $allowed_news_categories,
            $allowed_article_categories,
            $allowed_download_categories
        );
    }

    /**
     * A very specific method that gets the PermissionsBag for the "Registered" usergroup.
     *
     * WARNING: this is a partial permissions bag, and is only meant to be used for permissions (not allowed cat ids, deps, etc).
     */
    public function getPartialPermissionBagForRegisteredUsergroup()
    {
        return new PermissionsBag($this->generatePermissionsMapForRegisteredUsergroup());
    }

    /**
     * @param Person $person
     *
     * @return int[]
     */
    protected function getAllowedDepartmentIds(Person $person)
    {
        return $this->permissions_loader->loadAllowedDepartments($person);
    }

    /**
     * @param Person $person
     *
     * @return int[]
     */
    protected function getAllowedFeedbackCategoryIds(Person $person)
    {
        return $this->permissions_loader->loadAllowedFeedbackCategories($person);
    }

    /**
     * @param Person $person
     *
     * @return int[]
     */
    protected function getAllowedNewsCategoryIds(Person $person)
    {
        return $this->permissions_loader->loadAllowedNewsCategories($person);
    }

    /**
     * @param Person $person
     *
     * @return int[]
     */
    protected function getAllowedArticleCategoryIds(Person $person)
    {
        return $this->permissions_loader->loadAllowedArticleCategories($person);
    }

    /**
     * @param Person $person
     *
     * @return int[]
     */
    protected function getAllowedDownloadCategoryIds(Person $person)
    {
        return $this->permissions_loader->loadAllowedDownloadCategories($person);
    }

    /**
     * Given a set of ints, combines a hash of them with the current cache timestamp to get the cache key.
     *
     * @param array $usergroupIds
     *
     * @return string
     */
    public function getCacheKeyForUsergroupIds(array $usergroupIds)
    {
        return $this->getCacheTimestamp().'-permissions-'.Usergroup::generateUsergroupSetKey($usergroupIds);
    }

    /**
     * Gets the portal permissions timestamp from the global SettingsBag.
     *
     * @return int
     */
    public function getCacheTimestamp()
    {
        return $this->settingsResolver->getGlobalSettings()->get(static::CACHE_TIMESTAMP_SETTING_NAME);
    }

    /**
     * Given a person, uses IDs from usergroups and does getCacheKeyForUsergroupIds.
     *
     * @param Person $person
     *
     * @return string
     */
    public function getCacheKeyForPerson(Person $person)
    {
        // cache THIS for a request. It won't change during a single request!

        // cache this below as well..... it does lots of querying etc
        $usergroup_ids = $this->usergroup_decider->getUsergroupIdsForPerson($person);

        return $this->getCacheKeyForUsergroupIds($usergroup_ids);
    }

    /**
     * Get IDs from usergroups and does getCacheKeyForUsergroupIds.
     *
     * @param array $usergroups
     *
     * @return string
     */
    public function getCacheKeyForUsergroups(array $usergroups)
    {
        $usergroupIds = [];

        foreach ($usergroups as $usergroup) {
            $usergroupIds[] = $usergroup['id'];
        }

        return $this->getCacheKeyForUsergroupIds($usergroupIds);
    }

    /**
     * Generates the permissions map.
     *
     * @param Person $person
     *
     * @return array
     *
     * @deprecated use getPermissionsBagForPerson - this is meant to be used internally
     */
    public function generatePermissionsMapForPerson(Person $person)
    {
        // sometimes a guest object might sneak through here
        // but we always want to benefit from the same caching for guests
        if ($person instanceof PersonGuest) {
            return $this->generatePermissionsMapForGuest();
        }

        $usergoupIds = $this->usergroup_decider->getUsergroupIdsForPerson($person);

        return $this->permissions_loader->loadPermissions($usergoupIds);
    }

    /**
     * Generate permission map for a very specific usergroup "Registered".
     *
     * @return array
     *
     * @deprecated this is meant to be used internally only
     */
    public function generatePermissionsMapForRegisteredUsergroup()
    {
        /** @var \Application\DeskPRO\Entity\Usergroup $registered */
        $registered = $this->em->getRepository('DeskPRO:Usergroup')->findOneBy(['sys_name' => 'registered']);

        return $this->permissions_loader->loadPermissions([$registered->getId()]);
    }

    /**
     * @return mixed|null
     *
     * @deprecated use getPermissionsBagForGuest - this is meant to be used internally
     */
    public function generatePermissionsMapForGuest()
    {
        $usergoupIds = $this->usergroup_decider->getUsergroupIdsForGuest();

        return  $this->permissions_loader->loadPermissions($usergoupIds);
    }

    /**
     * @return bool
     */
    public function isCacheDisabled()
    {
        // FixMe make cache aware of configuration changes
        //return $this->settingsResolver->getGlobalSettings()->get('portal.disable_permissions_cache', false);
        return true;
    }
}
