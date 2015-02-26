<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

use Doctrine\DBAL\Connection;

class Build1420541683 extends AbstractBuild
{
    public function run()
    {
        $this->out("Adding label permissions");

        $insert_perms = array();

        $perm_map = array(
            'labels.downloads.agent_can_create'     => 'agent_publish.downloads_create_labels',
            'labels.feedback.agent_can_create'      => 'agent_publish.feedback_create_labels',
            'labels.articles.agent_can_create'      => 'agent_publish.articles_create_labels',
            'labels.news.agent_can_create'          => 'agent_publish.news_create_labels',
            'labels.organizations.agent_can_create' => 'agent_org.create_labels',
            'labels.people.agent_can_create'        => 'agent_people.create_labels',
            'labels.tickets.agent_can_create'       => 'agent_tickets.create_labels',
            'labels.chat.agent_can_create'          => 'agent_chat.create_labels',
        );

        // They are enabled by default (via old rows in settings.php)
        // so we are checking db settings for '0' value
        $perm_settings = $this->container->getDb()->fetchAllKeyValue("
            SELECT name, value
            FROM settings
            WHERE name IN (?)
        ", array(array_keys($perm_map)), array(Connection::PARAM_STR_ARRAY));

        foreach ($perm_map as $old_setting => $new_perm) {
            if (!isset($perm_settings[$old_setting]) || $perm_settings[$old_setting] === "1") {
                $insert_perms[] = $new_perm;
            }
        }

        if ($insert_perms) {
            $super_ug_ids = $this->container->getDb()->fetchAllCol("SELECT id FROM usergroups WHERE sys_name IN ('agent_all_perms', 'agent_all_safe_perms')");
            if (!$super_ug_ids) $super_ug_ids = array(0);

            $all_perm_agents = $this->container->getDb()->fetchAllCol("
                SELECT people.id
                FROM people
                LEFT JOIN person2usergroups ON (person2usergroups.person_id = people.id)
                WHERE person2usergroups.usergroup_id IN (?)
            ", array($super_ug_ids), array(Connection::PARAM_INT_ARRAY));
            if (!$all_perm_agents) $all_perm_agents = array(0);

            $agent_ids = $this->container->getDb()->fetchAllCol("
                SELECT people.id
                FROM people
                WHERE people.is_agent = 1 AND people.id NOT IN (?)
            ", array($all_perm_agents), array(Connection::PARAM_INT_ARRAY));

            $batch = array();

            foreach ($agent_ids as $aid) {
                foreach ($insert_perms as $perm) {
                    $batch[] = array(
                        'person_id' => $aid,
                        'name'      => $perm,
                        'value'     => 1
                    );
                }
            }

            if ($batch) {
                $this->container->getDb()->batchInsert('permissions', $batch, true);
            }
        }

        // clear perm cache
        $this->container->getDb()->executeUpdate("DELETE FROM permissions_cache");
    }
}