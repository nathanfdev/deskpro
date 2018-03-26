<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\UserPermissions\Value;

class TicketPermissions implements PermissionValueInterface
{
    /** @var bool */
    public $use = false;
    /** @var bool */
    public $reopen_resolved = false;
    /** @var bool */
    public $reopen_resolved_createnew = false;

    public function getNames()
    {
        return [
            'use', 'reopen_resolved', 'reopen_resolved_createnew',
        ];
    }
}
