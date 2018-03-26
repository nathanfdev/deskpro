<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\AgentPermissions\Value;

class PeoplePermissions implements PermissionValueInterface
{
    /** @var bool */
    public $use = false;
    /** @var bool */
    public $create = false;
    /** @var bool */
    public $edit = false;
    /** @var bool */
    public $validate = false;
    /** @var bool */
    public $manage_emails = false;
    /** @var bool */
    public $reset_password = false;
    /** @var bool */
    public $notes = false;
    /** @var bool */
    public $delete = false;
    /** @var bool */
    public $disable = false;
    /** @var bool */
    public $login_as = false;
    /** @var bool */
    public $merge = false;
    /** @var bool */
    public $create_labels = false;

    public function getNames()
    {
        return ['use', 'create', 'edit', 'validate', 'manage_emails', 'reset_password', 'notes', 'delete', 'disable', 'login_as', 'merge', 'create_labels'];
    }

    public function getDestructiveNames()
    {
        return ['delete'];
    }
}
