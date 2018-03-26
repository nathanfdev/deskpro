<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\People\PermissionLoader;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Usergroup;
use Application\DeskPRO\People\PersonContextInterface;

/**
 * @deprecated use new PermissionsManager to get the PermissionsBag instead of people helpers
 */
class Departments extends AbstractLoader implements NoCache, PersonContextInterface
{
    /** @var bool */
    protected $has_init = false;

    /**
     * An array of categories allowed for real, that we get by computing
     * inheritance.
     *
     * @var array
     */
    protected $allowed_cats = ['tickets' => [], 'chat' => []];

    /**
     * @var bool
     */
    protected $with_overrides = false;

    public function getSubkey()
    {
        if ($this->person && $this->person->is_agent) {
            return 'person-'.$this->person->id;
        }
    }

    public function _init()
    {
        if ($this->has_init) {
            return;
        }
        $this->has_init = true;

        $in = implode(',', $this->getUsergroupIds());

        if (DP_INTERFACE == 'agent' || ($this->person->isAgent() && DP_INTERFACE != 'user')) {
            $agent_groups = App::$container->getAgentGroups();
            $allow_all    = false;
            foreach ($this->usergroup_ids as $ugid) {
                if ($agent_groups->groupExists($ugid)) {
                    $usergroup = $agent_groups->getGroup($ugid);
                    $sysName   = $usergroup->getSysName();

                    if ($sysName === Usergroup::AGENT_ALL_PERM || $sysName === Usergroup::AGENT_ALL_SAFE_PERM) {
                        $allow_all = true;
                        break;
                    }
                }
            }

            if ($allow_all) {
                $ticketDeps = App::$container->getTicketDepartments()->getAllAllowedList();
                $chatDeps   = App::$container->getChatDepartments()->getAllAllowedList();

                $res = array_merge($ticketDeps, $chatDeps);
            } else {
                $agent_ugs     = App::getDataService('Usergroup')->getAgentUsergroups();
                $has_agent_ugs = [];

                foreach ($this->usergroup_ids as $ugid) {
                    if (isset($agent_ugs[$ugid])) {
                        $has_agent_ugs[] = $ugid;
                    }
                }

                $res = App::$container->getEm()->getRepository('DeskPRO:DepartmentPermission')->getPermsForAgent(
                    $this->person->getId(),
                    $has_agent_ugs
                );
            }
        } else {
            // no need to check load inactive permissions, so we gonna find where is_active = 1
            $res = App::getDb()->fetchAll("
                SELECT department_id, app, name, value
                FROM department_permissions
                WHERE usergroup_id IN($in) 
                AND is_active = 1
                AND value = 1
            ");
        }

        $parent_with_allowed_child = [
            'tickets' => [],
            'chat'    => [],
        ];

        foreach ($res as $department) {
            if (!empty($department['person_id'])) {
                $this->with_overrides = true;
            }

            $dep = App::getDataService('Department')->get($department['department_id']);

            $check = 'is_'.$department['app'].'_enabled';
            if (!isset($dep[$check]) || !$dep[$check]) {
                continue;
            }

            $this->allowed_cats[$department['app']][$department['department_id']][$department['name']] = $department['value'];

            // With departments, if a child is allowed, then the parent is too since its just a wrapper
            if ($dep && $dep->parent) {
                $parent_with_allowed_child[$department['app']][$dep->parent->getId()] = true;

                $this->allowed_cats[$department['app']][$dep->parent->getId()][$department['name']] = 1;
            }
        }

        // Now for each parent, we need to make sure at least one child is allowed
        // because you can never use a parent without a child (e.g, dont want to be able to assign to a parent)
        foreach (App::getDataService('Department')->getParentNodes() as $dep) {
            if (!isset($parent_with_allowed_child['chat'][$dep->getId()])) {
                unset($this->allowed_cats['chat'][$dep->getId()]);
            }
            if (!isset($parent_with_allowed_child['tickets'][$dep->getId()])) {
                unset($this->allowed_cats['tickets'][$dep->getId()]);
            }
        }
    }

    /**
     * Is a dep allowed?
     *
     * @return bool
     */
    public function isAllowed($id, $app, $permission = 'full')
    {
        $this->_init();

        if (!empty($this->allowed_cats[$app][$id]['full'])) {
            return true;
        }

        return !empty($this->allowed_cats[$app][$id][$permission]);
    }

    public function getAllAllowed()
    {
        $this->_init();

        return $this->allowed_cats;
    }

    /**
     * Get an array of all allowed categories.
     *
     * @return array
     */
    public function getAllowed($app, $permission = 'full')
    {
        $this->_init();

        $ids = [];
        foreach ($this->allowed_cats[$app] as $id => $perms) {
            if (!empty($perms[$permission]) || !empty($perms['full'])) {
                $ids[$id] = $id;
            }
        }

        return $ids;
    }

    /**
     * @param string $app
     * @param string $permission
     *
     * @return int[]
     */
    public function getAllowedIds($app, $permission = 'full')
    {
        $this->_init();

        if (empty($this->allowed_cats[$app])) {
            return [];
        }

        $ids = [];

        foreach ($this->allowed_cats[$app] as $id => $perms) {
            if (isset($perms[$permission]) && $perms[$permission]) {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    /**
     * Get an array of data we'll serialize.
     *
     * @return array
     */
    protected function serializeData()
    {
        $this->_init();

        return [
            'allowed_cats' => $this->allowed_cats,
        ];
    }

    /**
     * Initialize this object with an array of saved data.
     *
     * @param array $data
     */
    protected function unserializeData(array $data)
    {
        $this->allowed_cats = $data['allowed_cats'];
    }
}
