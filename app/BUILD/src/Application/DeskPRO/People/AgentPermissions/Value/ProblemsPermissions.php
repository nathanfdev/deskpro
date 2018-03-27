<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\People\AgentPermissions\Value;

class ProblemsPermissions implements PermissionValueInterface
{
    /** @var bool */
    public $view   = false;
    public $create = false;
    public $close  = false;
    public $reopen = false;
    public $delete = false;

    public function getNames()
    {
        return ['view', 'create', 'close', 'reopen', 'delete'];
    }

    public function getDestructiveNames()
    {
        return ['delete'];
    }
}
