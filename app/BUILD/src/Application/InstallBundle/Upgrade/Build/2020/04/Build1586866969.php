<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1586866969 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
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

        $agentIds = $db->fetchAllCol('SELECT id FROM people WHERE is_agent = 1 AND is_deleted = 0 AND is_disabled = 0');
        foreach ($agentIds as $agentId) {
            $db->insertIgnore('permissions', [
                'person_id' => $agentId,
                'name'      => 'agent_people.view_email_addresses',
                'value'     => 1,
                'is_active' => 1,
            ]);
        }
    }
}
