<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\AgentPermissions;

use Application\DeskPRO\People\AgentPermissions\Value\ChatPermissions;
use Application\DeskPRO\People\AgentPermissions\Value\GeneralPermissions;
use Application\DeskPRO\People\AgentPermissions\Value\OrgPermissions;
use Application\DeskPRO\People\AgentPermissions\Value\PeoplePermissions;
use Application\DeskPRO\People\AgentPermissions\Value\PermissionValueInterface;
use Application\DeskPRO\People\AgentPermissions\Value\ProblemsPermissions;
use Application\DeskPRO\People\AgentPermissions\Value\PublishPermissions;
use Application\DeskPRO\People\AgentPermissions\Value\SnippetsPermissions;
use Application\DeskPRO\People\AgentPermissions\Value\TasksPermissions;
use Application\DeskPRO\People\AgentPermissions\Value\TicketPermissions;
use Application\DeskPRO\People\PermissionsSetInterface;

class AgentPermissions implements PermissionsSetInterface
{
    /**
     * @var ChatPermissions
     */
    public $chat;

    /**
     * @var GeneralPermissions
     */
    public $general;

    /**
     * @var OrgPermissions
     */
    public $org;

    /**
     * @var PeoplePermissions
     */
    public $people;

    /**
     * @var PublishPermissions
     */
    public $publish;

    /**
     * @var TicketPermissions
     */
    public $ticket;

    /**
     * @var TasksPermissions
     */
    public $tasks;

    /**
     * @var ProblemsPermissions
     */
    public $problems;

    /**
     * @var SnippetsPermissions
     */
    public $snippet;

    /**
     * @var array
     */
    public static $prefix_map = [
        'agent_tickets'  => 'ticket',
        'agent_people'   => 'people',
        'agent_org'      => 'org',
        'agent_chat'     => 'chat',
        'agent_publish'  => 'publish',
        'agent_general'  => 'general',
        'agent_tasks'    => 'tasks',
        'agent_problems' => 'problems',
        'agent_snippets' => 'snippet',
    ];

    public function __construct()
    {
        $this->chat     = new ChatPermissions();
        $this->general  = new GeneralPermissions();
        $this->org      = new OrgPermissions();
        $this->people   = new PeoplePermissions();
        $this->publish  = new PublishPermissions();
        $this->ticket   = new TicketPermissions();
        $this->tasks    = new TasksPermissions();
        $this->problems = new ProblemsPermissions();
        $this->snippet  = new SnippetsPermissions();
    }

    /**
     * @return Value\PermissionValueInterface[]
     */
    public function getCollections()
    {
        return [
            $this->chat,
            $this->general,
            $this->org,
            $this->people,
            $this->publish,
            $this->ticket,
            $this->tasks,
            $this->problems,
            $this->snippet,
        ];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $arr = [];
        foreach (get_object_vars($this) as $prop => $val) {
            if (!$val instanceof PermissionValueInterface) {
                continue;
            }
            $arr[$prop] = [];
            foreach ($this->$prop->getNames() as $name) {
                $arr[$prop][$name] = (bool) $this->$prop->$name;
            }
        }

        return $arr;
    }

    /**
     * Reads perms in from an array.
     *
     * @param array $perms
     */
    public function fromArray(array $perms)
    {
        foreach (get_object_vars($this) as $prop => $val) {
            if (!$val instanceof PermissionValueInterface) {
                continue;
            }
            if (!isset($perms[$prop])) {
                continue;
            }

            foreach ($this->$prop->getNames() as $name) {
                $this->$prop->$name = isset($perms[$prop][$name]) ? ((bool) $perms[$prop][$name]) : false;
            }
        }
    }
}
