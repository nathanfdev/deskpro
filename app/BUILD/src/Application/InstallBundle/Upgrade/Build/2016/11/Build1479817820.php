<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1479817820 extends AbstractBuild
{
    public function run()
    {
        $this->out('Upgrade Usersources');
        $con = $this->getDbConnection();
        $con->exec("ALTER TABLE usersources ADD actions LONGTEXT NOT NULL COMMENT '(DC2Type:dp_json_obj)'");

        $q = 'select u.id, u.app_id, u.agent_permission_group_id pgid, u.user_permission_group_id ugid, ai.settings from usersources u left join app_instances ai on ai.id = u.app_id';
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
        $this->execDbQuery('default', 'ALTER TABLE usersources DROP FOREIGN KEY FK_4E3C994C21AF6383');
        $this->execDbQuery('default', 'DROP INDEX IDX_4E3C994CF9C72B85 ON usersources');
        $this->execDbQuery('default', 'DROP INDEX IDX_4E3C994C21AF6383 ON usersources');
        $this->execDbQuery('default', 'ALTER TABLE usersources DROP agent_permission_group_id');
        $this->execDbQuery('default', 'ALTER TABLE usersources DROP user_permission_group_id');
    }
}
