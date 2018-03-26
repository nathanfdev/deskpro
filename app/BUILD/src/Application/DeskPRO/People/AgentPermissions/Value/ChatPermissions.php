<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\AgentPermissions\Value;

class ChatPermissions implements PermissionValueInterface
{
    /** @var bool */
    public $use = false;
    /** @var bool */
    public $view_transcripts = false;
    /** @var bool */
    public $view_unassigned = false;
    /** @var bool */
    public $view_others = false;
    /** @var bool */
    public $delete = false;
    /** @var bool */
    public $create_labels = false;

    public function getNames()
    {
        return ['use', 'view_transcripts', 'view_unassigned', 'view_others', 'delete', 'create_labels'];
    }

    public function getDestructiveNames()
    {
        return ['delete'];
    }
}
