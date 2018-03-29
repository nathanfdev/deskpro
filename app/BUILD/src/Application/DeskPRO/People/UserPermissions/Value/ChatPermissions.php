<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\UserPermissions\Value;

class ChatPermissions implements PermissionValueInterface
{
    /** @var bool */
    public $use = false;

    public function getNames()
    {
        return [
            'use',
        ];
    }
}
