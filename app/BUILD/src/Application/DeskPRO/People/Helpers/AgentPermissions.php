<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\People\Helpers;

use Application\DeskPRO\App;
use Application\DeskPRO\DependencyInjection\SystemServices\DepartmentDataService;
use Application\DeskPRO\Entity;

/**
 * Figures out agent permissions.
 */
class AgentPermissions implements \ArrayAccess, \Orb\Helper\ShortCallableInterface
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @var array|null
     */
    protected $_allowed_ids = null;

    /**
     * @var array
     */
    protected $_disallowed_ids = [];

    /**
     * Constructor.
     *
     * @param Entity\Person $person
     */
    public function __construct(Entity\Person $person)
    {
        $this->person = $person;
    }

    /**
     * @return array
     */
    public function getShortCallableNames()
    {
        return [
            'getAgentPermissions'      => '_getthis',
            'getDisallowedDepartments' => 'getDisallowedDepartments',
            'getAllowedDepartments'    => 'getAllowedDepartments',
        ];
    }

    /**
     * we use this because we implement arrayaccess
     * so the caller gets this, and can use it as an array.
     * So if the caller gets it through a another array access, it means
     * we support $whatever['thishelper']['thisobject'];.
     *
     * @return $this
     */
    public function _getthis()
    {
        return $this;
    }

    /**
     * Check if the user is allowed to use a particular department.
     *
     * @param int|Entity\Department $dep
     * @param string                $context
     *
     * @return bool
     */
    public function isDepartmentAllowed($dep, $context = 'tickets')
    {
        if ($dep instanceof Entity\Department) {
            $dep = $dep->getId();
        }

        return in_array($dep, $this->getAllowedDepartments($context));
    }

    /**
     * Get an array of departments the user isn't allowed to see.
     *
     * @param string $context
     * @param bool   $forceAgentData
     *
     * @return array
     */
    public function getDisallowedDepartments($context = 'tickets', $forceAgentData = false)
    {
        if (isset($this->_disallowed_ids[$context])) {
            return $this->_disallowed_ids[$context];
        }

        $all_ids = App::$container->getTicketDepartments()->getAllAllowedIds();

        $allowed_ids    = $this->getAllowedDepartments($context, $forceAgentData);
        $disallowed_ids = array_diff($all_ids, $allowed_ids);

        $this->_disallowed_ids[$context] = $disallowed_ids;

        return $this->_disallowed_ids[$context];
    }

    /**
     * Get an array of departments the user is allowed to see.
     *
     * @param string $context
     * @param bool   $forceAgentData
     * @param string $permType
     *
     * @return array
     */
    public function getAllowedDepartments($context = 'tickets', $forceAgentData = false, $permType = 'full')
    {
        if ($this->_allowed_ids !== null) {
            if (!isset($this->_allowed_ids[$context])) {
                return [];
            }

            return $this->_allowed_ids[$context];
        }

        $agent_groups = App::$container->getAgentGroups();
        $agent_data   = App::$container->getAgentData();

        try {
            $uids = $agent_data->getGroupIdsForAgent($this->person, $forceAgentData);
        } catch (\InvalidArgumentException $e) {
            $uids = [];
        }

        if (!$uids) {
            $uids = [0];
        }

        // Only agent groups!
        $uids = array_filter($uids, function ($id) use ($agent_groups) {
            return $agent_groups->groupExists($id);
        });

        $allow_all = false;
        foreach ($uids as $ugid) {
            if ($agent_groups->getGroup($ugid)->hasAllSafePermissions()) {
                $allow_all = true;
                break;
            }
        }

        if ($allow_all) {
            $this->_allowed_ids = [
                'tickets' => App::$container->getTicketDepartments()->getAllAllowedIds(),
                'chat'    => App::$container->getChatDepartments()->getAllAllowedIds(),
            ];
        } else {
            $raw = App::$container->getEm()
                ->getRepository(Entity\DepartmentPermission::class)
                ->getPermsForAgent($this->person->getId(), $uids, $permType);

            /** @var DepartmentDataService $departmentDataService */
            $departmentDataService = App::getContainer()->getDataService('Department');

            $this->_allowed_ids = [];
            foreach ($raw as $r) {
                if (!isset($this->_allowed_ids[$r['app']])) {
                    $this->_allowed_ids[$r['app']] = [];
                }

                $this->_allowed_ids[$r['app']][] = $r['department_id'];

                $dep = $departmentDataService->get($r['department_id']);
                if ($dep && $dep->getParent()) {
                    $this->_allowed_ids[$r['app']][] = $dep->getParent()->getId();
                }
            }
        }

        if (!isset($this->_allowed_ids[$context])) {
            return [];
        }

        return $this->_allowed_ids[$context];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        $o = ['allowed_dep_ids', 'disallowed_dep_ids'];

        return in_array($offset, $o);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        if ($offset == 'allowed_dep_ids') {
            return $this->getAllowedDepartments();
        } else {
            return $this->getDisallowedDepartments();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('offsetSet not supported');
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('offsetUnset not supported');
    }
}
