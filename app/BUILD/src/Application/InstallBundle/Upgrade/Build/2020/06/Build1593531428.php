<?php
namespace Application\InstallBundle\Upgrade\Build;

class Build1584526366 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }


    public function runAlters()
    {
        $this->execSlowAlterTable(
            'tickets',
            'ADD total_user_waiting_wh_start DATETIME DEFAULT NULL AFTER total_to_first_reply,
             ADD total_user_waiting_wh INT NOT NULL DEFAULT 0 AFTER total_to_first_reply,
             ADD total_to_first_reply_wh INT NOT NULL DEFAULT 0 AFTER total_to_first_reply'
        );
    }

    public function run()
    {
    }
}
