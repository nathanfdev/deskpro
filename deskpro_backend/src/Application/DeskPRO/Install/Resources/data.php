<?php
##BEGIN:locale.language##
$l = new \Application\DeskPRO\Entity\Language();
$l['title'] = 'Default Engligh';
$l['locale'] = 'en_US';
$l['language_package'] = 'DeskproLanguages\\DeskPRO\\LangPackage';
\Application\DeskPRO\App::getOrm()->persist($l);
\Application\DeskPRO\App::getOrm()->flush();


##BEGIN:locale.locale##


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
\Application\DeskPRO\App::getOrm()->persist($q);
\Application\DeskPRO\App::getOrm()->flush();


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
\Application\DeskPRO\App::getOrm()->persist($q);
\Application\DeskPRO\App::getOrm()->flush();


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
		'options'   => array('status'    => 'awaiting_agent',)
	),
	array(
		'type'      => 'is_hold',
		'op'        => 'is',
		'options'   => array('is_hold'   => '0')
	),
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
		'options'   => array('status'    => 'awaiting_agent',)
	),
	array(
		'type'      => 'is_hold',
		'op'        => 'is',
		'options'   => array('is_hold'   => '0')
	),
);
\Application\DeskPRO\App::getOrm()->persist($q);
\Application\DeskPRO\App::getOrm()->flush();

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
);
\Application\DeskPRO\App::getOrm()->persist($q);
\Application\DeskPRO\App::getOrm()->flush();


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
);
\Application\DeskPRO\App::getOrm()->persist($q);
\Application\DeskPRO\App::getOrm()->flush();


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
);
\Application\DeskPRO\App::getOrm()->persist($q);
\Application\DeskPRO\App::getOrm()->flush();


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
);
\Application\DeskPRO\App::getOrm()->persist($q);
\Application\DeskPRO\App::getOrm()->flush();


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
);
\Application\DeskPRO\App::getOrm()->persist($q);
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
$j['description'] = 'Checks to make sure volatile search tables are fileld';
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


##BEGIN:create_jobs.process_email_gateways##

$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'process_email_gateways';
$j['worker_group'] = 'process_email_gateways';
$j['title'] = 'Process Email Gateways';
$j['description'] = 'Runs through the email gateways and processes new messages';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\ProcessEmailGateways';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\ProcessEmailGateways::DEFAULT_INTERVAL;
\Application\DeskPRO\App::getOrm()->persist($j);
\Application\DeskPRO\App::getOrm()->flush();
