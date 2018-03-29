<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\AgentPermissions\Value;

class GeneralPermissions implements PermissionValueInterface
{
    /** @var bool */
    public $picture = false;
    /** @var bool */
    public $signature = false;

    public function getNames()
    {
        return ['picture', 'signature'];
    }

    public function getDestructiveNames()
    {
        return [];
    }
}
