<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\UserPermissions\Value;

class GuidesPermissions implements PermissionValueInterface
{
    /** @var bool */
    public $use = false;
    /** @var bool */
    public $rate = false;
    /** @var bool */
    public $comment = false;
    /** @var bool */
    public $no_comment_validate = false;

    public function getNames()
    {
        return [
            'use', 'rate', 'comment', 'no_comment_validate',
        ];
    }
}
