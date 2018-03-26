<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\People\PermissionUtil;

class CleanupAlways extends AbstractJob
{
    const DEFAULT_INTERVAL = 1;

    public function run()
    {
        $this->doRun();
        App::getDb()->setIsolationDefault();
    }

    private function doRun()
    {
        //------------------------------
        // cleanup chat pings
        //------------------------------

        $cutoff = time() - 180;

        App::getDb()->executeUpdate("
            DELETE FROM chat_conversation_pings
            WHERE ping_time < $cutoff
        ");

        //------------------------------
        // action_alerts
        //------------------------------

        // action_alerts are instant - it is messages we use to update react application state
        $datetime = date('Y-m-d H:i:s', time() - 300); // 5 minutes seems to be fine gap

        $ids = App::getDb()->fetchAllCol(
            'SELECT id FROM notify_action_alerts WHERE date_created < ?',
            [$datetime],
            [\PDO::PARAM_STR]);

        if ($ids) {
            $batch_ids = array_chunk($ids, 50, false);
            foreach ($batch_ids as $ids) {
                $num = App::getDb()->executeUpdate('
                    DELETE FROM notify_action_alerts
                    WHERE id IN (?)
                ', [$ids], [Connection::PARAM_INT_ARRAY]);

                if ($num) {
                    $this->logStatus("Cleaned up $num old action_alerts");
                }
            }
        }

        //------------------------------
        // notifications
        //------------------------------

        // notifications are instant, same as above
        $datetime = date('Y-m-d H:i:s', time() - 300);

        $ids = App::getDb()->fetchAllCol(
            'SELECT id FROM notify_notifications WHERE date_created < ?',
            [$datetime],
            [\PDO::PARAM_STR]);

        if ($ids) {
            $batch_ids = array_chunk($ids, 50, false);
            foreach ($batch_ids as $ids) {
                $num = App::getDb()->executeUpdate('
                    DELETE FROM notify_notifications
                    WHERE id IN (?)
                ', [$ids], [Connection::PARAM_INT_ARRAY]);

                if ($num) {
                    $this->logStatus("Cleaned up $num old notifications");
                }
            }
        }

        //------------------------------
        // Optimise perms
        //------------------------------

        if (App::getSetting('trigger.optimise_perms')) {
            $db = App::getDb();
            App::getDb()->executeUpdate("REPLACE INTO `settings` (`name`, `value`) VALUES ('trigger.optimise_perms', '0')");

            $ag_perms_cache     = $db->fetchAllGrouped('SELECT usergroup_id, name FROM permissions', [], 'usergroup_id', null, 'name');
            $ag_dep_perms_cache = [
                'full'   => $db->fetchAllGrouped("SELECT usergroup_id, department_id FROM department_permissions WHERE name = 'full'", [], 'usergroup_id', null, 'department_id'),
                'assign' => $db->fetchAllGrouped("SELECT usergroup_id, department_id FROM department_permissions WHERE name = 'assign'", [], 'usergroup_id', null, 'department_id'),
            ];

            foreach (App::$container->getAgentData()->getAgents() as $a) {
                PermissionUtil::optimizePermissions($a, $ag_perms_cache, $ag_dep_perms_cache);
            }
        }

        //------------------------------
        // Try to delete old update status file
        //------------------------------

        if (file_exists(DP_WEB_ROOT.'/auto-update-status.php') && App::getSetting('core.last_auto_upgrade_time') < time() - 180) {
            @unlink(DP_WEB_ROOT.'/auto-update-status.php');
        }
    }
}
