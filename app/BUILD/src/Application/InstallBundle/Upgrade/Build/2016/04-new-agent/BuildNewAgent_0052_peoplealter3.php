<?php

namespace Application\InstallBundle\Upgrade\Build;

class BuildNewAgent_0052_peoplealter3 extends AbstractBuild
{
    public function run()
    {
        $this->execSlowAlterTableQuiet('custom_data_person', 'ADD UNIQUE INDEX `unique_idx` (`field_id` ASC, `person_id` ASC, `root_field_id` ASC)');
    }
}

//[[build:1460678418]]
