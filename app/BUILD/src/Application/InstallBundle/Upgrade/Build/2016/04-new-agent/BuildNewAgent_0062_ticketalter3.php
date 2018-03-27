<?php

/**
 * DeskPRO.
 */

namespace Application\InstallBundle\Upgrade\Build;

class BuildNewAgent_0062_ticketalter3 extends AbstractBuild
{
    public function run()
    {
        $this->execSlowAlterTableQuiet('ticket_slas', 'ADD UNIQUE INDEX unique_ticket_sla_idx (`ticket_id`, `sla_id`)');
    }
}

//[[build:1460678421]]
