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

namespace Application\DeskPRO\JobQueue\Processor\Reset;

class PermsProcessor extends Base
{
    const JOB_TYPE = 'reset.perms';

    /**
     * {@inheritdoc}
     */
    protected function doProcess(array $data)
    {
        $this->connection->executeUpdate('DELETE FROM permissions');
        $this->connection->executeUpdate('DELETE FROM permissions_cache');
        $this->connection->executeUpdate('DELETE FROM usergroups');

        $translate = $this->container->getTranslator();

        //#BEGIN:usergroups.everyone##
        $g             = new \Application\DeskPRO\Entity\Usergroup();
        $g['title']    = $translate->phrase('agent.defaults.usergroup_everyone');
        $g['note']     = $translate->phrase('agent.defaults.usergroup_everyone_note');
        $g['sys_name'] = 'everyone';
        $this->em->persist($g);
        $this->em->flush();
        $USERGROUP_EVERYONE = $g;

        //#BEGIN:usergroups.register##
        $g             = new \Application\DeskPRO\Entity\Usergroup();
        $g['title']    = $translate->phrase('agent.defaults.usergroup_registered');
        $g['note']     = $translate->phrase('agent.defaults.usergroup_registered_note');
        $g['sys_name'] = 'registered';
        $this->em->persist($g);
        $this->em->flush();
        $USERGROUP_REG = $g;

        //#BEGIN:usergroups.agent_all##
        $AGENTGROUP_ALL                   = new \Application\DeskPRO\Entity\Usergroup();
        $AGENTGROUP_ALL['title']          = $translate->phrase('agent.defaults.usergroup_agent_all_perms');
        $AGENTGROUP_ALL['note']           = $translate->phrase('agent.defaults.usergroup_agent_all_perms_note');
        $AGENTGROUP_ALL['is_agent_group'] = true;
        $AGENTGROUP_ALL['sys_name']       = 'agent_all_perms';
        $this->em->persist($AGENTGROUP_ALL);
        $this->em->flush();

        $this->connection->executeUpdate(sprintf('INSERT INTO person2usergroups VALUES (%d, %d)', $data['context_person_id'], $AGENTGROUP_ALL->id));

        //#BEGIN:usergroups.agent_all_nondestructive##
        $AGENTGROUP_ALL_ND                   = new \Application\DeskPRO\Entity\Usergroup();
        $AGENTGROUP_ALL_ND['title']          = $translate->phrase('agent.defaults.usergroup_agent_all_non_destructive');
        $AGENTGROUP_ALL_ND['note']           = $translate->phrase('agent.defaults.usergroup_agent_all_non_destructive_note');
        $AGENTGROUP_ALL_ND['is_agent_group'] = true;
        $AGENTGROUP_ALL_ND['sys_name']       = 'agent_all_safe_perms';
        $this->em->persist($AGENTGROUP_ALL_ND);
        $this->em->flush();

        // Permissions for ND group
        $ugid = $AGENTGROUP_ALL_ND->getId();
        $this->em->getConnection()->executeUpdate("
            INSERT INTO `permissions` (`usergroup_id`, `person_id`, `value`, `name`)
            VALUES
                ($ugid, NULL, '1', 'agent_tickets.use'),
                ($ugid, NULL, '1', 'agent_tickets.create'),
                ($ugid, NULL, '1', 'agent_tickets.modify_set_archived'),
                ($ugid, NULL, '1', 'agent_tickets.reply_own'),
                ($ugid, NULL, '1', 'agent_tickets.modify_own'),
                ($ugid, NULL, '1', 'agent_tickets.modify_department_own'),
                ($ugid, NULL, '1', 'agent_tickets.modify_fields_own'),
                ($ugid, NULL, '1', 'agent_tickets.modify_assign_agent_own'),
                ($ugid, NULL, '1', 'agent_tickets.modify_assign_team_own'),
                ($ugid, NULL, '1', 'agent_tickets.modify_assign_self_own'),
                ($ugid, NULL, '1', 'agent_tickets.modify_cc_own'),
                ($ugid, NULL, '1', 'agent_tickets.modify_merge_own'),
                ($ugid, NULL, '1', 'agent_tickets.modify_labels_own'),
                ($ugid, NULL, '1', 'agent_tickets.modify_notes_own'),
                ($ugid, NULL, '1', 'agent_tickets.modify_set_hold_own'),
                ($ugid, NULL, '1', 'agent_tickets.modify_set_awaiting_user_own'),
                ($ugid, NULL, '1', 'agent_tickets.modify_set_awaiting_agent_own'),
                ($ugid, NULL, '1', 'agent_tickets.modify_set_resolved_own'),
                ($ugid, NULL, '1', 'agent_tickets.reply_to_followed'),
                ($ugid, NULL, '1', 'agent_tickets.modify_followed'),
                ($ugid, NULL, '1', 'agent_tickets.modify_department_followed'),
                ($ugid, NULL, '1', 'agent_tickets.modify_fields_followed'),
                ($ugid, NULL, '1', 'agent_tickets.modify_assign_agent_followed'),
                ($ugid, NULL, '1', 'agent_tickets.modify_assign_team_followed'),
                ($ugid, NULL, '1', 'agent_tickets.modify_assign_self_followed'),
                ($ugid, NULL, '1', 'agent_tickets.modify_cc_followed'),
                ($ugid, NULL, '1', 'agent_tickets.modify_merge_followed'),
                ($ugid, NULL, '1', 'agent_tickets.modify_labels_followed'),
                ($ugid, NULL, '1', 'agent_tickets.modify_notes_followed'),
                ($ugid, NULL, '1', 'agent_tickets.modify_set_hold_followed'),
                ($ugid, NULL, '1', 'agent_tickets.modify_set_awaiting_user_followed'),
                ($ugid, NULL, '1', 'agent_tickets.modify_set_awaiting_agent_followed'),
                ($ugid, NULL, '1', 'agent_tickets.modify_set_resolved_followed'),
                ($ugid, NULL, '1', 'agent_tickets.view_unassigned'),
                ($ugid, NULL, '1', 'agent_tickets.reply_unassigned'),
                ($ugid, NULL, '1', 'agent_tickets.modify_unassigned'),
                ($ugid, NULL, '1', 'agent_tickets.modify_department_unassigned'),
                ($ugid, NULL, '1', 'agent_tickets.modify_fields_unassigned'),
                ($ugid, NULL, '1', 'agent_tickets.modify_assign_agent_unassigned'),
                ($ugid, NULL, '1', 'agent_tickets.modify_assign_team_unassigned'),
                ($ugid, NULL, '1', 'agent_tickets.modify_assign_self_unassigned'),
                ($ugid, NULL, '1', 'agent_tickets.modify_merge_unassigned'),
                ($ugid, NULL, '1', 'agent_tickets.modify_labels_unassigned'),
                ($ugid, NULL, '1', 'agent_tickets.modify_notes_unassigned'),
                ($ugid, NULL, '1', 'agent_tickets.modify_set_hold_unassigned'),
                ($ugid, NULL, '1', 'agent_tickets.modify_set_awaiting_user_unassigned'),
                ($ugid, NULL, '1', 'agent_tickets.modify_set_awaiting_agent_unassigned'),
                ($ugid, NULL, '1', 'agent_tickets.modify_set_resolved_unassigned'),
                ($ugid, NULL, '1', 'agent_tickets.view_others'),
                ($ugid, NULL, '1', 'agent_tickets.reply_others'),
                ($ugid, NULL, '1', 'agent_tickets.modify_others'),
                ($ugid, NULL, '1', 'agent_tickets.modify_department_others'),
                ($ugid, NULL, '1', 'agent_tickets.modify_fields_others'),
                ($ugid, NULL, '1', 'agent_tickets.modify_assign_agent_others'),
                ($ugid, NULL, '1', 'agent_tickets.modify_assign_team_others'),
                ($ugid, NULL, '1', 'agent_tickets.modify_assign_self_others'),
                ($ugid, NULL, '1', 'agent_tickets.modify_merge_others'),
                ($ugid, NULL, '1', 'agent_tickets.modify_labels_others'),
                ($ugid, NULL, '1', 'agent_tickets.modify_notes_others'),
                ($ugid, NULL, '1', 'agent_tickets.modify_set_hold_others'),
                ($ugid, NULL, '1', 'agent_tickets.modify_set_awaiting_user_others'),
                ($ugid, NULL, '1', 'agent_tickets.modify_set_awaiting_agent_others'),
                ($ugid, NULL, '1', 'agent_tickets.modify_set_resolved_others'),
                ($ugid, NULL, '1', 'agent_people.use'),
                ($ugid, NULL, '1', 'agent_people.create'),
                ($ugid, NULL, '1', 'agent_people.edit'),
                ($ugid, NULL, '1', 'agent_people.validate'),
                ($ugid, NULL, '1', 'agent_people.manage_emails'),
                ($ugid, NULL, '1', 'agent_people.reset_password'),
                ($ugid, NULL, '1', 'agent_people.notes'),
                ($ugid, NULL, '1', 'agent_people.disable'),
                ($ugid, NULL, '1', 'agent_org.create'),
                ($ugid, NULL, '1', 'agent_org.edit'),
                ($ugid, NULL, '1', 'agent_chat.use'),
                ($ugid, NULL, '1', 'agent_chat.view_unassigned'),
                ($ugid, NULL, '1', 'agent_chat.view_others'),
                ($ugid, NULL, '1', 'agent_publish.create'),
                ($ugid, NULL, '1', 'agent_publish.edit'),
                ($ugid, NULL, '1', 'agent_publish.validate'),
                ($ugid, NULL, '1', 'agent_general.signature'),
                ($ugid, NULL, '1', 'agent_general.signature_rte'),
                ($ugid, NULL, '1', 'agent_snippets.create_snippet'),
                ($ugid, NULL, '1', 'agent_snippets.create_self_snippet'),
                ($ugid, NULL, '1', 'agent_snippets.create_team_snippet'),
                ($ugid, NULL, '1', 'agent_snippets.create_global_snippet'),
                ($ugid, NULL, '1', 'agent_snippets.edit_by_others'),
        ");

        $scanner = new \Application\InstallBundle\Data\UserGroupPermScanner();
        foreach ($scanner->getNames() as $p_name) {
            $p            = new \Application\DeskPRO\Entity\Permission();
            $p->usergroup = $USERGROUP_EVERYONE;
            $p->name      = $p_name;
            $p->value     = 1;
            $this->em->persist($p);
        }
        $this->em->flush();
    }
}
