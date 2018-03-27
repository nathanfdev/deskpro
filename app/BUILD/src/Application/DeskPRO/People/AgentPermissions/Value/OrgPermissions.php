<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\AgentPermissions\Value;

class OrgPermissions implements PermissionValueInterface
{
    /** @var bool */
    public $create = false;
    /** @var bool */
    public $edit = false;
    /** @var bool */
    public $notes = false;
    /** @var bool */
    public $delete = false;
    /** @var bool */
    public $create_labels = false;

    public function getNames()
    {
        return ['create', 'edit', 'notes', 'delete', 'create_labels'];
    }

    public function getDestructiveNames()
    {
        return ['delete'];
    }
}
