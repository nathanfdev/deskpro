<?php if (!defined('DP_ROOT')) exit('No access');

################################################################################
# Language
################################################################################

##BEGIN:locale.language##
$l = new \Application\DeskPRO\Entity\Language();
$l['title'] = $translate->phrase('user.defaults.language_english');
$l['locale'] = 'en_US';
$l['sys_name'] = 'default';
$l['lang_code'] = 'eng';
$l['language_package'] = 'DeskproLanguages\\LangPackage';
$em->persist($l);
$em->flush();


################################################################################
# Departments
################################################################################

##BEGIN:create_department.department2##
if (!$IMPORT_INSTALL) {
	$q = new \Application\DeskPRO\Entity\Department();
	$q['title'] = $translate->phrase('user.defaults.department_support');
	$q['is_tickets_enabled'] = true;
	$q['is_chat_enabled'] = true;
	$em->persist($q);
	$em->flush();
}

##BEGIN:create_department.department1##
if (!$IMPORT_INSTALL) {
	$q = new \Application\DeskPRO\Entity\Department();
	$q['title'] = $translate->phrase('user.defaults.department_sales');
	$q['is_tickets_enabled'] = true;
	$q['is_chat_enabled'] = true;
	$em->persist($q);
	$em->flush();
}

################################################################################
# KB
################################################################################

##BEGIN:create_article.default##
if (!$IMPORT_INSTALL) {
	$DEFAULT_ARTICLE_CAT = new \Application\DeskPRO\Entity\ArticleCategory();
	$DEFAULT_ARTICLE_CAT['title'] = $translate->phrase('user.defaults.article_category_general');
	$em->persist($DEFAULT_ARTICLE_CAT);
	$em->flush();

	$DEFAULT_ARTICLE = new \Application\DeskPRO\Entity\Article();
	$DEFAULT_ARTICLE->person = $AGENT;
	$DEFAULT_ARTICLE->title = $translate->phrase('user.defaults.article_example_title');
	$DEFAULT_ARTICLE->content = $translate->phrase('user.defaults.article_example_content');
	$DEFAULT_ARTICLE->status = 'published';
	$DEFAULT_ARTICLE->addToCategory($DEFAULT_ARTICLE_CAT);
	$em->persist($DEFAULT_ARTICLE);
	$em->flush();
}


################################################################################
# Downloads
################################################################################

##BEGIN:create_download_cat.default##
if (!$IMPORT_INSTALL) {
	$q = new \Application\DeskPRO\Entity\DownloadCategory();
	$q['title'] = $translate->phrase('user.defaults.downloads_category_general');
	$em->persist($q);
	$em->flush();
}


################################################################################
# News
################################################################################

##BEGIN:create_news.default##
if (!$IMPORT_INSTALL) {
	$DEFAULT_NEWS_CAT = new \Application\DeskPRO\Entity\NewsCategory();
	$DEFAULT_NEWS_CAT['title'] = $translate->phrase('user.defaults.news_category_general');
	$em->persist($DEFAULT_NEWS_CAT);
	$em->flush();

	$DEFAULT_NEWS = new \Application\DeskPRO\Entity\News();
	$DEFAULT_NEWS->person = $AGENT;
	$DEFAULT_NEWS->title = $translate->phrase('user.defaults.news_example_title');
	$DEFAULT_NEWS->content = $translate->phrase('user.defaults.news_example_content');
	$DEFAULT_NEWS->status = 'published';
	$DEFAULT_NEWS->category = $DEFAULT_NEWS_CAT;
	$em->persist($DEFAULT_NEWS);
	$em->flush();
}


################################################################################
# Feedback
################################################################################

##BEGIN:create_feedback.default##
$DEFAULT_IDEA_CAT = new \Application\DeskPRO\Entity\FeedbackCategory();
$DEFAULT_IDEA_CAT['title'] = $translate->phrase('user.defaults.feedback_type_suggestion');
$em->persist($DEFAULT_IDEA_CAT);
$em->flush();

$cat = new \Application\DeskPRO\Entity\FeedbackCategory();
$cat['title'] = $translate->phrase('user.defaults.feedback_type_feature-request');
$em->persist($cat);
$em->flush();

$cat = new \Application\DeskPRO\Entity\FeedbackCategory();
$cat['title'] = $translate->phrase('user.defaults.feedback_type_bug-report');
$em->persist($cat);
$em->flush();

// Statuses are done as part of FeedbackCatsStep so we can map id's
if (!$IMPORT_INSTALL) {
	foreach (array('planning', 'started', 'under-review') as $t) {
		$s = new \Application\DeskPRO\Entity\FeedbackStatusCategory();
		$s->status_type = 'active';
		$s->title = $translate->phrase('user.defaults.feedback_status_' . $t);
		$em->persist($s);
	}

	foreach (array('completed', 'duplicate', 'declined') as $t) {
		$s = new \Application\DeskPRO\Entity\FeedbackStatusCategory();
		$s->status_type = 'closed';
		$s->title = $translate->phrase('user.defaults.feedback_status_' . $t);
		$em->persist($s);
	}
	$em->flush();
}

if (!$IMPORT_INSTALL) {
	$DEFAULT_IDEA = new \Application\DeskPRO\Entity\Feedback();
	$DEFAULT_IDEA->person = $AGENT;
	$DEFAULT_IDEA->title = $translate->phrase('user.defaults.feedback_example_title');
	$DEFAULT_IDEA->content = $translate->phrase('user.defaults.feedback_example_content');
	$DEFAULT_IDEA->status = 'new';
	$DEFAULT_IDEA->category = $DEFAULT_IDEA_CAT;
	$em->persist($DEFAULT_IDEA);
	$em->flush();
}

################################################################################
# Filters
################################################################################

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


##BEGIN:create_filter.archive_awaiting_user##
$q = new \Application\DeskPRO\Entity\TicketFilter();
$q['order_by']   = 'ticket.date_created:desc';
$q['person']     = null;
$q['title']      = 'Awaiting User';
$q['is_enabled'] = true;
$q['is_global']  = true;
$q['sys_name']   = 'archive_awaiting_user';
$q['terms']      = array(
	array(
		'type'      => 'status',
		'op'        => 'is',
		'options'   => array('status'    => 'awaiting_user',)
	)
);
$em->persist($q);
$em->flush();

##BEGIN:create_filter.archive_resolved##
$q = new \Application\DeskPRO\Entity\TicketFilter();
$q['order_by']   = 'ticket.date_created:desc';
$q['person']     = null;
$q['title']      = 'Resolved';
$q['is_enabled'] = true;
$q['is_global']  = true;
$q['sys_name']   = 'archive_resolved';
$q['terms']      = array(
	array(
		'type'      => 'status',
		'op'        => 'is',
		'options'   => array('status'    => 'resolved',)
	)
);
$em->persist($q);
$em->flush();

##BEGIN:create_filter.archive_closed##
$q = new \Application\DeskPRO\Entity\TicketFilter();
$q['order_by']   = 'ticket.date_created:desc';
$q['person']     = null;
$q['title']      = 'Resolved';
$q['is_enabled'] = true;
$q['is_global']  = true;
$q['sys_name']   = 'archive_closed';
$q['terms']      = array(
	array(
		'type'      => 'status',
		'op'        => 'is',
		'options'   => array('status'    => 'closed',)
	)
);
$em->persist($q);
$em->flush();

##BEGIN:create_filter.archive_validating##
$q = new \Application\DeskPRO\Entity\TicketFilter();
$q['order_by']   = 'ticket.date_created:desc';
$q['person']     = null;
$q['title']      = 'Awaiting Validation';
$q['is_enabled'] = true;
$q['is_global']  = true;
$q['sys_name']   = 'archive_validating';
$q['terms']      = array(
	array(
		'type'      => 'status',
		'op'        => 'is',
		'options'   => array('status'    => 'hidden.validating')
	)
);
$em->persist($q);
$em->flush();

##BEGIN:create_filter.archive_spam##
$q = new \Application\DeskPRO\Entity\TicketFilter();
$q['order_by']   = 'ticket.date_created:desc';
$q['person']     = null;
$q['title']      = 'Spam';
$q['is_enabled'] = true;
$q['is_global']  = true;
$q['sys_name']   = 'archive_spam';
$q['terms']      = array(
	array(
		'type'      => 'status',
		'op'        => 'is',
		'options'   => array('status'    => 'hidden.spam')
	)
);
$em->persist($q);
$em->flush();

##BEGIN:create_filter.archive_deleted##
$q = new \Application\DeskPRO\Entity\TicketFilter();
$q['order_by']   = 'ticket.date_created:desc';
$q['person']     = null;
$q['title']      = 'Deleted';
$q['is_enabled'] = true;
$q['is_global']  = true;
$q['sys_name']   = 'archive_deleted';
$q['terms']      = array(
	array(
		'type'      => 'status',
		'op'        => 'is',
		'options'   => array('status'    => 'hidden.deleted')
	)
);
$em->persist($q);
$em->flush();

################################################################################
# Triggers
################################################################################

##BEGIN:create_trigger.email_validation_web##
$q = new \Application\DeskPRO\Entity\TicketTrigger();
$q->title = 'email_validation.email';
$q->sys_name = 'email_validation.email';
$q->event_trigger = 'new_ticket';
$q->is_enabled = 0;
$q->terms = array(
	array(
		'type' => 'is_new_user',
		'op' => 'is',
		'options' => array('is_new_user' => '1'),
	),
	array(
		'type' => 'creation_system',
		'op' => 'is',
		'options' => array('creation_system' => 'gateway.person'),
	),
);
$q->actions = array(
	array (
		'type' => 'force_email_validation',
		'options' => array('force_email_validation' => '1'),
	)
);

$em->persist($q);
$em->flush();

##BEGIN:create_trigger.email_validation_email##
$q = new \Application\DeskPRO\Entity\TicketTrigger();
$q->title = 'email_validation.web';
$q->sys_name = 'email_validation.web';
$q->event_trigger = 'new_ticket';
$q->is_enabled = 0;
$q->terms = array(
	array(
		'type' => 'is_new_user',
		'op' => 'is',
		'options' => array('is_new_user' => '1'),
	),
	array(
		'type' => 'creation_system',
		'op' => 'is',
		'options' => array('creation_system' => 'web.person'),
	),
);
$q->actions = array(
	array (
		'type' => 'force_email_validation',
		'options' => array('force_email_validation' => '1'),
	)
);

$em->persist($q);
$em->flush();

##BEGIN:create_trigger.email_validation_widget##
$q = new \Application\DeskPRO\Entity\TicketTrigger();
$q->title = 'email_validation.widget';
$q->sys_name = 'email_validation.widget';
$q->event_trigger = 'new_ticket';
$q->is_enabled = 0;
$q->terms = array(
	array(
		'type' => 'is_new_user',
		'op' => 'is',
		'options' => array('is_new_user' => '1'),
	),
	array(
		'type' => 'creation_system',
		'op' => 'is',
		'options' => array('creation_system' => 'widget'),
	),
);
$q->actions = array(
	array (
		'type' => 'force_email_validation',
		'options' => array('force_email_validation' => '1'),
	)
);

$em->persist($q);
$em->flush();


##BEGIN:create_trigger.urgency_up1##
$q = new \Application\DeskPRO\Entity\TicketTrigger();
$q->title = '';
$q->event_trigger = 'time.user_waiting';
$q->setEventTriggerOption('time', '1 days');
$q->is_enabled = 0;
$q->terms = array();
$q->actions = array(
	array(
		'type' => 'urgency_set',
		'options' => array(
			'num' => 2
		)
	)
);

$em->persist($q);
$em->flush();

##BEGIN:create_trigger.urgency_up2##
$q = new \Application\DeskPRO\Entity\TicketTrigger();
$q->title = '';
$q->event_trigger = 'time.user_waiting';
$q->setEventTriggerOption('time', '2 days');
$q->is_enabled = 1;
$q->terms = array();
$q->actions = array(
	array(
		'type' => 'urgency_set',
		'options' => array(
			'num' => 3
		)
	)
);

$em->persist($q);
$em->flush();

##BEGIN:create_trigger.urgency_up3##
$q = new \Application\DeskPRO\Entity\TicketTrigger();
$q->title = '';
$q->event_trigger = 'time.user_waiting';
$q->setEventTriggerOption('time', '3 days');
$q->is_enabled = 1;
$q->terms = array();
$q->actions = array(
	array(
		'type' => 'urgency_set',
		'options' => array(
			'num' => 3
		)
	)
);

$em->persist($q);
$em->flush();

##BEGIN:create_trigger.auto_close_resolve_user_reply##
// When a ticket has been awaiting agent for 2 months, set it to resolved
$q = new \Application\DeskPRO\Entity\TicketTrigger();
$q->title = 'auto_close.resolve_user_reply';
$q->event_trigger = 'time.user_waiting';
$q->setEventTriggerOption('time', '2 months');
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
		'type' => 'status',
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
$q->event_trigger = 'time.agent_waiting';
$q->setEventTriggerOption('time', '5 days');
$q->is_enabled = 0;
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
		'type' => 'status',
		'options' => array(
			'status' => 'resolved'
		)
	)
);

$em->persist($q);
$em->flush();

##BEGIN:create_trigger.warn_auto_close##
// When a ticket has been awaiting user for 3 days, warn the user it will be closed
$q = new \Application\DeskPRO\Entity\TicketTrigger();
$q->title = 'auto_close.warn_user';
$q->event_trigger = 'time.agent_waiting';
$q->setEventTriggerOption('time', '3 days');
$q->is_enabled = 0;
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
		'type' => 'send_ticket_email',
		'options' => array(
			'message' => $translate->phrase('user.defaults.trigger_warn_autoclose')
		)
	)
);

$em->persist($q);
$em->flush();

##BEGIN:create_trigger.enable_autoreply_gateway##
$q = new \Application\DeskPRO\Entity\TicketTrigger();
$q->title = 'response.reply_confirm';
$q->sys_name = 'response.reply_confirm';
$q->event_trigger = 'update.user';
$q->is_enabled = 0;
$q->terms = array(
	array(
		'type' => 'new_reply_user',
		'op' => 'is',
		'options' => array('do' => '1')
	)
);
$q->actions = array(
	array(
		'type' => 'enable_user_notification_new_reply_user',
		'options' => array ('enable' => '1')
	)
);

$em->persist($q);
$em->flush();

##BEGIN:create_trigger.default_set##
$em->getConnection()->exec("
INSERT INTO `ticket_triggers` (`id`, `sys_name`, `title`, `event_trigger`, `is_enabled`, `terms`, `actions`, `run_order`, `date_created`, `event_trigger_options`, `terms_any`)
VALUES
	(NULL, 'setdep.newemail_user', '', 'new.email.user', 1, 'a:0:{}', 'a:1:{i:0;a:2:{s:4:\"type\";s:10:\"department\";s:7:\"options\";a:1:{s:10:\"department\";s:13:\"email_account\";}}}', 0, '2012-10-22 19:56:52', 'a:0:{}', 'a:0:{}'),
	(NULL, 'setgateway.newweb_user', '', 'new.web.user', 1, 'a:0:{}', 'a:1:{i:0;a:2:{s:4:\"type\";s:19:\"set_gateway_address\";s:7:\"options\";a:1:{s:18:\"gateway_address_id\";s:10:\"department\";}}}', 0, '2012-10-22 19:57:20', 'a:0:{}', 'a:0:{}'),
	(NULL, 'setdep.newemail_agent', '', 'new.email.agent', 1, 'a:0:{}', 'a:1:{i:0;a:2:{s:4:\"type\";s:10:\"department\";s:7:\"options\";a:1:{s:10:\"department\";s:13:\"email_account\";}}}', 0, '2012-10-22 19:57:36', 'a:0:{}', 'a:0:{}'),
	(NULL, 'setgateway.newweb_agent', '', 'new.web.agent.portal', 1, 'a:0:{}', 'a:1:{i:0;a:2:{s:4:\"type\";s:19:\"set_gateway_address\";s:7:\"options\";a:1:{s:18:\"gateway_address_id\";s:10:\"department\";}}}', 0, '2012-10-22 19:59:50', 'a:0:{}', 'a:0:{}'),
	(NULL, 'setgateway.update_agent', '', 'update.agent', 1, 'a:1:{i:0;a:3:{s:4:\"type\";s:10:\"department\";s:2:\"op\";s:7:\"changed\";s:7:\"options\";a:1:{s:10:\"department\";s:1:\"1\";}}}', 'a:1:{i:0;a:2:{s:4:\"type\";s:19:\"set_gateway_address\";s:7:\"options\";a:1:{s:18:\"gateway_address_id\";s:10:\"department\";}}}', 0, '2012-10-22 20:00:25', 'a:0:{}', 'a:0:{}'),
	(NULL, 'setgateway.update_user', '', 'update.user', 1, 'a:1:{i:0;a:3:{s:4:\"type\";s:10:\"department\";s:2:\"op\";s:7:\"changed\";s:7:\"options\";a:1:{s:10:\"department\";s:1:\"1\";}}}', 'a:1:{i:0;a:2:{s:4:\"type\";s:19:\"set_gateway_address\";s:7:\"options\";a:1:{s:18:\"gateway_address_id\";s:10:\"department\";}}}', 0, '2012-10-22 20:03:33', 'a:0:{}', 'a:0:{}'),
	(NULL, 'setfrom.reply_agent', '', 'update.agent', 1, 'a:0:{}', 'a:1:{i:0;a:2:{s:4:\"type\";s:21:\"set_initial_from_name\";s:7:\"options\";a:2:{s:9:\"from_name\";s:18:\"{{performer.name}}\";s:7:\"to_whom\";s:1:\"0\";}}}', 0, '2012-10-24 14:10:26', 'a:0:{}', 'a:0:{}'),
	(NULL, 'setfrom.reply_user', '', 'update.user', 1, 'a:0:{}', 'a:1:{i:0;a:2:{s:4:\"type\";s:21:\"set_initial_from_name\";s:7:\"options\";a:2:{s:9:\"from_name\";s:18:\"{{performer.name}}\";s:7:\"to_whom\";s:1:\"0\";}}}', 0, '2012-10-24 14:12:46', 'a:0:{}', 'a:0:{}'),
	(NULL, 'setfrom.newemail_user', '', 'new.email.user', 1, 'a:0:{}', 'a:1:{i:0;a:2:{s:4:\"type\";s:21:\"set_initial_from_name\";s:7:\"options\";a:2:{s:9:\"from_name\";s:18:\"{{performer.name}}\";s:7:\"to_whom\";s:5:\"agent\";}}}', 0, '2012-10-24 14:14:05', 'a:0:{}', 'a:0:{}'),
	(NULL, 'setfrom.newweb_user', '', 'new.web.user', 1, 'a:0:{}', 'a:1:{i:0;a:2:{s:4:\"type\";s:21:\"set_initial_from_name\";s:7:\"options\";a:2:{s:9:\"from_name\";s:18:\"{{performer.name}}\";s:7:\"to_whom\";s:5:\"agent\";}}}', 0, '2012-10-24 14:14:05', 'a:0:{}', 'a:0:{}'),
	(NULL, 'setfrom.newemail_agent', '', 'new.email.agent', 1, 'a:0:{}', 'a:1:{i:0;a:2:{s:4:\"type\";s:21:\"set_initial_from_name\";s:7:\"options\";a:2:{s:9:\"from_name\";s:18:\"{{performer.name}}\";s:7:\"to_whom\";s:1:\"0\";}}}', 0, '2012-10-24 14:14:05', 'a:0:{}', 'a:0:{}'),
	(NULL, 'setfrom.newweb_agent', '', 'new.web.agent.portal', 1, 'a:0:{}', 'a:1:{i:0;a:2:{s:4:\"type\";s:21:\"set_initial_from_name\";s:7:\"options\";a:2:{s:9:\"from_name\";s:18:\"{{performer.name}}\";s:7:\"to_whom\";s:1:\"0\";}}}', 0, '2012-10-24 14:14:05', 'a:0:{}', 'a:0:{}');
");

##BEGIN:create_style.master##

$s = new \Application\DeskPRO\Entity\Style();
$s['title'] = $translate->phrase('agent.defaults.default_style');
$s['note'] = $translate->phrase('agent.defaults.default_style');
$s['css_dir'] = 'stylesheets/user';
$em->persist($s);
$em->flush();


################################################################################
# Cron Jobs
################################################################################

##BEGIN:create_jobs.archive_tickets##
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'archive_tickets';
$j['worker_group'] = 'archive_tickets';
$j['title'] = 'Archive Tickets';
$j['description'] = 'Archives old tickets';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\ArchiveTickets';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\ArchiveTickets::DEFAULT_INTERVAL;
$em->persist($j);
$em->flush();


##BEGIN:create_jobs.article_publish_state##
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'article_publish_state';
$j['worker_group'] = 'article_publish_state';
$j['title'] = 'Article Publish State';
$j['description'] = 'Goes through articles with a publish date that was set in the future (publish now), or an end date set (deleting or archivng now).';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\ArticlePublishState';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\ArticlePublishState::DEFAULT_INTERVAL;
$em->persist($j);
$em->flush();


##BEGIN:create_jobs.chat_ping_timeout##
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'chat_ping_timeout';
$j['worker_group'] = 'chat';
$j['title'] = 'Chat Ping Timeout';
$j['description'] = 'Timesout chats where both parties are not longer participating';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\ChatPingTimeout';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\ChatPingTimeout::DEFAULT_INTERVAL;
$em->persist($j);
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


##BEGIN:create_jobs.cleanup_stats##
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'cleanup_stats';
$j['worker_group'] = 'cleanup';
$j['title'] = 'Cleanup Stats';
$j['description'] = 'Cleanup stat records that are outside of the reporting scope';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\CleanupStats';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\CleanupStats::DEFAULT_INTERVAL;
$em->persist($j);
$em->flush();


##BEGIN:create_jobs.cleanup_sendmail##
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'cleanup_sendmail';
$j['worker_group'] = 'cleanup';
$j['title'] = 'Chat Ping Timeout';
$j['description'] = 'Cleans up old logged copies of sent mail';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\CleanupSendmail';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\CleanupSendmail::DEFAULT_INTERVAL;
$em->persist($j);
$em->flush();


##BEGIN:create_jobs.sitemap_file##
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'sitemap_file';
$j['worker_group'] = 'sitemap_file';
$j['title'] = 'Generate Sitemap';
$j['description'] = 'Generates the sitemap.xml file';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\SitemapFile';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\SitemapFile::DEFAULT_INTERVAL;
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


##BEGIN:create_jobs.cleanup_tmp_data##
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'cleanup_tmp_data';
$j['worker_group'] = 'cleanup';
$j['title'] = 'Cleanup Temporary Data';
$j['description'] = 'Cleanup temporary data';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\CleanupTmpData';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\CleanupTmpData::DEFAULT_INTERVAL;
$em->persist($j);
$em->flush();


##BEGIN:create_jobs.cleanup_tmp_attach##
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'cleanup_tmp_attach';
$j['worker_group'] = 'cleanup';
$j['title'] = 'Cleanup Temporary Attachments';
$j['description'] = 'Cleanup temporary attachments';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\CleanupTmpAttach';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\CleanupTmpAttach::DEFAULT_INTERVAL;
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


##BEGIN:create_jobs.generate_stats##
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'generate_stats';
$j['worker_group'] = 'stats';
$j['title'] = 'Generates Stats';
$j['description'] = 'Generates statistical data for reports';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\GenerateStats';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\GenerateStats::DEFAULT_INTERVAL;
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


##BEGIN:create_jobs.search_index_update##
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'search_index_update';
$j['worker_group'] = 'search';
$j['title'] = 'Search Index Update';
$j['description'] = 'Updates the search index with updated objects';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\SearchIndexUpdate';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\SearchIndexUpdate::DEFAULT_INTERVAL;
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

##BEGIN:create_jobs.heartbeat##
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'heartbeat';
$j['worker_group'] = 'heartbeat';
$j['title'] = 'Heartbeat';
$j['description'] = 'Send heartbeat ping';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\Heartbeat';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\Heartbeat::DEFAULT_INTERVAL;
$em->persist($j);
$em->flush();

##BEGIN:create_jobs.move_blobs##
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'move_blobs';
$j['worker_group'] = 'move_blobs';
$j['title'] = 'Move Blobs';
$j['description'] = 'When the storage mechanism is changed, this job moves existing blobs to the new mechanism a bit at a time';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\MoveBlobs';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\MoveBlobs::DEFAULT_INTERVAL;
$em->persist($j);
$em->flush();


##BEGIN:create_jobs.process_email_gateways##
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'process_email_gateways';
$j['worker_group'] = 'process_email_gateways';
$j['title'] = 'Process Email Gateways';
$j['description'] = 'Runs through the email gateways and processes new messages';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\ProcessEmailGateways';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\ProcessEmailGateways::DEFAULT_INTERVAL;
$em->persist($j);
$em->flush();


##BEGIN:create_jobs.delete_spam_tickets##
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'delete_spam_tickets';
$j['worker_group'] = 'delete_spam_tickets';
$j['title'] = 'Delete Spam Tickets';
$j['description'] = 'Runs through old spammed tickets and deletes them';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\DeleteSpamTickets';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\DeleteSpamTickets::DEFAULT_INTERVAL;
$em->persist($j);
$em->flush();


##BEGIN:create_jobs.agent_mode_ticket_reasssign##
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'agent_mode_ticket_reasssign';
$j['worker_group'] = 'agent_mode_ticket_reasssign';
$j['title'] = 'Reassign tickets of vacation or deleted agents';
$j['description'] = 'When an agent enters vacation mode or is deleted, we need to batch-update their tickets to unassigned';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\AgentModeTicketReassign';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\AgentModeTicketReassign::DEFAULT_INTERVAL;
$em->persist($j);
$em->flush();


##BEGIN:create_jobs.ticket_triggers##
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'ticket_triggers';
$j['worker_group'] = 'ticket_triggers';
$j['title'] = 'Ticket Triggers';
$j['description'] = 'Executes time-based ticket triggers';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\TicketTriggers';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\TicketTriggers::DEFAULT_INTERVAL;
$em->persist($j);
$em->flush();

##BEGIN:create_jobs.article_publish_state##
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'update_view_counts';
$j['worker_group'] = 'update_view_counts';
$j['title'] = 'Update View Counts';
$j['description'] = 'Updates view counts on objects';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\UpdateViewCounts';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\UpdateViewCounts::DEFAULT_INTERVAL;
$em->persist($j);
$em->flush();

##BEGIN:create_jobs.run_queued_tasks##
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'run_queued_tasks';
$j['worker_group'] = 'run_queued_tasks';
$j['title'] = 'Run Queued Tasks';
$j['description'] = 'Runs any general-purpose queued tasks';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\RunQueuedTasks';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\RunQueuedTasks::DEFAULT_INTERVAL;
$em->persist($j);
$em->flush();

##BEGIN:create_jobs.cleanup_drafts##
$j = new \Application\DeskPRO\Entity\WorkerJob();
$j['id'] = 'cleanup_drafts';
$j['worker_group'] = 'cleanup';
$j['title'] = 'Cleanup Drafts';
$j['description'] = 'Cleans up old drafts';
$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\CleanupDrafts';
$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\CleanupDrafts::DEFAULT_INTERVAL;
$em->persist($j);
$em->flush();

################################################################################
# Portal Blocks
################################################################################

##BEGIN:create_portal_block.news##
$b = new \Application\DeskPRO\Entity\PortalPageDisplay();
$b->section = 'portal';
$b->type = 'news';
$b->is_enabled = true;
$em->persist($b);
$em->flush();

##BEGIN:create_portal_block.userinfo_sidebar##
$b = new \Application\DeskPRO\Entity\PortalPageDisplay();
$b->section = 'sidebar';
$b->type = 'userinfo';
$b->is_enabled = true;
$em->persist($b);
$em->flush();

##BEGIN:create_portal_block.kb_cat_list##
$b = new \Application\DeskPRO\Entity\PortalPageDisplay();
$b->section = 'sidebar';
$b->type = 'kb_cat_list';
$b->is_enabled = true;
$em->persist($b);
$em->flush();

##BEGIN:create_portal_block.feedback_cat_list##
$b = new \Application\DeskPRO\Entity\PortalPageDisplay();
$b->section = 'sidebar';
$b->type = 'feedback_cat_list';
$b->is_enabled = true;
$em->persist($b);
$em->flush();

##BEGIN:create_portal_block.downloads_cat_list##
$b = new \Application\DeskPRO\Entity\PortalPageDisplay();
$b->section = 'sidebar';
$b->type = 'downloads_cat_list';
$b->is_enabled = true;
$em->persist($b);
$em->flush();

##BEGIN:create_portal_block.staff_sidebar##
$b = new \Application\DeskPRO\Entity\PortalPageDisplay();
$b->section = 'sidebar';
$b->type = 'staff';
$b->is_enabled = true;
$em->persist($b);
$em->flush();


################################################################################
# Agent Teams
################################################################################

##BEGIN:agent_teams.default1##
$t = new \Application\DeskPRO\Entity\AgentTeam();
$t['name'] = $translate->phrase('agent.defaults.team_support_managers');
$em->persist($t);
$em->flush();

##BEGIN:agent_teams.default2##
$t = new \Application\DeskPRO\Entity\AgentTeam();
$t['name'] = $translate->phrase('agent.defaults.team_lvl1_support');
$em->persist($t);
$em->flush();

##BEGIN:agent_teams.default3##
$t = new \Application\DeskPRO\Entity\AgentTeam();
$t['name'] = $translate->phrase('agent.defaults.team_lvl2_support');
$em->persist($t);
$em->flush();

##BEGIN:agent_teams.setting##
$t = new \Application\DeskPRO\Entity\Setting();
$t['name'] = 'core.use_agent_team';
$t['value'] = '1';
$em->persist($t);
$em->flush();

################################################################################
# Usergroups
################################################################################

##BEGIN:usergroups.everyone##
$g = new \Application\DeskPRO\Entity\Usergroup();
$g['title'] = $translate->phrase('agent.defaults.usergroup_everyone');
$g['note'] = $translate->phrase('agent.defaults.usergroup_everyone_note');
$g['sys_name'] = \Application\DeskPRO\Entity\Usergroup::EVERYONE_NAME;
$em->persist($g);
$em->flush();
$USERGROUP_EVERYONE = $g;

##BEGIN:usergroups.register##
$g = new \Application\DeskPRO\Entity\Usergroup();
$g['title'] = $translate->phrase('agent.defaults.usergroup_registered');
$g['note'] = $translate->phrase('agent.defaults.usergroup_registered_note');
$g['sys_name'] = \Application\DeskPRO\Entity\Usergroup::REG_NAME;
$em->persist($g);
$em->flush();
$USERGROUP_REG = $g;

##BEGIN:usergroups.agent_all##
$AGENTGROUP_ALL = new \Application\DeskPRO\Entity\Usergroup();
$AGENTGROUP_ALL['title'] = $translate->phrase('agent.defaults.usergroup_agent_all_perms');
$AGENTGROUP_ALL['note'] = $translate->phrase('agent.defaults.usergroup_agent_all_perms_note');
$AGENTGROUP_ALL['is_agent_group'] = true;
$em->persist($AGENTGROUP_ALL);
$em->flush();


################################################################################
# Default base stats
################################################################################

$em->getConnection()->executeUpdate("
	INSERT INTO `report_dashboard` (`id`, `author_id`, `title`, `number_columns`, `disabled`, `date_created`, `display_order`)
	VALUES
		(1, NULL, '{$translate->phrase('agent.defaults.stat_dash_today')}', 4, 0, '2012-05-01 08:56:20', 20),
		(2, NULL, '{$translate->phrase('agent.defaults.stat_dash_monthly')}', 4, 0, '2012-05-01 09:09:42', 30),
		(3, NULL, '{$translate->phrase('agent.defaults.stat_dash_yearly')}', 4, 0, '2012-05-01 09:12:09', 40),
		(4, NULL, '{$translate->phrase('agent.defaults.stat_dash_backlog')}', 4, 0, '2012-05-01 09:15:22', 50),
		(5, NULL, '{$translate->phrase('agent.defaults.stat_dash_agent_performance')}', 4, 0, '2012-05-01 09:24:23', 60)
");

$em->getConnection()->executeUpdate("
	INSERT INTO `stat` (`id`, `author_id`, `parent_stat_id`, `title`, `criteria`, `grouping_ref`, `stat_concept_class`, `variation`, `generate_stats`, `disabled`, `run_frequency`, `last_run`, `date_created`)
	VALUES
		(1,  NULL, NULL, 'Tickets awaiting agent', X'613A303A7B7D', 'tickets.agent_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketsAwaitingAgent', 'bad', 1, 0, 'hourly', NULL, '2012-07-27 07:07:46'),
		(2,  NULL, NULL, 'Rate of tickets processed', X'613A303A7B7D', 'tickets.department_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\RateOfTicketsProcessed', 'good', 1, 0, 'hourly', NULL, '2012-07-27 07:08:22'),
		(3,  NULL, NULL, 'First response time', X'613A303A7B7D', 'tickets.agent_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketFirstResponseTime', 'bad', 1, 0, 'hourly', NULL, '2012-07-27 07:09:29'),
		(4,  NULL, NULL, 'Open tickets', X'613A303A7B7D', 'tickets.agent_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketsOpen', 'bad', 1, 0, 'hourly', NULL, '2012-07-27 07:10:06'),
		(5,  NULL, NULL, 'Open tickets by department', X'613A303A7B7D', 'tickets.department_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketsOpen', 'bad', 1, 0, 'hourly', NULL, '2012-07-27 07:11:07'),
		(6,  NULL, NULL, 'Open tickets by team', X'613A303A7B7D', 'tickets.agent_team_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketsOpen', 'bad', 1, 0, 'hourly', NULL, '2012-07-27 07:11:46'),
		(7,  NULL, NULL, 'Total user waiting time by agent', X'613A303A7B7D', 'tickets.agent_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TotalUserWaitingTicketResolvedTime', 'bad', 1, 0, 'hourly', NULL, '2012-07-27 07:12:32'),
		(8,  NULL, NULL, 'Open Tickets', X'613A303A7B7D', 'tickets.department_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketsOpen', 'bad', 1, 0, 'hourly', NULL, '2012-07-27 07:18:31'),
		(9,  NULL, NULL, 'Tickets awaiting agent', X'613A303A7B7D', 'tickets.agent_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketsAwaitingAgent', 'bad', 1, 0, 'daily', NULL, '2012-07-27 07:07:46'),
		(10, NULL, NULL, 'Rate of tickets processed', X'613A303A7B7D', 'tickets.department_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\RateOfTicketsProcessed', 'good', 1, 0, 'daily', NULL, '2012-07-27 07:08:22'),
		(11, NULL, NULL, 'First response time', X'613A303A7B7D', 'tickets.agent_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketFirstResponseTime', 'bad', 1, 0, 'daily', NULL, '2012-07-27 07:09:29'),
		(12, NULL, NULL, 'Open tickets', X'613A303A7B7D', 'tickets.agent_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketsOpen', 'bad', 1, 0, 'daily', NULL, '2012-07-27 07:10:06'),
		(13, NULL, NULL, 'Open tickets by department', X'613A303A7B7D', 'tickets.department_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketsOpen', 'bad', 1, 0, 'daily', NULL, '2012-07-27 07:11:07'),
		(14, NULL, NULL, 'Open tickets by team', X'613A303A7B7D', 'tickets.agent_team_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketsOpen', 'bad', 1, 0, 'daily', NULL, '2012-07-27 07:11:46'),
		(15, NULL, NULL, 'Total user waiting time by agent', X'613A303A7B7D', 'tickets.agent_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TotalUserWaitingTicketResolvedTime', 'bad', 1, 0, 'daily', NULL, '2012-07-27 07:12:32'),
		(16, NULL, NULL, 'Open Tickets', X'613A303A7B7D', 'tickets.department_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketsOpen', 'bad', 1, 0, 'daily', NULL, '2012-07-27 07:18:31'),
		(17, NULL, NULL, 'Tickets awaiting agent', X'613A303A7B7D', 'tickets.agent_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketsAwaitingAgent', 'bad', 1, 0, 'monthly', NULL, '2012-07-27 07:07:46'),
		(18, NULL, NULL, 'Rate of tickets processed', X'613A303A7B7D', 'tickets.department_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\RateOfTicketsProcessed', 'good', 1, 0, 'monthly', NULL, '2012-07-27 07:08:22'),
		(19, NULL, NULL, 'First response time', X'613A303A7B7D', 'tickets.agent_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketFirstResponseTime', 'bad', 1, 0, 'monthly', NULL, '2012-07-27 07:09:29'),
		(20, NULL, NULL, 'Open tickets', X'613A303A7B7D', 'tickets.agent_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketsOpen', 'bad', 1, 0, 'monthly', NULL, '2012-07-27 07:10:06'),
		(21, NULL, NULL, 'Open tickets by department', X'613A303A7B7D', 'tickets.department_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketsOpen', 'bad', 1, 0, 'monthly', NULL, '2012-07-27 07:11:07'),
		(22, NULL, NULL, 'Open tickets by team', X'613A303A7B7D', 'tickets.agent_team_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketsOpen', 'bad', 1, 0, 'monthly', NULL, '2012-07-27 07:11:46'),
		(23, NULL, NULL, 'Total user waiting time by agent', X'613A303A7B7D', 'tickets.agent_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TotalUserWaitingTicketResolvedTime', 'bad', 1, 0, 'monthly', NULL, '2012-07-27 07:12:32'),
		(24, NULL, NULL, 'Open Tickets', X'613A303A7B7D', 'tickets.department_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketsOpen', 'bad', 1, 0, 'monthly', NULL, '2012-07-27 07:18:31'),
		(25, NULL, NULL, 'Open Tickets', X'613A303A7B7D', 'tickets.agent_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketsOpen', 'bad', 1, 0, 'daily', NULL, '2012-07-27 07:31:03'),
		(26, NULL, NULL, 'Count of tickets awaiting agent this month', X'613A303A7B7D', 'tickets.agent_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketsAwaitingAgent', 'bad', 1, 0, 'daily', NULL, '2012-07-27 07:31:35'),
		(27, NULL, NULL, 'Count of tickets awaiting agent today', X'613A303A7B7D', 'tickets.agent_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketsAwaitingAgent', 'bad', 1, 0, 'hourly', NULL, '2012-07-27 07:32:30'),
		(28, NULL, NULL, 'New tickets created today', X'613A303A7B7D', 'tickets.department_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketsNew', 'bad', 1, 0, 'hourly', NULL, '2012-07-27 07:33:53'),
		(29, NULL, NULL, 'New tickets created this month', X'613A303A7B7D', 'tickets.department_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketsNew', 'bad', 1, 0, 'monthly', NULL, '2012-07-27 07:34:29'),
		(30, NULL, NULL, 'New Tickets', X'613A303A7B7D', 'tickets.department_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketsNew', 'bad', 1, 0, 'hourly', NULL, '2012-07-27 07:36:17')

");

$em->getConnection()->executeUpdate("
	INSERT INTO `report_dashboard_stat` (`id`, `report_dashboard_id`, `stat_id`, `title`, `view_class`, `grid_slots`, `grid_columns`, `grid_rows`, `slot_number`, `number_data_points`, `show_legend`, `display_grouping`, `date_created`)
	VALUES
		(1,  1, 1, 'Tickets awaiting agent by agent', 'Application\\\\ReportBundle\\\\Chart\\\\DeskPRO\\\\DetailedDrillDownChart', 1, 1, 1, 2, 24, 0, 0, '2012-07-27 07:07:46'),
		(2,  1, 2, 'Rate of tickets processed', 'Application\\\\ReportBundle\\\\Chart\\\\DeskPRO\\\\SimpleVariationChart', 1, 1, 1, 7, 24, 0, 0, '2012-07-27 07:08:22'),
		(3,  1, 3, 'First response time', 'Application\\\\ReportBundle\\\\Chart\\\\DeskPRO\\\\SimpleVariationChart', 1, 1, 1, 9, 24, 0, 0, '2012-07-27 07:09:29'),
		(4,  1, 4, 'Open tickets by agent', 'Application\\\\ReportBundle\\\\Chart\\\\DeskPRO\\\\DetailedDrillDownChart', 1, 1, 1, 3, 24, 0, 1, '2012-07-27 07:10:06'),
		(5,  1, 5, 'Open tickets by department', 'Application\\\\ReportBundle\\\\Chart\\\\DeskPRO\\\\DetailedDrillDownChart', 1, 1, 1, 6, 24, 0, 1, '2012-07-27 07:11:07'),
		(6,  1, 6, 'Open tickets by team', 'Application\\\\ReportBundle\\\\Chart\\\\DeskPRO\\\\DetailedDrillDownChart', 1, 1, 1, 10, 24, 0, 0, '2012-07-27 07:11:46'),
		(7,  1, 7, 'Total user waiting time by agent', 'Application\\\\ReportBundle\\\\Chart\\\\DeskPRO\\\\DetailedDrillDownChart', 1, 1, 1, 8, 24, 0, 1, '2012-07-27 07:12:32'),
		(8,  1, 8, 'Open Tickets', 'Application\\\\ReportBundle\\\\Chart\\\\AmChart\\\\ColumnChart', 2, 2, 2, 0, 24, 0, 1, '2012-07-27 07:18:31'),
		(9,  2, 9, 'Tickets awaiting agent by agent', 'Application\\\\ReportBundle\\\\Chart\\\\DeskPRO\\\\DetailedDrillDownChart', 1, 1, 1, 2, 7, 0, 0, '2012-07-27 07:07:46'),
		(10, 2, 10, 'Rate of tickets processed', 'Application\\\\ReportBundle\\\\Chart\\\\DeskPRO\\\\SimpleVariationChart', 1, 1, 1, 7, 7, 0, 0, '2012-07-27 07:08:22'),
		(11, 2, 11, 'First response time', 'Application\\\\ReportBundle\\\\Chart\\\\DeskPRO\\\\SimpleVariationChart', 1, 1, 1, 9, 7, 0, 0, '2012-07-27 07:09:29'),
		(12, 2, 12, 'Open tickets by agent', 'Application\\\\ReportBundle\\\\Chart\\\\DeskPRO\\\\DetailedDrillDownChart', 1, 1, 1, 3, 7, 0, 1, '2012-07-27 07:10:06'),
		(13, 2, 13, 'Open tickets by department', 'Application\\\\ReportBundle\\\\Chart\\\\DeskPRO\\\\DetailedDrillDownChart', 1, 1, 1, 6, 7, 0, 1, '2012-07-27 07:11:07'),
		(14, 2, 14, 'Open tickets by team', 'Application\\\\ReportBundle\\\\Chart\\\\DeskPRO\\\\DetailedDrillDownChart', 1, 1, 1, 10, 7, 0, 0, '2012-07-27 07:11:46'),
		(15, 2, 15, 'Total user waiting time by agent', 'Application\\\\ReportBundle\\\\Chart\\\\DeskPRO\\\\DetailedDrillDownChart', 1, 1, 1, 8, 7, 0, 1, '2012-07-27 07:12:32'),
		(16, 2, 16, 'Open Tickets', 'Application\\\\ReportBundle\\\\Chart\\\\AmChart\\\\ColumnChart', 2, 2, 2, 0, 7, 0, 1, '2012-07-27 07:18:31'),
		(17, 3, 17, 'Tickets awaiting agent by agent', 'Application\\\\ReportBundle\\\\Chart\\\\DeskPRO\\\\DetailedDrillDownChart', 1, 1, 1, 2, 12, 0, 0, '2012-07-27 07:07:46'),
		(18, 3, 18, 'Rate of tickets processed', 'Application\\\\ReportBundle\\\\Chart\\\\DeskPRO\\\\SimpleVariationChart', 1, 1, 1, 7, 12, 0, 0, '2012-07-27 07:08:22'),
		(19, 3, 19, 'First response time', 'Application\\\\ReportBundle\\\\Chart\\\\DeskPRO\\\\SimpleVariationChart', 1, 1, 1, 9, 12, 0, 0, '2012-07-27 07:09:29'),
		(20, 3, 20, 'Open tickets by agent', 'Application\\\\ReportBundle\\\\Chart\\\\DeskPRO\\\\DetailedDrillDownChart', 1, 1, 1, 3, 12, 0, 1, '2012-07-27 07:10:06'),
		(21, 3, 21, 'Open tickets by department', 'Application\\\\ReportBundle\\\\Chart\\\\DeskPRO\\\\DetailedDrillDownChart', 1, 1, 1, 6, 12, 0, 1, '2012-07-27 07:11:07'),
		(22, 3, 22, 'Open tickets by team', 'Application\\\\ReportBundle\\\\Chart\\\\DeskPRO\\\\DetailedDrillDownChart', 1, 1, 1, 10, 12, 0, 0, '2012-07-27 07:11:46'),
		(23, 3, 23, 'Total user waiting time by agent', 'Application\\\\ReportBundle\\\\Chart\\\\DeskPRO\\\\DetailedDrillDownChart', 1, 1, 1, 8, 12, 0, 1, '2012-07-27 07:12:32'),
		(24, 3, 24, 'Open Tickets', 'Application\\\\ReportBundle\\\\Chart\\\\AmChart\\\\ColumnChart', 2, 2, 2, 0, 12, 0, 1, '2012-07-27 07:18:31'),
		(26, 4, 26, 'Count of tickets awaiting agent this month', 'Application\\\\ReportBundle\\\\Chart\\\\AmChart\\\\StackedLineChart', 2, 2, 2, 2, 30, 0, 0, '2012-07-27 07:31:35'),
		(27, 4, 27, 'Count of tickets awaiting agent today', 'Application\\\\ReportBundle\\\\Chart\\\\AmChart\\\\StackedLineChart', 2, 2, 2, 0, 24, 0, 0, '2012-07-27 07:32:30'),
		(28, 4, 28, 'New tickets created today', 'Application\\\\ReportBundle\\\\Chart\\\\AmChart\\\\StackedLineChart', 1, 1, 1, 8, 24, 0, 0, '2012-07-27 07:33:53'),
		(29, 4, 29, 'New tickets created this month', 'Application\\\\ReportBundle\\\\Chart\\\\AmChart\\\\StackedLineChart', 1, 1, 1, 9, 30, 0, 0, '2012-07-27 07:34:29'),
		(30, 1, 30, 'New Tickets', 'Application\\\\ReportBundle\\\\Chart\\\\DeskPRO\\\\SimpleVariationChart', 1, 1, 1, 11, 24, 0, 0, '2012-07-27 07:36:17')

");