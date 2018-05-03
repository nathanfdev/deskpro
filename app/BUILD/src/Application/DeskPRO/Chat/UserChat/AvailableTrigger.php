<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Chat\UserChat;

use Application\DeskPRO\App;

class AvailableTrigger
{
    /**
     * Update the chat status.
     *
     * @param bool|null $is_chat_available True/false to mark chat as available/unavailable, null to auto-detect with query
     */
    public static function update($is_chat_available = null)
    {
        if ($is_chat_available === null) {
            $is_chat_available = false;

            if (!App::get('brand_aware_settings_resolver')->isChatAvailable()) {
                $agent_ids = [];
            } else {
                $agent_ids = App::getDb()->fetchAllCol("
                    SELECT person_id
                    FROM sessions
                    WHERE date_last >= ? AND active_status = 'available' AND is_person = 1 AND is_chat_available = 1 AND interface = 'agent'
                ", [date('Y-m-d H:i:s', time() - App::getSetting('core_chat.agent_timeout'))]);
                $agent_ids = array_unique($agent_ids);
            }

            if ($agent_ids) {
                $agent_ids = array_filter($agent_ids, function ($agent_id) {
                    $agent = App::getContainer()->getAgentData()->get($agent_id);
                    if ($agent && $agent->hasPerm('agent_chat.use')) {
                        return true;
                    }

                    return false;
                });
            }

            if ($agent_ids) {
                $agent_ids_cs = implode(',', $agent_ids);

                // At least one department needs to be allowed for the online agents
                $ug_ids = App::getDb()->fetchAllCol("
                    SELECT DISTINCT usergroup_id
                    FROM person2usergroups
                    WHERE person_id IN ($agent_ids_cs)
                ");
                if (!$ug_ids) {
                    $ug_ids = [0];
                }

                $all1 = App::$container->getAgentGroups()->getSysGroup('agent_all_perms')->id;
                $all2 = App::$container->getAgentGroups()->getSysGroup('agent_all_safe_perms')->id;

                // 'all perms' check
                if (in_array($all1, $ug_ids) || in_array($all2, $ug_ids)) {
                    $dep_check = true;
                } else {
                    $ug_ids_cs = implode(',', $ug_ids);

                    $dep_check = App::getDb()->fetchColumn("
                        SELECT department_id
                        FROM department_permissions
                        WHERE 
                          (
                            department_permissions.person_id IN ($agent_ids_cs) 
                            OR department_permissions.usergroup_id IN ($ug_ids_cs)
                          ) 
                          AND department_permissions.app = 'chat' 
                          AND department_permissions.value = '1'
                          AND department_permissions.is_active = 1  
                        LIMIT 1
                    ");
                }

                if ($dep_check) {
                    $is_chat_available = true;
                }
            }
        }

        $trigger_File = App::$container->getParameter('dp.user.cache_dir').'/chat_is_available.trigger';
        if ($is_chat_available) {
            file_put_contents($trigger_File, time());
            @chmod($trigger_File, 0777);
        } elseif (is_file($trigger_File)) {
            @unlink($trigger_File);
        }
    }
}
