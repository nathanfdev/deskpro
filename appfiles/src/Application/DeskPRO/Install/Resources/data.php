<?php
##BEGIN:create_queue.agent##
\Application\DeskPRO\App::getOrm()->beginTransaction();
$q = new \Application\DeskPRO\Entity\TicketQueue();
$q['order_by']   = 'ticket.urgency:desc';
$q['person']     = null;
$q['title']      = 'Your Tickets';
$q['is_enabled'] = true;
$q['is_global']  = true;
$q['sys_name']   = 'agent';
$q['terms']      = array(array(
		'rule_type' => 'agent',
		'op'        => 'is',
		'agent'     => '-1',
	),
	array(
		'rule_type' => 'status',
		'op'        => 'is',
		'status'    => 'open',
	)
);
\Application\DeskPRO\App::getOrm()->persist($q);
\Application\DeskPRO\App::getOrm()->flush();
\Application\DeskPRO\App::getOrm()->commit();

##BEGIN:create_queue.agent_team##
\Application\DeskPRO\App::getOrm()->beginTransaction();
$q = new \Application\DeskPRO\Entity\TicketQueue();
$q['order_by']   = 'ticket.urgency:desc';
$q['person']     = null;
$q['title']      = 'Your Teams Tickets';
$q['is_enabled'] = true;
$q['is_global']  = true;
$q['sys_name']   = 'agent_team';
$q['terms']      = array(array(
		'rule_type'  => 'agent_team',
		'op'         => 'is',
		'agent_team' => '-1',
	),
	array(
		'rule_type' => 'status',
		'op'        => 'is',
		'status'    => 'open',
	)
);
\Application\DeskPRO\App::getOrm()->persist($q);
\Application\DeskPRO\App::getOrm()->flush();
\Application\DeskPRO\App::getOrm()->commit();

##BEGIN:create_queue.participant##
\Application\DeskPRO\App::getOrm()->beginTransaction();
$q = new \Application\DeskPRO\Entity\TicketQueue();
$q['order_by']   = 'ticket.urgency:desc';
$q['person']     = null;
$q['title']      = 'You\'re a Participant';
$q['is_enabled'] = true;
$q['is_global']  = true;
$q['sys_name']   = 'participant';
$q['terms']      = array(array(
		'rule_type' => 'participant',
		'op'        => 'is',
		'agent'     => '-1',
	),
	array(
		'rule_type' => 'status',
		'op'        => 'is',
		'status'    => 'open',
	)
);
\Application\DeskPRO\App::getOrm()->persist($q);
\Application\DeskPRO\App::getOrm()->flush();
\Application\DeskPRO\App::getOrm()->commit();

##BEGIN:create_queue.unassigned##
\Application\DeskPRO\App::getOrm()->beginTransaction();
$q = new \Application\DeskPRO\Entity\TicketQueue();
$q['order_by']   = 'ticket.urgency:desc';
$q['person']     = null;
$q['title']      = 'Unassigned';
$q['is_enabled'] = true;
$q['is_global']  = true;
$q['sys_name']   = 'unassigned';
$q['terms']      = array(array(
		'rule_type' => 'agent',
		'op'        => 'is',
		'agent'     => '0',
	),
	array(
		'rule_type' => 'status',
		'op'        => 'is',
		'status'    => 'open',
	)
);
\Application\DeskPRO\App::getOrm()->persist($q);
\Application\DeskPRO\App::getOrm()->flush();
\Application\DeskPRO\App::getOrm()->commit();

##BEGIN:create_queue.all##
\Application\DeskPRO\App::getOrm()->beginTransaction();
$q = new \Application\DeskPRO\Entity\TicketQueue();
$q['order_by']   = 'ticket.urgency:desc';
$q['person']     = null;
$q['title']      = 'All';
$q['is_enabled'] = true;
$q['is_global']  = true;
$q['sys_name']   = 'all';
$q['terms']      = array(
	array(
		'rule_type' => 'status',
		'op'        => 'is',
		'status'    => 'open',
	)
);
\Application\DeskPRO\App::getOrm()->persist($q);
\Application\DeskPRO\App::getOrm()->flush();
\Application\DeskPRO\App::getOrm()->commit();

##BEGIN:create_style.master##
\Application\DeskPRO\App::getOrm()->beginTransaction();
$s = new \Application\DeskPRO\Entity\Style();
$s['title'] = 'Default';
$s['note'] = '';
\Application\DeskPRO\App::getOrm()->persist($s);
\Application\DeskPRO\App::getOrm()->flush();
\Application\DeskPRO\App::getOrm()->commit();