<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\People\AgentPermissions\Value;

class SnippetsPermissions implements PermissionValueInterface
{
    /** @var bool */
    public $create_snippet = false;
    /** @var bool */
    public $create_self_snippet = false;
    /** @var bool */
    public $create_team_snippet = false;
    /** @var bool */
    public $create_global_snippet = false;
    /** @var bool */
    public $edit_by_others = false;
    /** @var bool */
    public $delete_by_others = false;

    public function getNames()
    {
        return ['create_snippet', 'create_self_snippet', 'create_team_snippet', 'create_global_snippet', 'edit_by_others', 'delete_by_others'];
    }

    public function getDestructiveNames()
    {
        return ['delete_by_others'];
    }
}
