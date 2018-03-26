<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\People\AgentPermissions\Value;

class TasksPermissions implements PermissionValueInterface
{
    /** @var bool */
    public $use = false;

    public function getNames()
    {
        return ['use'];
    }

    public function getDestructiveNames()
    {
        return [];
    }
}
