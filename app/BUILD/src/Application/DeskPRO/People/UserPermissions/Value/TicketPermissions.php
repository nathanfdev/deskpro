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
     *          0 - no limits
     */
    public $reopen_resolved_timelimit = 0;

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
