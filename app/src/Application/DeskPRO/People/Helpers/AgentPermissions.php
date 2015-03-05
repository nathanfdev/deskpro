<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\People\Helpers;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

/**
 * Figures out agent permissions
 */
class AgentPermissions implements \ArrayAccess, \Orb\Helper\ShortCallableInterface
{
    /** @var \Application\DeskPRO\Entity\Person */
    protected $person;

    /** @var array|null */
    protected $_allowed_ids = null;
    /** @var array */
    protected $_disallowed_ids = array();

    public function __construct(Entity\Person $person)
    {
        $this->person = $person;
    }

    public function getShortCallableNames()
    {
        return array(
            'getAgentPermissions' => '_getthis',
            'getDisallowedDepartments' => 'getDisallowedDepartments',
            'getAllowedDepartments' => 'getAllowedDepartments',
        );
    }

    // we use this because we implement arrayaccess
    // so the caller gets this, and can use it as an array.
    // So if the caller gets it through a another array access, it means
    // we support $whatever['thishelper']['thisobject'];
    public function _getthis() { return $this; }



    /**
     * Check if the user is allowed to use a particular department
     *
     * @param  int|Department $dep
     * @return bool
     */
    public function isDepartmentAllowed($dep, $context = 'tickets')
    {
        if ($dep instanceof Entity\Department) {
            $dep = $dep['id'];
        }

        return in_array($dep, $this->getAllowedDepartments($context));
    }



    /**
     * Get an array of departments the user isn't allowed to see
     *
     * @return array
     */
    public function getDisallowedDepartments($context = 'tickets')
    {
        if (isset($this->_disallowed_ids[$context])) {
            return $this->_disallowed_ids[$context];
        }

        $all_ids = App::getDataService('Department')->getIds();

        $allowed_ids = $this->getAllowedDepartments($context);

        $disallowed_ids = array_diff($all_ids, $allowed_ids);

        $this->_disallowed_ids[$context] = $disallowed_ids;

        return $this->_disallowed_ids[$context];
    }

    /**
     * Get an array of departments the user is allowed to see
     *
     * @return array
     */
    public function getAllowedDepartments($context = 'tickets')
    {
        if ($this->_allowed_ids !== null) {
            if (!isset($this->_allowed_ids[$context])) {
                return array();
            }

            return $this->_allowed_ids[$context];
        }

        $agent_groups = App::$container->getAgentGroups();
        $agent_data   = App::$container->getAgentData();

        try {
            $uids = $agent_data->getGroupIdsForAgent($this->person);
        } catch (\InvalidArgumentException $e) {
            $uids = array();
        }

        if (!$uids) {
            $uids = array(0);
        }

        // Only agent groups!
        $uids = array_filter($uids, function ($id) use ($agent_groups) {
            return $agent_groups->groupExists($id);
        });

        $allow_all = false;
        foreach ($uids as $ugid) {
            if ($agent_groups->groupExists($ugid)) {
                $g = $agent_groups->getGroup($ugid);
                if ($g->sys_name == 'agent_all_perms' || $g->sys_name == 'agent_all_safe_perms') {
                    $allow_all = true;
                    break;
                }
            }
        }

        if ($allow_all) {
            $this->_allowed_ids = array();
            foreach (array(App::$container->getTicketDepartments()->getAll(), App::$container->getChatDepartments()->getAll()) as $coll) {
                foreach ($coll as $d) {
                    $app = $d->is_tickets_enabled ? 'tickets' : 'chat';
                    if (!isset($this->_allowed_ids[$app])) {
                        $this->_allowed_ids[$app] = array();
                    }

                    $this->_allowed_ids[$app][] = $d->id;
                    if ($d && $d->parent) {
                        $this->_allowed_ids[$app][] = $d->parent->id;
                    }
                }
            }
        } else {
            $raw = App::$container->getEm()->getRepository('DeskPRO:DepartmentPermission')->getPermsForAgent($this->person->id, $uids, 'full');

            $this->_allowed_ids = array();
            foreach ($raw as $r) {
                if (!isset($this->_allowed_ids[$r['app']])) {
                    $this->_allowed_ids[$r['app']] = array();
                }
                $this->_allowed_ids[$r['app']][] = $r['department_id'];

                $dep = App::getContainer()->getDataService('Department')->get($r['department_id']);
                if ($dep && $dep->parent) {
                    $this->_allowed_ids[$r['app']][] = $dep->parent->getId();
                }
            }
        }

        if (!isset($this->_allowed_ids[$context])) {
            return array();
        }

        return $this->_allowed_ids[$context];
    }

    public function offsetExists($offset)
    {
        $o = array('allowed_dep_ids', 'disallowed_dep_ids');

        return in_array($offset, $o);
    }
    public function offsetGet($offset)
    {
        if ($offset == 'allowed_dep_ids') {
            return $this->getAllowedDepartments();
        } else {
            return $this->getDisallowedDepartments();
        }
    }
    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('offsetSet not supported');
    }
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('offsetUnset not supported');
    }
}
