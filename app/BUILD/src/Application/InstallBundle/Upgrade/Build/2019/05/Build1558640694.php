<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1558640694 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        // need more chars in 'name' field
        $this->execSlowAlterTable('permissions', 'CHANGE name name VARCHAR(100) NOT NULL');
    }

    public function run()
    {
        $db          = $this->getDbConnection();
        $safeGroupId = (int) $db->fetchColumn("SELECT id FROM usergroups WHERE sys_name = 'agent_all_safe_perms'");

        $sufixes = ['own', 'followed', 'unassigned', 'others'];
        $unsafe  = ['agent_tickets.modify_messages'];
        $safe    = [
            'agent_tickets.modify_messages_edit',
            'agent_tickets.modify_messages_edit_timelimited_notes',
            'agent_tickets.modify_messages_edit_notes',
            'agent_tickets.modify_messages_convert_notes',
            'agent_tickets.modify_messages_convert_messages',
        ];

        // Delete unsafe perms from safe user group
        $in = [];
        foreach ($unsafe as $perm) {
            foreach ($sufixes as $sufix) {
                $in[] = "'{$perm}_{$sufix}'";
            }
        }
        $this->execDbQuery('default', sprintf(
            'DELETE FROM permissions WHERE usergroup_id = %s and name in (%s)',
            $safeGroupId,
            implode(', ', $in)
        ));

        // Insert new save perms
        // This is not really required because new perms' will be automatically filled by perms loader
        // but just in case ...
        foreach ($safe as $perm) {
            foreach ($sufixes as $sufix) {
                $db->insert('permissions', [
                    'usergroup_id' => $safeGroupId,
                    'name'         => $perm.'_'.$sufix,
                    'value'        => 1,
                    'is_active'    => 1,
                ]);
            }
        }
    }
}
