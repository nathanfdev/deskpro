<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\UserPermissions\Value;

class FeedbackPermissions implements PermissionValueInterface
{
    /** @var bool */
    public $use = false;
    /** @var bool */
    public $submit = false;
    /** @var bool */
    public $no_submit_validate = false;
    /** @var bool */
    public $rate = false;
    /** @var bool */
    public $comment = false;
    /** @var bool */
    public $no_comment_validate = false;

    public function getNames()
    {
        return [
            'use', 'submit', 'no_submit_validate', 'rate', 'comment', 'no_comment_validate',
        ];
    }
}
