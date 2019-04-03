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
    /**
     * @var int
     *          -1 = no limit
     */
    public $reopen_resolved_timelimit = -1;

    public function getNames()
    {
        return [
            'use', 'reopen_resolved', 'reopen_resolved_createnew', 'reopen_resolved_timelimit',
        ];
    }

    /**
     * @param int $value
     */
    public function setReopenResolvedTimelimit($value)
    {
        $this->reopen_resolved_timelimit = (int) $value;
    }

    /**
     * @return int
     */
    public function getReopenResolvedTimelimit()
    {
        return (int) $this->reopen_resolved_timelimit;
    }
}
