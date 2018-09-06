<?php

namespace Application\InstallBundle\Upgrade\Build;

use Doctrine\DBAL\Connection;

class Build1536231609 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->out('Scanning for incorrectly set ticket departments');

        $db = $this->getDbConnection('default');

        $chatDepsIds = $db->fetchAllCol('
            SELECT id FROM
            departments
            WHERE is_chat_enabled = 1
        ');

        if (count($chatDepsIds) > 0) {
            $depsWithCount = $db->fetchAllCol('
                SELECT department_id, COUNT(*) AS cnt
                FROM tickets
                WHERE department_id IN (?)
                GROUP BY department_id HAVING cnt > 0
            ', [$chatDepsIds], [Connection::PARAM_INT_ARRAY]);

            foreach ($depsWithCount as $depId) {
                $this->out("Fixing tickets using chat department {$depId}");
                $oldDep = $db->fetchAssoc('
                    SELECT d.*, dtb.brand_id as brand_id
                    FROM departments d
                    LEFT JOIN department_to_brand dtb ON d.id = dtb.department_id
                    WHERE id = ?
                ', [$depId]);

                $db->insert('departments', [
                    'title'              => $oldDep['title'],
                    'user_title'         => $oldDep['user_title'],
                    'is_tickets_enabled' => 1,
                    'is_chat_enabled'    => 0,
                    'display_order'      => 0,
                ]);

                $newId = $db->lastInsertId();
                $this->out("\tNew department is $newId");

                $db->insertIgnore('department_to_brand', [
                    'brand_id'      => $oldDep['brand_id'],
                    'department_id' => $newId,
                ]);

                $this->out("\tUpdating tickets...");
                $count = $db->update('tickets', ['department_id' => $newId], ['department_id' => $oldDep['id']]);
                $db->update('tickets_search_active', ['department_id' => $newId], ['department_id' => $oldDep['id']]);
                $this->out("\tDone: $count tickets updated");
            }
        }

        $db->update('departments', ['is_tickets_enabled' => 0], ['is_chat_enabled' => 1]);
    }
}
