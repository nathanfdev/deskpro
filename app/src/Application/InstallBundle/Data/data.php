<?php if (!defined('DP_ROOT')) exit('No access');

################################################################################
# Language
################################################################################

##BEGIN:locale.language##
$l = new \Application\DeskPRO\Entity\Language();
$l['title'] = 'English';
$l['locale'] = 'en_US';
$l['language_package'] = 'DeskproLanguages\\LangPackage';
$em->persist($l);
$em->flush();


################################################################################
# Departments
################################################################################

##BEGIN:create_department.department1##
if (!$IMPORT_INSTALL) {
	$q = new \Application\DeskPRO\Entity\Department();
	$q['title'] = 'Sales';
	$q['is_tickets_enabled'] = true;
	$q['is_chat_enabled'] = true;
	$em->persist($q);
	$em->flush();
}

##BEGIN:create_department.department2##
if (!$IMPORT_INSTALL) {
	$q = new \Application\DeskPRO\Entity\Department();
	$q['title'] = 'Support';
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
	$DEFAULT_ARTICLE_CAT['title'] = 'General';
	$em->persist($DEFAULT_ARTICLE_CAT);
	$em->flush();

	$DEFAULT_ARTICLE = new \Application\DeskPRO\Entity\Article();
	$DEFAULT_ARTICLE->person = $AGENT;
	$DEFAULT_ARTICLE->title = 'Example Article';
	$DEFAULT_ARTICLE->content = 'This is an example knowledgebase article. Feel free to edit or delete it.';
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
	$q['title'] = 'General';
	$em->persist($q);
	$em->flush();
}


################################################################################
# News
################################################################################

##BEGIN:create_news.default##
if (!$IMPORT_INSTALL) {
	$DEFAULT_NEWS_CAT = new \Application\DeskPRO\Entity\NewsCategory();
	$DEFAULT_NEWS_CAT['title'] = 'General';
	$em->persist($DEFAULT_NEWS_CAT);
	$em->flush();

	$DEFAULT_NEWS = new \Application\DeskPRO\Entity\News();
	$DEFAULT_NEWS->person = $AGENT;
	$DEFAULT_NEWS->title = 'Example News Post';
	$DEFAULT_NEWS->content = 'This is an example news post. Feel free to edit or delete it.';
	$DEFAULT_NEWS->status = 'published';
	$DEFAULT_NEWS->category = $DEFAULT_NEWS_CAT;
	$em->persist($DEFAULT_NEWS);
	$em->flush();
}


################################################################################
# Feedback
################################################################################

##BEGIN:create_feedback.default##
if (!$IMPORT_INSTALL) {
	$DEFAULT_IDEA_CAT = new \Application\DeskPRO\Entity\FeedbackCategory();
	$DEFAULT_IDEA_CAT['title'] = 'General';
	$em->persist($DEFAULT_IDEA_CAT);
	$em->flush();

	foreach (array('Planning', 'Started', 'Under Review') as $t) {
		$s = new \Application\DeskPRO\Entity\FeedbackStatusCategory();
		$s->status_type = 'active';
		$s->title = $t;
		$em->persist($s);
	}

	foreach (array('Completed', 'Duplicate', 'Already Exists', 'Declined') as $t) {
		$s = new \Application\DeskPRO\Entity\FeedbackStatusCategory();
		$s->status_type = 'closed';
		$s->title = $t;
		$em->persist($s);
	}
	$em->flush();

	$DEFAULT_IDEA = new \Application\DeskPRO\Entity\Feedback();
	$DEFAULT_IDEA->person = $AGENT;
	$DEFAULT_IDEA->title = 'Example Feedback';
	$DEFAULT_IDEA->content = 'This is an example feedback. Feel free to edit or delete it.';
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


################################################################################
# Triggers
################################################################################

##BEGIN:create_trigger.email_validation_web##
$q = new \Application\DeskPRO\Entity\TicketTrigger();
$q->title = 'Enable email validation for new users';
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
$q->title = 'Enable email validation for new users';
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
		'options' => array('creation_system' => 'new_ticket.web_person'),
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
$q->title = 'Enable email validation for new users';
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
		'options' => array('creation_system' => 'new_ticket.widget'),
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

##BEGIN:create_trigger.urgency_base##
$q = new \Application\DeskPRO\Entity\TicketTrigger();
$q->title = 'Initial urgency';
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

##BEGIN:create_trigger.urgency_up1##
$q = new \Application\DeskPRO\Entity\TicketTrigger();
$q->title = '';
$q->event_trigger = 'time_user_waiting';
$q->event_trigger_option = '86400'; // 1 day
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
$q->event_trigger = 'time_user_waiting';
$q->event_trigger_option = '172800'; // 2 days
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
$q->event_trigger = 'time_user_waiting';
$q->event_trigger_option = '259200'; // 3 days
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
$q->sys_name = 'auto_close.resolve_agent_reply';
$q->event_trigger = 'time_agent_waiting';
$q->event_trigger_option = '432000'; // 5 days
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
		'type' => 'status',
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


################################################################################
# Cron Jobs
################################################################################

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

################################################################################
# Portal Blocks
################################################################################

##BEGIN:create_portal_block.news##
$b = new \Application\DeskPRO\Entity\PortalPageDisplay();
$b->section = 'portal';
$b->type = 'News';
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

##BEGIN:create_portal_block.kb_cat_list##
$b = new \Application\DeskPRO\Entity\PortalPageDisplay();
$b->section = 'sidebar';
$b->type = 'kb_cat_list';
$em->persist($b);
$em->flush();

##BEGIN:create_portal_block.feedback_cat_list##
$b = new \Application\DeskPRO\Entity\PortalPageDisplay();
$b->section = 'sidebar';
$b->type = 'feedback_cat_list';
$em->persist($b);
$em->flush();


################################################################################
# Agent Teams
################################################################################

##BEGIN:agent_teams.default1##
$t = new \Application\DeskPRO\Entity\AgentTeam();
$t['name'] = 'Support Managers';
$em->persist($t);
$em->flush();

##BEGIN:agent_teams.default2##
$t = new \Application\DeskPRO\Entity\AgentTeam();
$t['name'] = '1st Level Support';
$em->persist($t);
$em->flush();

##BEGIN:agent_teams.default3##
$t = new \Application\DeskPRO\Entity\AgentTeam();
$t['name'] = '2nd Level Support';
$em->persist($t);
$em->flush();


################################################################################
# Usergroups
################################################################################

##BEGIN:usergroups.everyone##
$g = new \Application\DeskPRO\Entity\Usergroup();
$g['title'] = 'Everyone';
$g['note'] = 'Permissions applied to every user in the system by default';
$g['sys_name'] = 'everyone';
$em->persist($g);
$em->flush();
$USERGROUP_EVERYONE = $g;

##BEGIN:usergroups.agent_all##
$AGENTGROUP_ALL = new \Application\DeskPRO\Entity\Usergroup();
$AGENTGROUP_ALL['title'] = 'All Permissions';
$AGENTGROUP_ALL['note'] = 'Agent has full permissions';
$AGENTGROUP_ALL['is_agent_group'] = true;
$em->persist($AGENTGROUP_ALL);
$em->flush();


################################################################################
# Default base stats
################################################################################

##BEGIN:stats.base_stats##
$em->getConnection()->exec("
	INSERT INTO `stat` (`author_id`, `parent_stat_id`, `title`, `grouping_ref`, `stat_concept_class`, `disabled`, `generate_stats`, `run_frequency`, `variation`, `criteria`, `last_run`, `date_created`)
	VALUES
		(NULL, NULL, 'Tickets Awaiting Agent', 'tickets.agent_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketsAwaitingAgent', 0, 1, 'daily', 'bad', 'a:1:{i:0;a:3:{s:4:\"type\";s:10:\"agent_team\";s:2:\"op\";s:2:\"is\";s:7:\"options\";a:1:{s:10:\"agent_team\";s:2:\"-1\";}}}', NULL, '2012-01-01 01:01:01'),
		(NULL, NULL, 'Tickets Opened', 'tickets.date_created', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketsOpened', 0, 1, 'daily', 'bad', 'a:1:{i:0;a:3:{s:4:\"type\";s:10:\"agent_team\";s:2:\"op\";s:2:\"is\";s:7:\"options\";a:1:{s:10:\"agent_team\";s:2:\"-1\";}}}', NULL, '2012-01-01 01:01:01'),
		(NULL, NULL, 'Rate of Tickets Processed (Opened/Closed)', 'tickets.agent_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\RateOfTicketsProcessed', 0, 1, 'daily', 'bad', 'a:1:{i:0;a:3:{s:4:\"type\";s:10:\"agent_team\";s:2:\"op\";s:2:\"is\";s:7:\"options\";a:1:{s:10:\"agent_team\";s:2:\"-1\";}}}', NULL, '2012-01-01 01:01:01'),
		(NULL, NULL, 'Total Ticket Messages', 'tickets.agent_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TotalTicketMessages', 0, 1, 'daily', 'bad', 'a:1:{i:0;a:3:{s:4:\"type\";s:10:\"agent_team\";s:2:\"op\";s:2:\"is\";s:7:\"options\";a:1:{s:10:\"agent_team\";s:2:\"-1\";}}}', NULL, '2012-01-01 01:01:01'),
		(NULL, NULL, 'Reopened Tickets', 'tickets.agent_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\ReopenedTickets', 0, 0, 'daily', 'bad', 'a:1:{i:0;a:3:{s:4:\"type\";s:10:\"agent_team\";s:2:\"op\";s:2:\"is\";s:7:\"options\";a:1:{s:10:\"agent_team\";s:2:\"-1\";}}}', NULL, '2012-01-01 01:01:01'),
		(NULL, NULL, 'First Resolution Rate test', 'tickets.agent_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\FirstResolutionRate', 0, 0, 'daily', 'bad', 'a:1:{i:0;a:3:{s:4:\"type\";s:10:\"agent_team\";s:2:\"op\";s:2:\"is\";s:7:\"options\";a:1:{s:10:\"agent_team\";s:2:\"-1\";}}}', NULL, '2012-01-01 01:01:01'),
		(NULL, NULL, 'Tickets Agent Participated In', 'tickets.agent_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketsAgentParticipate', 0, 0, 'daily', 'bad', 'a:1:{i:0;a:3:{s:4:\"type\";s:10:\"agent_team\";s:2:\"op\";s:2:\"is\";s:7:\"options\";a:1:{s:10:\"agent_team\";s:2:\"-1\";}}}', NULL, '2012-01-01 01:01:01'),
		(NULL, NULL, 'Total Ticket Resolve Time', 'tickets.agent_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TotalTicketResolveTime', 0, 1, 'daily', 'bad', 'a:1:{i:0;a:3:{s:4:\"type\";s:10:\"agent_team\";s:2:\"op\";s:2:\"is\";s:7:\"options\";a:1:{s:10:\"agent_team\";s:2:\"-1\";}}}', NULL, '2012-01-01 01:01:01'),
		(NULL, NULL, 'Total User Waiting Ticket Resolve Time', 'tickets.agent_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TotalUserWaitingTicketResolvedTime', 0, 1, 'daily', 'bad', 'a:1:{i:0;a:3:{s:4:\"type\";s:10:\"agent_team\";s:2:\"op\";s:2:\"is\";s:7:\"options\";a:1:{s:10:\"agent_team\";s:2:\"-1\";}}}', NULL, '2012-01-01 01:01:01'),
		(NULL, NULL, 'Total Agent Waiting Ticket Resolve Time', 'tickets.agent_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TotalAgentWaitingTicketResolvedTime', 0, 1, 'daily', 'good', 'a:1:{i:0;a:3:{s:4:\"type\";s:10:\"agent_team\";s:2:\"op\";s:2:\"is\";s:7:\"options\";a:1:{s:10:\"agent_team\";s:2:\"-1\";}}}', NULL, '2012-01-01 01:01:01'),
		(NULL, NULL, 'Total First Response Time', 'tickets.agent_id', 'Application\\\\ReportBundle\\\\Stat\\\\DeskPRO\\\\TicketFirstResponseTime', 0, 1, 'daily', 'bad', 'a:1:{i:0;a:3:{s:4:\"type\";s:10:\"agent_team\";s:2:\"op\";s:2:\"is\";s:7:\"options\";a:1:{s:10:\"agent_team\";s:2:\"-1\";}}}', NULL, '2012-01-01 01:01:01')
");

##BEGIN:stats.default_dashboard##
$em->getConnection()->exec("
	INSERT INTO `report_dashboard` (`author_id`, `title`, `number_columns`, `disabled`, `date_created`)
	VALUES (NULL, 'Default', 3, 0, '2012-01-01 01:01:01');
");
