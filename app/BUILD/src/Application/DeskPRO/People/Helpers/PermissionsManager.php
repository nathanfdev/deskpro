<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
 *
 * @category Tickets
 */

namespace Application\DeskPRO\People\Helpers;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\PermissionCache;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PermissionLoader\Usergroups;
use Application\DeskPRO\People\PersonContextInterface;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use DeskPRO\Bundle\AppBundle\Settings\PortalSettingsResolver;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use Orb\Util\Util;

/**
 * The permission manager takes care of loading effective permissions for a user.
 */
class PermissionsManager implements \Orb\Helper\ShortCallableInterface
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * All the users usergroups.
     *
     * @string array
     */
    protected $usergroup_ids;

    /**
     * Groups we got inherited from an org.
     *
     * @string array
     */
    protected $org_usergroup_ids;

    /**
     * The usergroups key for all the users groups.
     *
     * @var string
     */
    protected $usergroups_key;

    /**
     * Types that we know we want, but havent been loaded yet.
     *
     * @param array
     */
    protected $queued_types = [];

    /**
     * Initialized loaders.
     *
     * @var \Application\DeskPRO\People\PermissionLoader\AbstractLoader[]
     */
    protected $loaders = [];

    /**
     * Initialized checkers.
     *
     * @var \Application\DeskPRO\People\PermissionChecker\AbstractChecker[]
     */
    protected $checkers = [];

    /**
     * @var array
     */
    protected $dirty_caches = [];

    /**
     * @var bool
     */
    protected $admin_god_mode = false;

    /**
     * Permission records loaded?
     *
     * @var bool
     */
    protected $is_loaded = false;

    // TODO: Implement a better caching system. At the moment caching actually *hurts* performance
    // which is why it's being disabled here:
    protected $enable_caching = false;

    /**
     * @param \Application\DeskPRO\Entity\Person $person
     * @param array                              $options
     */
    public function __construct(Person $person, array $options = null)
    {
        $this->person = $person;
        $personId     = $person->getId();
        $agent_data   = App::$container->getAgentData();
        $forceLoadUg  = isset($options['force_load_usergroups']) && $options['force_load_usergroups'];

        if (!$forceLoadUg && $person->isActiveAgent()) {
            $this->usergroup_ids = $agent_data->getGroupIdsForAgent($person);
        } else {
            $this->usergroup_ids = App::getDb()->fetchAllCol('
                SELECT person2usergroups.usergroup_id
                FROM person2usergroups
                LEFT JOIN usergroups ON usergroups.id = person2usergroups.usergroup_id
                WHERE person2usergroups.person_id = ? AND usergroups.is_enabled = 1
            ', [$this->person->getId()]);
        }

        $everyone_ug = App::$container->getUserGroups()->getEveryoneGroup(false);
        if ($everyone_ug && $everyone_ug->isEnabled()) {
            $this->usergroup_ids[] = $everyone_ug->getId();
        } else {
            $this->usergroup_ids[] = 0;
        }

        $reg_ug = App::$container->getUserGroups()->getRegisteredGroup(false);
        if ($personId && $reg_ug && $reg_ug->isEnabled()) {
            $this->usergroup_ids[] = $reg_ug->getId();
        }

        // And org ones...
        $this->org_usergroup_ids = [];
        if ($this->person->getOrganization()) {
            $this->org_usergroup_ids = App::getDb()->fetchAllCol('
                SELECT organization2usergroups.usergroup_id
                FROM organization2usergroups
                JOIN usergroups ON usergroups.id = organization2usergroups.usergroup_id
                WHERE organization2usergroups.organization_id = ? AND usergroups.is_enabled = 1
            ', [$this->person->getOrganization()->getId()]);

            if ($this->org_usergroup_ids) {
                $this->usergroup_ids = array_merge($this->usergroup_ids, $this->org_usergroup_ids);
                $this->usergroup_ids = array_unique($this->usergroup_ids);
            }
        }

        sort($this->usergroup_ids, SORT_NUMERIC);

        $this->usergroups_key = PermissionCache::generateUsergroupSetKey($this->usergroup_ids);

        if ($this->person->isAgent()) {
            $this->usergroups_key = $this->usergroups_key.'-person-'.$personId;
        }

        \DpShutdown::add([$this, 'flushCache']);
    }

    /**
     * Admin mode enables all permissions when viewing the user interface (usually through the portal editor).
     *
     * @return bool
     */
    public function enableAdminMode()
    {
        $this->admin_god_mode = true;
    }

    /**
     * Get an array of usergroups this user has applied to them.
     *
     * @return array
     */
    public function getUsergroupIds()
    {
        return $this->usergroup_ids;
    }

    /**
     * Of the groups we belong to, get the ones we inherited from our organization.
     *
     * @return array
     */
    public function getOrganizationUsergroupIds()
    {
        return $this->org_usergroup_ids;
    }

    /**
     * Get the usergroups set key.
     *
     * @return string
     */
    public function getUsergroupSetKey()
    {
        return $this->usergroups_key;
    }

    /**
     * Load permissions of a particular type.
     *
     * @param $name
     */
    public function loadPermissions($name)
    {
        $args = func_get_args();

        foreach ($args as $name) {
            if (isset($this->loaders[strtolower($name)])) {
                continue;
            }

            if (!class_exists($this->getLoaderClass($name))) {
                throw new \InvalidArgumentException("No loader for permission type `$name`");
            }

            $this->queued_types[] = $name;
        }
    }

    /**
     * This loads up the queued permission types. The reason they're queued is so we
     * can fetch multiple records from the cache at once, which is helpful when
     * the cache is a slow-cache such as the db.
     */
    public function _loadQueued()
    {
        //-------------------------
        // Fetch from the cache first
        //-------------------------

        if (!$this->is_loaded && $this->enable_caching) {
            $caches = App::getEntityRepository(PermissionCache::class)->loadPermissionTypes($this->usergroups_key, $this->person->getId());

            foreach ($caches as $cache) {
                $loader = $cache;
                $name   = Util::getBaseClassname($loader);

                if ($loader instanceof PersonContextInterface) {
                    $loader->setPersonContext($this->person);
                }

                // Cache for 'usergroups' (which has perms) must be cache for the agent
                if ($loader instanceof Usergroups) {
                    if ($this->person && $this->person->is_agent && strpos($loader->loaded_key, '-person-') === false) {
                        continue;
                    }
                }

                $this->loaders[strtolower($name)] = $loader;
            }
        }

        $this->is_loaded = true;

        //-------------------------
        // Load the rest for the first time
        //-------------------------

        $queued_types       = $this->queued_types;
        $this->queued_types = [];

        foreach ($queued_types as $name) {
            if (isset($this->loaders[strtolower($name)])) {
                continue;
            }

            $class  = $this->getLoaderClass($name);
            $loader = new $class($this->usergroup_ids);

            if ($loader instanceof PersonContextInterface) {
                $loader->setPersonContext($this->person);
            }

            $this->loaders[strtolower($name)] = $loader;

            if (!($loader instanceof \Application\DeskPRO\People\PermissionLoader\NoCache) && $this->enable_caching) {
                $this->dirty_caches[] = PermissionCache::newFromLoader($loader, $this->person->getId());
            }
        }
    }

    /**
     * Using property overloading to give direct access to individual loaders.
     *
     * @param  $name
     *
     * @return \Application\DeskPRO\People\PermissionLoader\AbstractLoader
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Get a loader.
     *
     * @param  $name
     *
     * @return \Application\DeskPRO\People\PermissionLoader\AbstractLoader
     */
    public function get($name)
    {
        $namel = strtolower($name);

        if (isset($this->loaders[$namel])) {
            return $this->loaders[$namel];
        }
        if (isset($this->checkers[$namel])) {
            return $this->checkers[$namel];
        }

        if (substr($name, -7) === 'Checker') {
            if (!isset($this->checkers[$namel])) {
                $class = 'Application\\DeskPRO\\People\\PermissionChecker\\'.$name;
                if (!$class) {
                    throw new \InvalidArgumentException("Unknown permission checker `{$name}`");
                }

                $this->checkers[$namel] = new $class($this->person);
            }

            return $this->checkers[$namel];
        }

        if (!isset($this->loaders[$namel])) {
            $this->loadPermissions($name);
            $this->_loadQueued();
        }

        return $this->loaders[$namel];
    }

    /**
     * Get a normal usergruop permission.
     * This is a shortcut for the usergroups loader.
     *
     * @param $name
     *
     * @return bool
     */
    public function hasPerm($name)
    {
        static $god_mode_names = [
            'articles.use'  => true,
            'feedback.use'  => true,
            'downloads.use' => true,
            'news.use'      => true,
            'chat.use'      => true,
            'guides.use'    => true,
        ];

        $crossBrandAppSettings = $this->getBrandAppSettings();

        if ($this->admin_god_mode && isset($god_mode_names[$name])) {
            return true;
        }

        if ($name === 'agent_tickets.create') {
            if (!App::getDataService('Department')->getPersonDepartments($this->person, 'tickets', [], 'assign')) {
                return false;
            }
        }

        if ($name === 'articles.use' && !$crossBrandAppSettings['core.apps_kb']) {
            return false;
        }
        if ($name === 'feedback.use' && !$crossBrandAppSettings['core.apps_feedback']) {
            return false;
        }
        if ($name === 'downloads.use' && !$crossBrandAppSettings['core.apps_downloads']) {
            return false;
        }
        if ($name === 'news.use' && !$crossBrandAppSettings['core.apps_news']) {
            return false;
        }
        if ($name === 'guides.use' && !$crossBrandAppSettings['core.apps_guides']) {
            return false;
        }
        if ($name === 'chat.use' || $name === 'agent_chat.use') {
            if (!$this->get('Departments')->getAllowed('chat')) {
                return false;
            }
        }
        if ($name === 'articles.comment' || $name === 'downloads.comment' || $name === 'news.comment') {
            if (!$crossBrandAppSettings['user.publish_comments']) {
                return false;
            }
        }

        /** @var Usergroups $usergroups */
        $usergroups = $this->get('Usergroups');

        return $usergroups->getPermission($name) ? true : false;
    }

    /**
     * Flush any pending permission group caches that need to be written.
     */
    public function flushCache()
    {
        if (!$this->dirty_caches) {
            return;
        }

        try {
            foreach ($this->dirty_caches as $c) {
                $insert_cache = [
                    'name'          => $c->getName(),
                    'usergroup_key' => $c->getUsergroupKey(),
                    'usergroup_ids' => implode(',', $c->getUsergroupIds()),
                    'perms'         => serialize($c->getPerms()),
                ];

                App::getDb()->replace('permissions_cache', $insert_cache);
            }
        } catch (\Exception $e) {
            $info = \DpSys\LowError\SystemErrorHandler::getExceptionInfo($e);
            \DpSys\LowError\SystemErrorHandler::logErrorInfo($info);
        }

        $this->dirty_caches = [];
    }

    /**
     * Get the full name of the loader for a given permission type name.
     *
     * @param  $name
     *
     * @return string
     */
    public function getLoaderClass($name)
    {
        return 'Application\\DeskPRO\\People\\PermissionLoader\\'.$name;
    }

    public function getShortCallableNames()
    {
        return [
            'getPermissionsManager' => '_getthis',
            'getPermsLoader'        => 'get',
            'hasPerm'               => 'hasPerm',
            'has_perm'              => 'hasPerm',
        ];
    }

    public function _getthis()
    {
        return $this;
    }

    public function clear()
    {
        $this->person = null;
    }

    protected function getBrandAppSettings()
    {
        static $appSettings = [];

        if (!empty($appSettings)) {
            return $appSettings;
        }

        $appSettings = [
            PortalSettingsResolver::APPS_KB          => false,
            PortalSettingsResolver::APPS_DOWNLOADS   => false,
            PortalSettingsResolver::APPS_NEWS        => false,
            PortalSettingsResolver::APPS_FEEDBACK    => false,
            PortalSettingsResolver::APPS_GUIDES      => false,
            PortalSettingsResolver::PUBLISH_COMMENTS => false,
        ];

        /** @var Brand[] $brands */
        $brands = App::getEntityRepository(Brand::class)->findAll();

        /** @var BrandStack $brandStack */
        $brandStack = App::get('brand_stack');

        /** @var BrandAwareSettingsResolver $brandSettingsResolver */
        $brandSettingsResolver = App::get('brand_aware_settings_resolver');

        foreach ($brands as $brand) {
            $brandStack->push($brand, true);
            foreach ($appSettings as $key => &$setting) {
                $setting = $setting || $brandSettingsResolver->getSetting($key);
            }
            $brandStack->pop();
        }

        return $appSettings;
    }
}
