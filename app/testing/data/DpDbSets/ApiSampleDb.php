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
 */

namespace DpDbSets;

use Application\DeskPRO\Entity\ApiKey;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\Usergroup;

class ApiSampleDb extends AbstractDbSet
{
	/**
	 * @return int
	 */
	protected function installSet()
	{
		#------------------------------
		# Insert a default admin
		#------------------------------

		$agent = new Person();
		$agent->first_name = 'Admin';
		$agent->last_name = 'Admin';
		$email = $agent->setEmail('admin@example.com', true);
		$agent->setPassword('pass');
		$agent->is_user = true;
		$agent->is_confirmed = true;
		$agent->is_agent_confirmed = true;
		$agent->is_agent = true;
		$agent->can_agent = true;
		$agent->can_admin = true;
		$agent->can_billing = true;
		$agent->can_reports = true;

		$this->getEm()->persist($agent);
		$this->getEm()->persist($email);

		$anotherAgent = new Person();
		$anotherAgent->first_name = 'Another';
		$anotherAgent->last_name = 'Agent';
		$anotherEmail = $anotherAgent->setEmail('anotheruser@example.com', true);
		$agent->setPassword('pass');
		$anotherAgent->is_user = true;
		$anotherAgent->is_confirmed = true;
		$anotherAgent->is_agent_confirmed = true;
		$anotherAgent->is_agent = true;
		$anotherAgent->can_agent = false;
		$anotherAgent->can_admin = false;
		$anotherAgent->can_billing = false;
		$anotherAgent->can_reports = false;

		$this->getEm()->persist($agent);
		$this->getEm()->persist($email);
		$this->getEm()->persist($anotherAgent);
		$this->getEm()->persist($anotherEmail);

		$this->getEm()->flush();

		$this->getDb()->insert('permissions', array('person_id' => $agent->id, 'name' => 'admin.use', 'value' => 1));

		#------------------------------
		# Create an API key used in tests
		#------------------------------

		$api_key = new ApiKey();
		$api_key->person = $agent;
		$api_key->code = str_repeat('X', 25);
		$this->getEm()->persist($api_key);
		$this->getEm()->flush();

		$api_key = new ApiKey();
		$api_key->person = $anotherAgent;
		$api_key->code = str_repeat('Y', 25);
		$this->getEm()->persist($api_key);
		$this->getEm()->flush();


		$everyone_ug = new Usergroup();
		$everyone_ug['title'] = 'Everyone';
		$everyone_ug['note'] = '';
		$everyone_ug['sys_name'] = 'everyone';
		$this->getEm()->persist($everyone_ug);
		$this->getEm()->flush();

		$g = new Usergroup();
		$g['title'] = 'Registered';
		$g['note'] = '';
		$g['sys_name'] = 'registered';
		$this->getEm()->persist($g);
		$this->getEm()->flush();

		$agent_group = new Usergroup();
		$agent_group['title'] = 'All Permissions';
		$agent_group['note'] = '';
		$agent_group['is_agent_group'] = true;
		$this->getEm()->persist($agent_group);
		$this->getEm()->flush();

		$limitedAgentGroup = new Usergroup();
		$limitedAgentGroup['title'] = 'No Permissions';
		$limitedAgentGroup['note'] = '';
		$limitedAgentGroup['is_agent_group'] = true;
		$this->getEm()->persist($limitedAgentGroup);
		$this->getEm()->flush();

		// Permissions
		$ugid = $agent_group->getId();
		$this->getEm()->getConnection()->executeUpdate("
			INSERT INTO `permissions` (`usergroup_id`, `person_id`, `value`, `name`)
			VALUES
				($ugid, NULL, '1', 'agent_tickets.use'),
				($ugid, NULL, '1', 'agent_tickets.create'),
				($ugid, NULL, '1', 'agent_tickets.delete_own'),
				($ugid, NULL, '1', 'agent_tickets.delete_unassigned'),
				($ugid, NULL, '1', 'agent_tickets.delete_followed'),
				($ugid, NULL, '1', 'agent_tickets.modify_set_closed'),
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
				($ugid, NULL, '1', 'agent_people.delete'),
				($ugid, NULL, '1', 'agent_org.create'),
				($ugid, NULL, '1', 'agent_org.edit'),
				($ugid, NULL, '1', 'agent_chat.use'),
				($ugid, NULL, '1', 'agent_chat.view_unassigned'),
				($ugid, NULL, '1', 'agent_chat.view_others'),
				($ugid, NULL, '1', 'agent_publish.create'),
				($ugid, NULL, '1', 'agent_publish.edit'),
				($ugid, NULL, '1', 'agent_publish.validate'),
				($ugid, NULL, '1', 'agent_general.signature'),
				($ugid, NULL, '1', 'agent_general.signature_rte')
		");

		$this->getDb()->insert('person2usergroups', array('person_id' => $agent->id, 'usergroup_id' => $agent_group->id));

		$limitedGId = $limitedAgentGroup->getId();
		$this->getEm()->getConnection()->executeUpdate("
			INSERT INTO `permissions` (`usergroup_id`, `person_id`, `value`, `name`)
			VALUES
				($limitedGId, NULL, '0', 'agent_tickets.use'),
				($limitedGId, NULL, '0', 'agent_tickets.create'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_set_closed'),
				($limitedGId, NULL, '0', 'agent_tickets.reply_own'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_own'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_department_own'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_fields_own'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_assign_agent_own'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_assign_team_own'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_assign_self_own'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_cc_own'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_merge_own'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_labels_own'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_notes_own'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_set_hold_own'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_set_awaiting_user_own'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_set_awaiting_agent_own'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_set_resolved_own'),
				($limitedGId, NULL, '0', 'agent_tickets.reply_to_followed'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_followed'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_department_followed'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_fields_followed'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_assign_agent_followed'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_assign_team_followed'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_assign_self_followed'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_cc_followed'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_merge_followed'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_labels_followed'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_notes_followed'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_set_hold_followed'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_set_awaiting_user_followed'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_set_awaiting_agent_followed'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_set_resolved_followed'),
				($limitedGId, NULL, '0', 'agent_tickets.view_unassigned'),
				($limitedGId, NULL, '0', 'agent_tickets.reply_unassigned'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_unassigned'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_department_unassigned'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_fields_unassigned'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_assign_agent_unassigned'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_assign_team_unassigned'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_assign_self_unassigned'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_merge_unassigned'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_labels_unassigned'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_notes_unassigned'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_set_hold_unassigned'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_set_awaiting_user_unassigned'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_set_awaiting_agent_unassigned'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_set_resolved_unassigned'),
				($limitedGId, NULL, '0', 'agent_tickets.view_others'),
				($limitedGId, NULL, '0', 'agent_tickets.reply_others'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_others'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_department_others'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_fields_others'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_assign_agent_others'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_assign_team_others'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_assign_self_others'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_merge_others'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_labels_others'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_notes_others'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_set_hold_others'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_set_awaiting_user_others'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_set_awaiting_agent_others'),
				($limitedGId, NULL, '0', 'agent_tickets.modify_set_resolved_others'),
				($limitedGId, NULL, '0', 'agent_people.use'),
				($limitedGId, NULL, '0', 'agent_people.create'),
				($limitedGId, NULL, '0', 'agent_people.edit'),
				($limitedGId, NULL, '0', 'agent_people.validate'),
				($limitedGId, NULL, '0', 'agent_people.manage_emails'),
				($limitedGId, NULL, '0', 'agent_people.reset_password'),
				($limitedGId, NULL, '0', 'agent_people.notes'),
				($limitedGId, NULL, '0', 'agent_people.disable'),
				($limitedGId, NULL, '0', 'agent_org.create'),
				($limitedGId, NULL, '0', 'agent_org.edit'),
				($limitedGId, NULL, '0', 'agent_chat.use'),
				($limitedGId, NULL, '0', 'agent_chat.view_unassigned'),
				($limitedGId, NULL, '0', 'agent_chat.view_others'),
				($limitedGId, NULL, '0', 'agent_publish.create'),
				($limitedGId, NULL, '0', 'agent_publish.edit'),
				($limitedGId, NULL, '0', 'agent_publish.validate'),
				($limitedGId, NULL, '0', 'agent_general.signature'),
				($limitedGId, NULL, '0', 'agent_general.signature_rte')
		");

		$this->getDb()->insert('person2usergroups', array('person_id' => $anotherAgent->id, 'usergroup_id' => $limitedAgentGroup->id));

		#------------------------------
		# A few ticket categories
		#------------------------------

		$this->getDb()->exec("
			INSERT INTO `ticket_categories` (`id`, `parent_id`, `title`,`display_order`)
			VALUES
				(1, NULL, 'Test Category', 0)
		");

		#------------------------------
		# A few departments
		#------------------------------

		$this->getDb()->exec("
			INSERT INTO `departments` (`id`, `parent_id`, `title`, `user_title`, `is_tickets_enabled`, `is_chat_enabled`, `display_order`)
			VALUES
				(1, NULL, 'Support', '', 1, 0, 0),
				(2, NULL, 'Sales', '', 1, 0, 0),
				(3, NULL, 'Support', '', 0, 1, 0),
				(4, NULL, 'Sales', '', 0, 1, 0);
		");

		// Initial agent has access to all deps
		$this->getDb()->insert('department_permissions', array('department_id' => 1, 'person_id' => $agent->id, 'app' => 'tickets', 'name' => 'full', 'value' => 1));
		$this->getDb()->insert('department_permissions', array('department_id' => 2, 'person_id' => $agent->id, 'app' => 'tickets', 'name' => 'full', 'value' => 1));
		$this->getDb()->insert('department_permissions', array('department_id' => 3, 'person_id' => $agent->id, 'app' => 'chat', 'name' => 'full', 'value' => 1));
		$this->getDb()->insert('department_permissions', array('department_id' => 4, 'person_id' => $agent->id, 'app' => 'chat', 'name' => 'full', 'value' => 1));

		// The everyone group has access to all deps too
		$this->getDb()->insert('department_permissions', array('department_id' => 1, 'usergroup_id' => $everyone_ug->id, 'app' => 'tickets', 'name' => 'full', 'value' => 1));
		$this->getDb()->insert('department_permissions', array('department_id' => 2, 'usergroup_id' => $everyone_ug->id, 'app' => 'tickets', 'name' => 'full', 'value' => 1));
		$this->getDb()->insert('department_permissions', array('department_id' => 3, 'usergroup_id' => $everyone_ug->id, 'app' => 'chat', 'name' => 'full', 'value' => 1));
		$this->getDb()->insert('department_permissions', array('department_id' => 4, 'usergroup_id' => $everyone_ug->id, 'app' => 'chat', 'name' => 'full', 'value' => 1));

		#------------------------------
		# Example ticket
		#------------------------------

		$department = $this->getEm()->find('DeskPRO:Department', 1);

		$ticket = new Ticket();
		$ticket->disableAutoTicketProcess();
		$ticket->creation_system = Ticket::CREATED_WEB_PERSON;
		$ticket->person          = $agent;
		$ticket->department      = $department;
		$ticket->subject         = 'Test';
		$ticket->status          = Ticket::STATUS_AWAITING_AGENT;

		$message = new TicketMessage();
		$message->person  = $agent;
		$message->ticket  = $ticket;
		$message->message = "Test Ticket";
		$ticket->addMessage($message);

		$ticket->recomputeHash();

		$this->getEm()->persist($ticket);
		$this->getEm()->persist($message);
		$this->getEm()->flush();

		#------------------------------
		# Settings
		#------------------------------

		// Initial settings that mark as as "installed"
		$this->getDb()->exec("
			REPLACE INTO `settings` (`name`, `value`)
			VALUES
				('core.app_secret', 'YXI5Z2HSQ9IF8KROQQ63GL4FB4CV57ZIIZ7CZO68FUDYBZIP2M'),
				('core.cron_logreport.cli-phperr.log', '1380716762'),
				('core.default_from_email', 'noreply@example.com'),
				('core.default_timezone', 'UTC'),
				('core.deskpro_build', '".time()."'),
				('core.deskpro_build_num', '0'),
				('core.deskpro_url', 'http://localhost:8888/'),
				('core.deskpro_version', '20131002122551'),
				('core.done_data_initializer', '1'),
				('core.done_rewrite_urls_check', '".time()."'),
				('core.install_build', '".time()."'),
				('core.install_key', '6S7X77ZAR2CYSDT4GJCJ'),
				('core.install_timestamp', '".time()."'),
				('core.install_token', 'PUGYIA9E82Z8JCPKO0NKGC957HITHNZRFHY4CQ3V1380214398'),
				('core.last_cron_run', '".time()."'),
				('core.last_cron_start', '".time()."'),
				('core.license', 'TlZNVi0wMTEyLUZVVVNFVEJHVFJNRU9KQlNHVlJNUVNTUgERC3\r\nlkZGRncEQKPwB2IyU+LiJjOgZ9FhE8ARdRIQ4OCR8seUR0ZRUZ\r\nJi9+cQB4eTF5ZjQ3P2J5TXYxdREHWzB/a1xiVQ0KeQdqMS5Qf1\r\nYtWXwZagd5DX9OCxASXzAzNGJmGTE7HhAKEBBnODZiGyYGAXVt\r\nLh8TKxcMQyFbKiAhP08aEFoECSM4TQkmMS8mEXJ1UQQINRcsAG\r\noHPBBxZxcFP1l7Uw8TJwseDn1IXAI5WwxLfVQoASkUClloBy93\r\nUEF2XFMQCwYFSC9aewFYHwJVeV0RAAonCEkhIzkjHn8WWSkRPn\r\ncpVyxrMQw6fARnIk8TDQcQCGcZRSombUhedVMENwhxUmpTLUIV\r\nZHRUflZ5UAhnAVs0CyhTZgspTkUIfQVdNWA'),
				('core.rewrite_urls', '1'),
				('core.setup_initial', '1'),
				('core.task_completed_add_ticketfield', '".time()."'),
				('core.twitter_last_cleanup', '".time()."'),
				('core.use_agent_team', '1'),
				('core_tickets.enable_like_search_auto', '1'),
				('user.kb_subscriptions_last', '".time()."');
		");

		return 1;
	}
}