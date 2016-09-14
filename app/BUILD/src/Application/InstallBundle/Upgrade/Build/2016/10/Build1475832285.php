<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\InstallBundle\Upgrade\Build;

class Build1475832285 extends AbstractBuild
{
    public function run()
    {
        $this->out('Upgrade Usersources');
        $con = $this->getDbConnection();
        $con->exec("ALTER TABLE usersources ADD actions LONGTEXT NOT NULL COMMENT '(DC2Type:dp_json_obj)'");

        $q = 'select u.id, u.app_id, u.agent_permission_group_id pgid, auto_user_permission_group ugid, ai.settings from usersources u left join app_instances ai on ai.id = u.app_id';
        foreach ($con->fetchAll($q) as $row) {
            $actions = [];

            if ($row['pgid']) {
                $actions[] = ['type' => 'AddToAgentGroup', 'data' => $row['pgid']];
            }

            if ($row['ugid']) {
                $actions[] = ['type' => 'AddToUserGroup', 'data' => $row['ugid']];
            }

            $con->update(
                'usersources',
                ['actions' => '{"@CLASS":"Application\\DeskPRO\\Usersource\\ActionsCollection","@DATA":'.json_encode($actions).'}'],
                ['id'      => $row['id']]
            );

            if ($settings = json_decode($row['settings'], 1)) {
                unset($settings['auto_agent_permission_group'], $settings['auto_user_permission_group']);
                $settings['actions'] = $actions;

                $con->update(
                    'app_instances',
                    ['settings' => json_encode($settings)],
                    ['id'       => $row['app_id']]
                );
            }
        }

        $this->execDbQuery('default', 'ALTER TABLE usersources DROP FOREIGN KEY FK_4E3C994CF9C72B85');
        $this->execDbQuery('default', 'DROP INDEX IDX_4E3C994CF9C72B85 ON usersources');
        $this->execDbQuery('default', 'ALTER TABLE usersources DROP agent_permission_group_id');
        $this->execDbQuery('default', 'ALTER TABLE usersources DROP FOREIGN KEY FK_4E3C994C21AF6383');
        $this->execDbQuery('default', 'DROP INDEX IDX_4E3C994C21AF6383 ON usersources');
        $this->execDbQuery('default', 'ALTER TABLE usersources DROP user_permission_group_id');
    }
}
