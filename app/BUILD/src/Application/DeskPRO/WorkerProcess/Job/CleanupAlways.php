<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
        // client_messages
        //------------------------------

        // client messages are nearly instant, so this timesnip is very low
        $datetime = date('Y-m-d H:i:s', time() - 1800);

        // Long-lived channels are still deleted after 14 days
        $datetime2 = date('Y-m-d H:i:s', time() - 1209600);

        $long_lived_channels = [
            'agent_chat.new-message',
        ];

        // We fetch first, then delete in small batches to reduce locking
        $ids = App::getDb()->fetchAllCol('
            SELECT id FROM client_messages
            WHERE (
                date_created < ? AND channel NOT IN (?)
            ) OR (
                date_created < ? AND channel IN (?)
            )
        ',
            [$datetime, $long_lived_channels, $datetime2, $long_lived_channels],
            [\PDO::PARAM_STR, Connection::PARAM_STR_ARRAY, \PDO::PARAM_STR, Connection::PARAM_STR_ARRAY]);
        if ($ids) {
            $batch_ids = array_chunk($ids, 50, false);
            foreach ($batch_ids as $ids) {
                $num = App::getDb()->executeUpdate('
                    DELETE FROM client_messages
                    WHERE id IN (?)
                ', [$ids], [Connection::PARAM_INT_ARRAY]);

                if ($num) {
                    $this->logStatus("Cleaned up $num old client messages");
                }
            }
        }

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
