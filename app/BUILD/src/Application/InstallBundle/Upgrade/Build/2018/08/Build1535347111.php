<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1535347111 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->getDbConnection()->replace('settings', [
            'name'  => 'voice.group_missed_call_tickets',
            'value' => '1',
        ]);

        $this->getDbConnection()->replace('settings', [
            'name'  => 'voice.group_missed_call_tickets_timeout',
            'value' => '24',
        ]);
    }
}
