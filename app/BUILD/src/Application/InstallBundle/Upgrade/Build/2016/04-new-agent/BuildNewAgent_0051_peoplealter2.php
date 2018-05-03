<?php

/**
 * DeskPRO.
 */

namespace Application\InstallBundle\Upgrade\Build;

class BuildNewAgent_0051_peoplealter2 extends AbstractBuild
{
    public function run()
    {
        $this->execSlowAlterTable('people_emails', 'DROP is_own_validated');
    }
}

//[[build:1460678417]]
