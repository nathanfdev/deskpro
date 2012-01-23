<?php

##BEGIN:locale.language##
$l = new \Application\DeskPRO\Entity\Language();
$l['title'] = 'Default Engligh';
$l['locale'] = 'en_US';
$l['language_package'] = 'DeskproLanguages\\DeskPRO\\LangPackage';
$em->persist($l);
$em->flush();

##BEGIN:create_department.default##
$q = new \Application\DeskPRO\Entity\Department();
$q['title'] = 'General';
$q['is_tickets_enabled'] = true;
$q['is_chat_enabled'] = true;
$em->persist($q);
$em->flush();

##BEGIN:create_article_cat.default##
$q = new \Application\DeskPRO\Entity\ArticleCategory();
$q['title'] = 'General';
$em->persist($q);
$em->flush();

##BEGIN:create_download_cat.default##
$q = new \Application\DeskPRO\Entity\DownloadCategory();
$q['title'] = 'General';
$em->persist($q);
$em->flush();

##BEGIN:create_news_cat.default##
$q = new \Application\DeskPRO\Entity\NewsCategory();
$q['title'] = 'General';
$em->persist($q);
$em->flush();

##BEGIN:create_filter.agent##
$q = new \Application\DeskPRO\Entity\TicketFilter();
$q['order_by']   = 'ticket.urgency:desc';
$q['person']     = null;
$q['title']      = 'My Tickets';
$q['is_enabled'] = true;
$q['is_global']  = true;
$q['sys_name']   = 'agent';
$q['terms']      = array(array(
		'type'      => 'agent',
		'op'        => 'is',
		'options'   => array('agent'     => '-1',)
	),
	array(
		'type'      => 'status',
		'op'        => 'is',
		'options'   => array('status'    => 'awaiting_agent',)
	),
	array(
		'type'      => 'is_hold',
		'op'        => 'is',
		'options'   => array('is_hold'   => '0')
	),
);
$em->persist($q);
$em->flush();


##BEGIN:create_filter.agent_team##
$q = new \Application\DeskPRO\Entity\TicketFilter();
$q['order_by']   = 'ticket.urgency:desc';
$q['person']     = null;
$q['title']      = 'My Team\'s Tickets';
$q['is_enabled'] = true;
$q['is_global']  = true;
$q['sys_name']   = 'agent_team';
$q['terms']      = array(array(
		'type'      => 'agent_team',
		'op'        => 'is',
		'options'   => array('agent_team' => '-1',)
	),
	array(
		'type'      => 'status',
		'op'        => 'is',
		'options'   => array('status'    => 'awaiting_agent',)
	),
	array(
		'type'      => 'is_hold',
		'op'        => 'is',
		'options'   => array('is_hold'   => '0')
	),
);
$em->persist($q);
$em->flush();


##BEGIN:create_filter.participant##
$q = new \Application\DeskPRO\Entity\TicketFilter();
$q['order_by']   = 'ticket.urgency:desc';
$q['person']     = null;
$q['title']      = 'TIckets I Follow';
$q['is_enabled'] = true;
$q['is_global']  = true;
$q['sys_name']   = 'participant';
$q['terms']      = array(array(
		'type'      => 'participant',
		'op'        => 'is',
		'options'   => array('agent'     => '-1',)
	),
	array(
		'type'      => 'status',
		'op'        => 'is',
		'options'   => array('status'    => 'awaiting_agent',)
	),
	array(
		'type'      => 'is_hold',
		'op'        => 'is',
		'options'   => array('is_hold'   => '0')
	),
);
$em->persist($q);
$em->flush();


##BEGIN:create_filter.unassigned##
$q = new \Application\DeskPRO\Entity\TicketFilter();
$q['order_by']   = 'ticket.urgency:desc';
$q['person']     = null;
$q['title']      = 'Unassigned';
$q['is_enabled'] = true;
$q['is_global']  = true;
$q['sys_name']   = 'unassigned';
$q['terms']      = array(array(
		'type'      => 'agent',
		'op'        => 'is',
		'options'   => array('agent'     => '0',)
	),
	array(
		'type'      => 'status',
		'op'        => 'is',
		'options'   => array('status'    => 'awaiting_agent',)
	),
	array(
		'type'      => 'is_hold',
		'op'        => 'is',
		'options'   => array('is_hold'   => '0')
	),
);
$em->persist($q);
$em->flush();


##BEGIN:create_filter.all##
$q = new \Application\DeskPRO\Entity\TicketFilter();
$q['order_by']   = 'ticket.urgency:desc';
$q['person']     = null;
$q['title']      = 'All';
$q['is_enabled'] = true;
$q['is_global']  = true;
$q['sys_name']   = 'all';
$q['terms']      = array(
	array(
		'type'      => 'status',
		'op'        => 'is',
		'options'   => array('status'    => 'awaiting_agent',)
	),
	array(
		'type'      => 'is_hold',
		'op'        => 'is',
		'options'   => array('is_hold'   => '0')
	),
);
$em->persist($q);
$em->flush();

##BEGIN:create_filter.agent_w_hold##
$q = new \Application\DeskPRO\Entity\TicketFilter();
$q['order_by']   = 'ticket.urgency:desc';
$q['person']     = null;
$q['title']      = 'My Tickets';
$q['is_enabled'] = true;
$q['is_global']  = true;
$q['sys_name']   = 'agent_w_hold';
$q['terms']      = array(array(
		'type'      => 'agent',
		'op'        => 'is',
		'options'   => array('agent'     => '-1',)
	),
	array(
		'type'      => 'status',
		'op'        => 'is',
		'options'   => array('status'    => 'awaiting_agent',)
	),
	array(
		'type'      => 'is_hold',
		'op'        => 'is',
		'options'   => array('is_hold'   => '1')
	),
);
$em->persist($q);
$em->flush();


##BEGIN:create_filter.agent_team_w_hold##
$q = new \Application\DeskPRO\Entity\TicketFilter();
$q['order_by']   = 'ticket.urgency:desc';
$q['person']     = null;
$q['title']      = 'My Team\'s Tickets';
$q['is_enabled'] = true;
$q['is_global']  = true;
$q['sys_name']   = 'agent_team_w_hold';
$q['terms']      = array(array(
		'type'      => 'agent_team',
		'op'        => 'is',
		'options'   => array('agent_team' => '-1',)
	),
	array(
		'type'      => 'status',
		'op'        => 'is',
		'options'   => array('status'    => 'awaiting_agent',)
	),
	array(
		'type'      => 'is_hold',
		'op'        => 'is',
		'options'   => array('is_hold'   => '1')
	),
);
$em->persist($q);
$em->flush();


##BEGIN:create_filter.participant_w_hold##
$q = new \Application\DeskPRO\Entity\TicketFilter();
$q['order_by']   = 'ticket.urgency:desc';
$q['person']     = null;
$q['title']      = 'TIckets I Follow';
$q['is_enabled'] = true;
$q['is_global']  = true;
$q['sys_name']   = 'participant_w_hold';
$q['terms']      = array(array(
		'type'      => 'participant',
		'op'        => 'is',
		'options'   => array('agent'     => '-1',)
	),
	array(
		'type'      => 'status',
		'op'        => 'is',
		'options'   => array('status'    => 'awaiting_agent',)
	),
	array(
		'type'      => 'is_hold',
		'op'        => 'is',
		'options'   => array('is_hold'   => '1')
	),
);
$em->persist($q);
$em->flush();


##BEGIN:create_filter.unassigned_w_hold##

$q = new \Application\DeskPRO\Entity\TicketFilter();
$q['order_by']   = 'ticket.urgency:desc';
$q['person']     = null;
$q['title']      = 'Unassigned';
$q['is_enabled'] = true;
$q['is_global']  = true;
$q['sys_name']   = 'unassigned_w_hold';
$q['terms']      = array(array(
		'type'      => 'agent',
		'op'        => 'is',
		'options'   => array('agent'     => '0',)
	),
	array(
		'type'      => 'status',
		'op'        => 'is',
		'options'   => array('status'    => 'awaiting_agent',)
	),
	array(
		'type'      => 'is_hold',
		'op'        => 'is',
		'options'   => array('is_hold'   => '1')
	),
);
$em->persist($q);
$em->flush();


##BEGIN:create_filter.all_w_hold##
$q = new \Application\DeskPRO\Entity\TicketFilter();
$q['order_by']   = 'ticket.urgency:desc';
$q['person']     = null;
$q['title']      = 'All';
$q['is_enabled'] = true;
$q['is_global']  = true;
$q['sys_name']   = 'all_w_hold';
$q['terms']      = array(
	array(
		'type'      => 'status',
		'op'        => 'is',
		'options'   => array('status'    => 'awaiting_agent',)
	),
	array(
		'type'      => 'is_hold',
		'op'        => 'is',
		'options'   => array('is_hold'   => '1')
	),
);
$em->persist($q);
$em->flush();

##BEGIN:create_trigger.urgency_base##
$q = new \Application\DeskPRO\Entity\TicketTrigger();
$q->title = 'urgency.base';
$q->sys_name = 'urgency.base';
$q->event_trigger = 'new_ticket';
$q->is_enabled = 1;
$q->terms = array();
$q->actions = array(
	array(
		'type' => 'urgency_set',
		'options' => array(
			'num' => 1
		)
	)
);

$em->persist($q);
$em->flush();

##BEGIN:create_trigger.auto_close_resolve_user_reply##
// When a ticket has been awaiting agent for 2 months, set it to resolved
$q = new \Application\DeskPRO\Entity\TicketTrigger();
$q->title = 'auto_close.resolve_user_reply';
$q->sys_name = 'auto_close.resolve_user_reply';
$q->event_trigger = 'time_user_waiting';
$q->event_trigger_option = '5259487'; // 2 months
$q->is_enabled = 1;
$q->terms = array(
	array (
		'type' => 'status',
		'op' => 'is',
		'options' => array (
			'status' => 'awaiting_agent',
		),
	)
);
$q->actions = array(
	array(
		'type' => 'satus',
		'options' => array(
			'status' => 'resolved'
		)
	)
);

$em->persist($q);
$em->flush();

##BEGIN:create_trigger.auto_close_resolve_agent_reply##
// When a ticket has been awaiting user for 5 days, set it to resolved
$q = new \Application\DeskPRO\Entity\TicketTrigger();
$q->title = 'auto_close.resolve_agent_reply';
$q->sys_name = 'auto_close.resolve_agent_reply';
$q->event_trigger = 'time_agent_waiting';
$q->event_trigger_option = '432000'; // 2 months
$q->is_enabled = 1;
$q->terms = array(
	array (
		'type' => 'status',
		'op' => 'is',
		'options' => array (
			'status' => 'awaiting_user',
		),
	)
);
$q->actions = array(
	array(
		'type' => 'satus',
		'options' => array(
			'status' => 'resolved'
		)
	)
);

$em->persist($q);
$em->flush();

##BEGIN:create_trigger.auto_close_close_user_reply##
// When a ticket has been resolved for 15 months, set it to closed
$q = new \Application\DeskPRO\Entity\TicketTrigger();
$q->title = 'auto_close.close_resolved';
$q->sys_name = 'auto_close.close_resolved';
$q->event_trigger = 'time_resolved';
$q->event_trigger_option = '1296000'; // 15 days
$q->is_enabled = 1;
$q->terms = array(
	array (
		'type' => 'status',
		'op' => 'is',
		'options' => array (
			'status' => 'resolved',
		),
	)
);
$q->actions = array(
	array(
		'type' => 'satus',
		'options' => array(
			'status' => 'closed'
		)
	)
);

$em->persist($q);
$em->flush();

##BEGIN:create_style.master##

$s = new \Application\DeskPRO\Entity\Style();
$s['title'] = 'Default';
$s['note'] = 'Default style';
$s['css_dir'] = 'stylesheets/user';
$em->persist($s);
$em->flush();


##BEGIN:create_jobs.cleanup_client_messages##
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'cleanup_client_messages';
$j['worker_group'] = 'cleanup';
$j['title'] = 'Cleanup Client Messages';
$j['description'] = 'Cleanup expired client polling messages';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\CleanupClientMessages';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\CleanupClientMessages::DEFAULT_INTERVAL;
$em->persist($j);
$em->flush();


##BEGIN:create_jobs.cleanup_sessions##
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'cleanup_sessions';
$j['worker_group'] = 'cleanup';
$j['title'] = 'Cleanup Client Messages';
$j['description'] = 'Cleanup expired sessions';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\CleanupSessions';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\CleanupSessions::DEFAULT_INTERVAL;
$em->persist($j);
$em->flush();


##BEGIN:create_jobs.ensure_search_tables##
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'ensure_search_tables';
$j['worker_group'] = 'ensure_search_tables';
$j['title'] = 'Ensure Search Tables';
$j['description'] = 'Checks to make sure volatile search tables are fileld (i.e., in event of a reboot they are re-filled)';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\EnsureSearchTables';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\EnsureSearchTables::DEFAULT_INTERVAL;
$em->persist($j);
$em->flush();


##BEGIN:create_jobs.sendmail_queue##
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'sendmail_queue';
$j['worker_group'] = 'sendmail_queue';
$j['title'] = 'Sendmail Queue';
$j['description'] = 'Attempts to send queued mail, or re-send fail mail';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\SendmailQueue';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\SendmailQueue::DEFAULT_INTERVAL;
$em->persist($j);
$em->flush();


##BEGIN:create_jobs.hard_delete_tickets##
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'hard_delete_tickets';
$j['worker_group'] = 'hard_delete_tickets';
$j['title'] = 'Hard Delete Tickets';
$j['description'] = 'Processes tickets that were soft-deleted long ago and permanantly deletes them';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\HardDeleteTickets';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\HardDeleteTickets::DEFAULT_INTERVAL;
$em->persist($j);
$em->flush();



##BEGIN:create_portal_block.news##
$b = new \Application\DeskPRO\Entity\PortalPageDisplay();
$b->section = 'portal';
$b->type = 'News';
$b->is_enabled = true;
$em->persist($b);
$em->flush();

##BEGIN:create_portal_block.kb##
$b = new \Application\DeskPRO\Entity\PortalPageDisplay();
$b->section = 'portal';
$b->type = 'Kb';
$b->is_enabled = true;
$em->persist($b);
$em->flush();

##BEGIN:create_portal_block.ideas##
$b = new \Application\DeskPRO\Entity\PortalPageDisplay();
$b->section = 'portal';
$b->type = 'Ideas';
$b->is_enabled = true;
$em->persist($b);
$em->flush();

##BEGIN:create_portal_block.downloads##
$b = new \Application\DeskPRO\Entity\PortalPageDisplay();
$b->section = 'portal';
$b->type = 'Downloads';
$em->persist($b);
$em->flush();

##BEGIN:create_portal_block.contact_sidebar##
$b = new \Application\DeskPRO\Entity\PortalPageDisplay();
$b->section = 'sidebar';
$b->type = 'Contact';
$b->is_enabled = true;
$em->persist($b);
$em->flush();

##BEGIN:create_portal_block.nav_sidebar##
$b = new \Application\DeskPRO\Entity\PortalPageDisplay();
$b->section = 'sidebar';
$b->type = 'Nav';
$b->is_enabled = true;
$em->persist($b);
$em->flush();

##BEGIN:create_portal_block.staff_sidebar##
$b = new \Application\DeskPRO\Entity\PortalPageDisplay();
$b->section = 'sidebar';
$b->type = 'Staff';
$b->is_enabled = true;
$em->persist($b);
$em->flush();

##BEGIN:create_portal_block.news_sidebar##
$b = new \Application\DeskPRO\Entity\PortalPageDisplay();
$b->section = 'sidebar';
$b->type = 'News';
$em->persist($b);
$em->flush();

##BEGIN:create_portal_block.downloads_sidebar##
$b = new \Application\DeskPRO\Entity\PortalPageDisplay();
$b->section = 'sidebar';
$b->type = 'Downloads';
$em->persist($b);
$em->flush();

##BEGIN:create_portal_block.ideas_sidebar##
$b = new \Application\DeskPRO\Entity\PortalPageDisplay();
$b->section = 'sidebar';
$b->type = 'Ideas';
$em->persist($b);
$em->flush();

##BEGIN:create_portal_block.labels_sidebar##
$b = new \Application\DeskPRO\Entity\PortalPageDisplay();
$b->section = 'sidebar';
$b->type = 'Labels';
$em->persist($b);
$em->flush();

##BEGIN:create_portal_block.twitter_sidebar##
$b = new \Application\DeskPRO\Entity\PortalPageDisplay();
$b->section = 'sidebar';
$b->type = 'Twitter';
$em->persist($b);
$em->flush();



##BEGIN:agent_teams.default1##
$t = new \Application\DeskPRO\Entity\AgentTeam();
$t['title'] = 'Support Managers';
$em->persist($t);
$em->flush();

##BEGIN:agent_teams.default2##
$t = new \Application\DeskPRO\Entity\AgentTeam();
$t['title'] = '1st Level Support';
$em->persist($t);
$em->flush();

##BEGIN:agent_teams.default3##
$t = new \Application\DeskPRO\Entity\AgentTeam();
$t['title'] = '2nd Level Support';
$em->persist($t);
$em->flush();


##BEGIN:agent_group.default1##
$t = new \Application\DeskPRO\Entity\AgentTeam();
$t['title'] = 'Support Managers';
$em->persist($t);
$em->flush();

##BEGIN:agent_teams.default2##
$t = new \Application\DeskPRO\Entity\AgentTeam();
$t['title'] = '1st Level Support';
$em->persist($t);
$em->flush();

##BEGIN:agent_teams.default3##
$t = new \Application\DeskPRO\Entity\AgentTeam();
$t['title'] = '2nd Level Support';
$em->persist($t);
$em->flush();



##BEGIN:usergroups.everyone##
$g = new \Application\DeskPRO\Entity\Usergroup();
$g['title'] = 'Everyone';
$g['note'] = '(system group)';
$em->persist($g);
$em->flush();

##BEGIN:usergroups.agent_all##
$AGENTGROUP_ALL = new \Application\DeskPRO\Entity\Usergroup();
$AGENTGROUP_ALL['title'] = 'All Permissions';
$AGENTGROUP_ALL['note'] = 'Agent has full permissions';
$AGENTGROUP_ALL['is_agent_group'] = true;
$em->persist($AGENTGROUP_ALL);
$em->flush();
