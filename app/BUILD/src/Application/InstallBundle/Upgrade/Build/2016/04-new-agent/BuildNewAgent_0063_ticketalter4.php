<?php

namespace Application\InstallBundle\Upgrade\Build;

class BuildNewAgent_0063_ticketalter4 extends AbstractBuild
{
    public function run()
    {
        $this->execSlowAlterTableQuiet('custom_data_ticket', 'ADD UNIQUE INDEX `unique_idx` (`field_id` ASC, `ticket_id` ASC, `root_field_id` ASC)');
    }
}

//[[build:1460678422]]
