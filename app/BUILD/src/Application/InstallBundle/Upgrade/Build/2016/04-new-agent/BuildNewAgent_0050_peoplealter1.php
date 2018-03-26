<?php

/**
 * DeskPRO.
 */

namespace Application\InstallBundle\Upgrade\Build;

class BuildNewAgent_0050_peoplealter1 extends AbstractBuild
{
    public function run()
    {
        $this->execSlowAlterTable('people', 'DROP is_agent_confirmed');
    }
}

//[[build:1460678416]]
