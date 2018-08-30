<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1535627667 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $connection      = $this->getDbConnection('default');
        $chatDepartments = $connection->fetchAll('SELECT id FROM `departments` WHERE `is_chat_enabled` = 1');
        if (count($chatDepartments) > 0) {
            $departments = $connection->fetchAll(
                'SELECT `department_id`, COUNT(*) AS cnt FROM `tickets` WHERE department_id IN (?) GROUP BY department_id HAVING cnt > 0',
                [implode(', ', array_map(function ($dep) {
                    return $dep['id'];
                }, $chatDepartments))]
            );
            foreach ($departments as $department) {
                $chatDepartment = $connection->fetchAssoc('SELECT d.*, dtb.`brand_id` as brand_id FROM `departments` d LEFT JOIN `department_to_brand` dtb ON d.id = dtb.department_id WHERE id = ?', [$department['department_id']]);
                $connection->executeQuery('INSERT IGNORE INTO `departments` (`title`, `user_title`, `is_tickets_enabled`, `is_chat_enabled`, `display_order`) VALUES (?, ?, 1, 0, 0)',
                    [$chatDepartment['title'], $chatDepartment['user_title']]
                );
                $newId = $connection->lastInsertId();
                $connection->executeQuery('INSERT IGNORE INTO `department_to_brand` (`brand_id`, `department_id`) VALUES (?, ?)', [$chatDepartment['brand_id'], $newId]);
                $connection->executeQuery('UPDATE tickets SET department_id = ? WHERE department_id = ?', [$newId, $chatDepartment['id']]);
                $connection->executeQuery('UPDATE tickets_search_active SET department_id = ? WHERE department_id = ?', [$newId, $chatDepartment['id']]);
            }
            $connection->executeQuery('UPDATE departments SET is_tickets_enabled = 0 WHERE is_chat_enabled = 1');
        }
    }
}
