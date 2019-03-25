<?php
namespace Application\InstallBundle\Upgrade\Build;

class Build1552312086 extends AbstractBuild implements BlockingBuildInterface
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
        $agentIds = $db->fetchAllCol("
            SELECT id
            FROM people
            WHERE
                is_agent = 1
                AND is_deleted = 0
                AND is_disabled = 0
            ORDER BY id ASC
        ");

        foreach ($agentIds as $agentId) {
            $db->insertIgnore('person_onboarding', [
                'person_id' => $agentId,
                'onboarding_class' => 'newPendingStatus',
                'application' => 'Agent',
            ]);
        }
    }
}
