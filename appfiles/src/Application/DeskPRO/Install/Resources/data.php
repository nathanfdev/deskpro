<?php
##BEGIN:locale.language##
\Application\DeskPRO\App::getOrm()->beginTransaction();
$l = new \Application\DeskPRO\Entity\Language();
$l['title'] = 'Default Engligh';
\Application\DeskPRO\App::getOrm()->persist($l);
\Application\DeskPRO\App::getOrm()->flush();
\Application\DeskPRO\App::getOrm()->commit();

##BEGIN:locale.locale##
\Application\DeskPRO\App::getOrm()->beginTransaction();
$l = new \Application\DeskPRO\Entity\Locale();
$l['title'] = 'English (US)';
$l['language_id'] = 1;
$l['locale'] = 'en_US';
\Application\DeskPRO\App::getOrm()->persist($l);
\Application\DeskPRO\App::getOrm()->flush();
\Application\DeskPRO\App::getOrm()->commit();

##BEGIN:create_filter.agent##
\Application\DeskPRO\App::getOrm()->beginTransaction();
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
\Application\DeskPRO\App::getOrm()->commit();

##BEGIN:create_filter.agent_team##
\Application\DeskPRO\App::getOrm()->beginTransaction();
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
\Application\DeskPRO\App::getOrm()->commit();

##BEGIN:create_filter.participant##
\Application\DeskPRO\App::getOrm()->beginTransaction();
$q = new \Application\DeskPRO\Entity\TicketFilter();
$q['order_by']   = 'ticket.urgency:desc';
$q['person']     = null;
$q['title']      = 'You\'re a Participant';
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
\Application\DeskPRO\App::getOrm()->commit();

##BEGIN:create_filter.unassigned##
\Application\DeskPRO\App::getOrm()->beginTransaction();
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
\Application\DeskPRO\App::getOrm()->commit();

##BEGIN:create_filter.all##
\Application\DeskPRO\App::getOrm()->beginTransaction();
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
\Application\DeskPRO\App::getOrm()->commit();

##BEGIN:create_style.master##
\Application\DeskPRO\App::getOrm()->beginTransaction();
$s = new \Application\DeskPRO\Entity\Style();
$s['title'] = 'Default';
$s['note'] = 'Default style';
\Application\DeskPRO\App::getOrm()->persist($s);
\Application\DeskPRO\App::getOrm()->flush();
\Application\DeskPRO\App::getOrm()->commit();

##BEGIN:create_jobs.cleanup_client_messages##
\Application\DeskPRO\App::getOrm()->beginTransaction();
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'cleanup_client_messages';
$j['worker_group'] = 'cleanup';
$j['title'] = 'Cleanup Client Messages';
$j['description'] = 'Cleanup expired client polling messages';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\CleanupClientMessages';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\CleanupClientMessages::DEFAULT_INTERVAL;
\Application\DeskPRO\App::getOrm()->persist($j);
\Application\DeskPRO\App::getOrm()->flush();
\Application\DeskPRO\App::getOrm()->commit();

##BEGIN:create_jobs.cleanup_sessions##
\Application\DeskPRO\App::getOrm()->beginTransaction();
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'cleanup_sessions';
$j['worker_group'] = 'cleanup';
$j['title'] = 'Cleanup Client Messages';
$j['description'] = 'Cleanup expired sessions';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\CleanupSessions';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\CleanupSessions::DEFAULT_INTERVAL;
\Application\DeskPRO\App::getOrm()->persist($j);
\Application\DeskPRO\App::getOrm()->flush();
\Application\DeskPRO\App::getOrm()->commit();

##BEGIN:create_jobs.ensure_search_tables##
\Application\DeskPRO\App::getOrm()->beginTransaction();
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'ensure_search_tables';
$j['worker_group'] = 'ensure_search_tables';
$j['title'] = 'Ensure Search Tables';
$j['description'] = 'Checks to make sure volatile search tables are fileld (i.e., in event of a reboot they are re-filled)';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\EnsureSearchTables';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\EnsureSearchTables::DEFAULT_INTERVAL;
\Application\DeskPRO\App::getOrm()->persist($j);
\Application\DeskPRO\App::getOrm()->flush();
\Application\DeskPRO\App::getOrm()->commit();

##BEGIN:create_jobs.sendmail_queue##
\Application\DeskPRO\App::getOrm()->beginTransaction();
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'sendmail_queue';
$j['worker_group'] = 'sendmail_queue';
$j['title'] = 'Sendmail Queue';
$j['description'] = 'Attempts to send queued mail, or re-send fail mail';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\SendmailQueue';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\SendmailQueue::DEFAULT_INTERVAL;
\Application\DeskPRO\App::getOrm()->persist($j);
\Application\DeskPRO\App::getOrm()->flush();
\Application\DeskPRO\App::getOrm()->commit();

##BEGIN:create_jobs.hard_delete_tickets##
\Application\DeskPRO\App::getOrm()->beginTransaction();
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'hard_delete_tickets';
$j['worker_group'] = 'hard_delete_tickets';
$j['title'] = 'Hard Delete Tickets';
$j['description'] = 'Processes tickets that were soft-deleted long ago and permanantly deletes them';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\HardDeleteTickets';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\HardDeleteTickets::DEFAULT_INTERVAL;
\Application\DeskPRO\App::getOrm()->persist($j);
\Application\DeskPRO\App::getOrm()->flush();
\Application\DeskPRO\App::getOrm()->commit();