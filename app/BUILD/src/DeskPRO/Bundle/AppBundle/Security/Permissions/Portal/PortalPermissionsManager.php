<?php

namespace DeskPRO\Bundle\AppBundle\Security\Permissions\Portal;

use Application\DeskPRO\Cache\CacheAdapterInterface;
use Application\DeskPRO\Cache\ConvenientCache;
use Application\DeskPRO\Entity\Permission;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Usergroup;
use Application\DeskPRO\NewSettings\SettingsResolver;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Helper\ArbitraryHasher;
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
     * @var \Application\DeskPRO\Cache\ConvenientCache
     */
    private $cache;

    /**
     * @var array
     */
    private $permissionBagCache = [];

    /**
     * @var PortalUsergroupDecider
     */
    private $usergroupDecider;

    /**
     * @var PortalPermissionsLoader
     */
    protected $permissionsLoader;

    /**
     * Constructor.
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
        $this->settingsResolver  = $settingsResolver;
        $this->usergroupDecider  = $usergroupDecider;
        $this->permissionsLoader = $permissionsLoader;
        $this->cache             = new ConvenientCache($cacheAdapter); // the cache adapter knows details of how/where we cache permissions to
        $this->em                = $em;
    }

    /**
     * Updates the settings for the portal permissions timestamp and then forces a reload of global settings.
     */
    public function invalidatePortalPermissionsCaches()
    {
        $connection = $this->em->getConnection();

        // execute a SQL statement to update the portal.global_cache_timestamp setting
        $connection->executeQuery('REPLACE INTO settings SET name = "'.static::CACHE_TIMESTAMP_SETTING_NAME.'", value = '.time());
        $connection->executeQuery('DELETE FROM permissions_cache');

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
        return $this->generateAndCache(
            ['getPermissionsBagForGuest'],
            function () {
                $userGroups = $this->usergroupDecider->getUsergroupIdsForGuest();
                $permissions = $this->permissionsLoader->getUsergroupPermissions($userGroups);

                return $this->createPermissionBag($userGroups, $permissions);
            }
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
        // if we get here with a PersonGuest (usually in a form), grab the guest bag (faster).
        if ($person instanceof PersonGuest) {
            return $this->getPermissionsBagForGuest();
        }

        // we create person permission map based just on its user groups (ignore agent groups and permission overrides),
        // agent should be as just a user so we can cache permission bag based on usergroup ids

        $userGroups = $this->usergroupDecider->getUsergroupIdsForPerson($person);

        return $this->generateAndCache(
            ['getPermissionsBagForPerson', Usergroup::generateUsergroupSetKey($userGroups)],
            function () use ($userGroups) {
                $permissions = $this->permissionsLoader->getUsergroupPermissions($userGroups);

                return $this->createPermissionBag($userGroups, $permissions);
            }
        );
    }

    /**
     * A very specific method that gets the PermissionsBag for the "Registered" usergroup.
     *
     * WARNING: this is a partial permissions bag, and is only meant to be used for permissions (not allowed cat ids, deps, etc).
     */
    public function getPartialPermissionBagForRegisteredUsergroup()
    {
        return $this->getPartialPermissionBagForUsergroups([
            $this->em->getRepository(Usergroup::class)->findOneBy(['sys_name' => Usergroup::REGISTERED]),
        ]);
    }

    /**
     * Get permissions for specific user groups.
     *
     * WARNING: this is a partial permissions bag, and is only meant to be used for permissions (not allowed cat ids, deps, etc).
     *
     * @param array $userGroups
     *
     * @return mixed|null
     */
    public function getPartialPermissionBagForUsergroups(array $userGroups)
    {
        return $this->generateAndCache(
            [
                'getPartialPermissionBagForUsergroups',
                $userGroups,
            ],
            function () use ($userGroups) {
                $permissions = $this->permissionsLoader->getUsergroupPermissions($userGroups);

                return new PermissionsBag($permissions);
            }
        );
    }

    /**
     * @param array $userGroups
     * @param array $permissions
     *
     * @return PermissionsBag
     */
    private function createPermissionBag(array $userGroups, array $permissions)
    {
        $cacheKey = md5(serialize([
            array_map(function ($userGroup) {
                return $userGroup instanceof Usergroup ? $userGroup->getId() : $userGroup;
            }, $userGroups),
            array_map(function ($permission) {
                return $permission instanceof Permission ? $permission->getId() : $permission;
            }, $permissions),
        ]));

        if (!isset($this->permissionBagCache[$cacheKey])) {
            $this->permissionBagCache[$cacheKey] = new PermissionsBag(
                $permissions,
                $this->permissionsLoader->getAllowedTicketDepartments($userGroups),
                $this->permissionsLoader->getAllowedChatDepartments($userGroups),
                $this->permissionsLoader->getAllowedFeedbackCategories($userGroups),
                $this->permissionsLoader->getAllowedNewsCategories($userGroups),
                $this->permissionsLoader->getAllowedArticleCategories($userGroups),
                $this->permissionsLoader->getAllowedDownloadCategories($userGroups),
                $this->permissionsLoader->getAllowedGuides($userGroups)
            );
        }

        return $this->permissionBagCache[$cacheKey];
    }

    /**
     * @param array    $params
     * @param callable $callable
     *
     * @return mixed|null
     */
    private function generateAndCache(array $params, callable $callable)
    {
        $settings = $this->settingsResolver->getGlobalSettings();

        // FixMe make cache aware of configuration changes
        $isCacheDisabled = true; // $settings->get('portal.disable_permissions_cache', false);
        $hashGenerator   = new ArbitraryHasher();

        $params['timestamp'] = $settings->get(static::CACHE_TIMESTAMP_SETTING_NAME);

        return $isCacheDisabled ? $callable() : $this->cache->get($hashGenerator->generateHash($params), $callable);
    }
}
