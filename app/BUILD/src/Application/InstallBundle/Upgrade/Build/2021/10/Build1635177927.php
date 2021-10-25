<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1635177927 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $db = $this->getDbConnection('default');
        $modes = $db->fetchColumn('SELECT by_agent_mode FROM ticket_triggers WHERE sys_name = "default_newticket_byagent"') ?: '';
        $modes = explode(',', $modes);
        $modes[] = 'forwarding';

        $this->execDbQuery('default', 'UPDATE ticket_triggers SET by_agent_mode = "'.implode(',', $modes).'" WHERE sys_name = "default_newticket_byagent"');
    }
}
