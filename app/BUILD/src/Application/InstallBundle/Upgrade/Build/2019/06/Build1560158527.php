<?php

namespace Application\InstallBundle\Upgrade\Build;

use Application\DeskPRO\Tickets\ExecutorContext;

class Build1560158527 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $rows = $this->getDbConnection()->fetchAll("
          SELECT id, by_agent_mode, by_user_mode 
          FROM ticket_triggers 
          WHERE sys_name IN ('default_newticket_agentemail', 'default_newreply_agentemail', 'default_update_agentemail')
        ");

        foreach ($rows as $row) {
            foreach (['by_agent_mode', 'by_user_mode'] as $mode) {
                if (!empty($row[$mode])) {
                    $existModes = explode(',', $row[$mode]);
                    if (!in_array(ExecutorContext::METHOD_PHONE, $existModes)) {
                        $existModes[] = ExecutorContext::METHOD_PHONE;
                    }

                    $newModes = implode(',', $existModes);
                    if ($newModes !== $existModes) {
                        $statement = $this->getDbConnection('default')->prepare(
                            "UPDATE ticket_triggers SET {$mode} = :new_modes WHERE id = :id"
                        );
                        $statement->execute([
                            'id'        => $row['id'],
                            'new_modes' => $newModes,
                        ]);
                    }
                }
            }
        }
    }
}
