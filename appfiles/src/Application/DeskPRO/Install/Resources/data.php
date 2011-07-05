<?php
##BEGIN:locale.language##

$l = new \Application\DeskPRO\Entity\Language();
$l['title'] = 'Default Engligh';
\Application\DeskPRO\App::getOrm()->persist($l);
\Application\DeskPRO\App::getOrm()->flush();


##BEGIN:locale.locale##

$l = new \Application\DeskPRO\Entity\Locale();
$l['title'] = 'English (US)';
$l['language_id'] = 1;
$l['locale'] = 'en_US';
\Application\DeskPRO\App::getOrm()->persist($l);
\Application\DeskPRO\App::getOrm()->flush();


##BEGIN:create_filter.agent##

$q = new \Application\DeskPRO\Entity\TicketFilter();
$q['order_by']   = 'ticket.urgency:desc';
$q['person']     = null;
$q['title']      = 'Your Tickets';
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
		'options'   => array('status'    => 'open',)
	)
);
\Application\DeskPRO\App::getOrm()->persist($q);
\Application\DeskPRO\App::getOrm()->flush();


##BEGIN:create_filter.agent_team##

$q = new \Application\DeskPRO\Entity\TicketFilter();
$q['order_by']   = 'ticket.urgency:desc';
$q['person']     = null;
$q['title']      = 'Your Teams Tickets';
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
		'options'   => array('status'    => 'open',)
	)
);
\Application\DeskPRO\App::getOrm()->persist($q);
\Application\DeskPRO\App::getOrm()->flush();


##BEGIN:create_filter.participant##

$q = new \Application\DeskPRO\Entity\TicketFilter();
$q['order_by']   = 'ticket.urgency:desc';
$q['person']     = null;
$q['title']      = 'Subscribed Tickets';
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
		'options'   => array('status'    => 'open',)
	)
);
\Application\DeskPRO\App::getOrm()->persist($q);
\Application\DeskPRO\App::getOrm()->flush();


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
		'options'   => array('status'    => 'open',)
	)
);
\Application\DeskPRO\App::getOrm()->persist($q);
\Application\DeskPRO\App::getOrm()->flush();


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
		'options'   => array('status'    => 'open',)
	)
);
\Application\DeskPRO\App::getOrm()->persist($q);
\Application\DeskPRO\App::getOrm()->flush();


##BEGIN:create_ticket_trigger.newticket_notify_agents##

$t = new \Application\DeskPRO\Entity\TicketTrigger();
$t['title'] = 'New Ticket: Send notification to agents';
$t['event_trigger'] = 'new_ticket';
$t['is_enabled'] = true;
$t['terms'] = array();
$t['actions'] = array(
	array(
		'type' => 'agent_notification_new_ticket',
		'options' => array('send_to' => array('default_agents'), 'custom_template_name' => '')
	)
);
\Application\DeskPRO\App::getOrm()->persist($t);
\Application\DeskPRO\App::getOrm()->flush();


##BEGIN:create_ticket_trigger.newticket_notify_user##

$t = new \Application\DeskPRO\Entity\TicketTrigger();
$t['title'] = 'New Ticket: Send confirmation to user';
$t['event_trigger'] = 'new_ticket';
$t['is_enabled'] = true;
$t['terms'] = array();
$t['actions'] = array(
	array(
		'type' => 'user_notification_new_ticket',
		'options' => array('from_address' => 'default')
	)
);
\Application\DeskPRO\App::getOrm()->persist($t);
\Application\DeskPRO\App::getOrm()->flush();


##BEGIN:create_ticket_trigger.newreply_notify_agents##

$t = new \Application\DeskPRO\Entity\TicketTrigger();
$t['title'] = 'New Reply: Send notification to agents';
$t['event_trigger'] = 'new_reply';
$t['is_enabled'] = true;
$t['terms'] = array();
$t['actions'] = array(
	array(
		'type' => 'agent_notification_new_reply',
		'options' => array('send_to' => array('default_agents'))
	)
);
\Application\DeskPRO\App::getOrm()->persist($t);
\Application\DeskPRO\App::getOrm()->flush();


##BEGIN:create_ticket_trigger.newreply_notify_users##

$t = new \Application\DeskPRO\Entity\TicketTrigger();
$t['title'] = 'New Agent Reply: Send notification to users';
$t['event_trigger'] = 'new_reply';
$t['is_enabled'] = true;
$t['terms'] = array(
	array(
		'type' => 'action_performer',
		'op' => 'is',
		'options' => array('action_performer' => 'agent')
	)
);
$t['actions'] = array(
	array(
		'type' => 'user_notification_new_reply',
		'options' => array('user_notification_new_reply' => 1)
	)
);
\Application\DeskPRO\App::getOrm()->persist($t);
\Application\DeskPRO\App::getOrm()->flush();


##BEGIN:create_style.master##

$s = new \Application\DeskPRO\Entity\Style();
$s['title'] = 'Default';
$s['note'] = 'Default style';
\Application\DeskPRO\App::getOrm()->persist($s);
\Application\DeskPRO\App::getOrm()->flush();


##BEGIN:create_jobs.cleanup_client_messages##

$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'cleanup_client_messages';
$j['worker_group'] = 'cleanup';
$j['title'] = 'Cleanup Client Messages';
$j['description'] = 'Cleanup expired client polling messages';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\CleanupClientMessages';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\CleanupClientMessages::DEFAULT_INTERVAL;
\Application\DeskPRO\App::getOrm()->persist($j);
\Application\DeskPRO\App::getOrm()->flush();


##BEGIN:create_jobs.cleanup_sessions##

$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'cleanup_sessions';
$j['worker_group'] = 'cleanup';
$j['title'] = 'Cleanup Client Messages';
$j['description'] = 'Cleanup expired sessions';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\CleanupSessions';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\CleanupSessions::DEFAULT_INTERVAL;
\Application\DeskPRO\App::getOrm()->persist($j);
\Application\DeskPRO\App::getOrm()->flush();


##BEGIN:create_jobs.ensure_search_tables##

$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'ensure_search_tables';
$j['worker_group'] = 'ensure_search_tables';
$j['title'] = 'Ensure Search Tables';
$j['description'] = 'Checks to make sure volatile search tables are fileld (i.e., in event of a reboot they are re-filled)';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\EnsureSearchTables';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\EnsureSearchTables::DEFAULT_INTERVAL;
\Application\DeskPRO\App::getOrm()->persist($j);
\Application\DeskPRO\App::getOrm()->flush();


##BEGIN:create_jobs.sendmail_queue##

$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'sendmail_queue';
$j['worker_group'] = 'sendmail_queue';
$j['title'] = 'Sendmail Queue';
$j['description'] = 'Attempts to send queued mail, or re-send fail mail';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\SendmailQueue';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\SendmailQueue::DEFAULT_INTERVAL;
\Application\DeskPRO\App::getOrm()->persist($j);
\Application\DeskPRO\App::getOrm()->flush();


##BEGIN:create_jobs.hard_delete_tickets##

$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'hard_delete_tickets';
$j['worker_group'] = 'hard_delete_tickets';
$j['title'] = 'Hard Delete Tickets';
$j['description'] = 'Processes tickets that were soft-deleted long ago and permanantly deletes them';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\HardDeleteTickets';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\HardDeleteTickets::DEFAULT_INTERVAL;
\Application\DeskPRO\App::getOrm()->persist($j);
\Application\DeskPRO\App::getOrm()->flush();
