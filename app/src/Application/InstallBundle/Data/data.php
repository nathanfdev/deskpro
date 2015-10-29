<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

# Language
################################################################################

##BEGIN:locale.language##
$em->getConnection()->executeUpdate(
    "
    INSERT INTO `languages`
        (`id`, `sys_name`, `lang_code`, `title`, `base_filepath`, `locale`, `flag_image`, `is_rtl`, `has_user`, `has_agent`, `has_admin`)
    VALUES
        (1, 'default', 'eng', 'English', NULL, 'en_US', 'us.png', 0, 1, 1, 1)
    ;
"
);

################################################################################
# Departments
################################################################################

##BEGIN:create_department.department2##
if (!$IMPORT_INSTALL) {
    $q                       = new \Application\DeskPRO\Entity\Department();
    $q['title']              = $translate->phrase('user.defaults.department_support');
    $q['is_tickets_enabled'] = true;
    $q['is_chat_enabled']    = false;
    $em->persist($q);
    $em->flush();
}

##BEGIN:create_department.department1##
if (!$IMPORT_INSTALL) {
    $q                       = new \Application\DeskPRO\Entity\Department();
    $q['title']              = $translate->phrase('user.defaults.department_sales');
    $q['is_tickets_enabled'] = true;
    $q['is_chat_enabled']    = false;
    $em->persist($q);
    $em->flush();
}

##BEGIN:create_department.department3##
if (!$IMPORT_INSTALL) {
    $q                       = new \Application\DeskPRO\Entity\Department();
    $q['title']              = $translate->phrase('user.defaults.department_support');
    $q['is_tickets_enabled'] = false;
    $q['is_chat_enabled']    = true;
    $em->persist($q);
    $em->flush();
}

##BEGIN:create_department.department4##
if (!$IMPORT_INSTALL) {
    $q                       = new \Application\DeskPRO\Entity\Department();
    $q['title']              = $translate->phrase('user.defaults.department_sales');
    $q['is_tickets_enabled'] = false;
    $q['is_chat_enabled']    = true;
    $em->persist($q);
    $em->flush();
}

################################################################################
# KB
################################################################################

##BEGIN:create_article.default##
if (!$IMPORT_INSTALL) {
    $DEFAULT_ARTICLE_CAT          = new \Application\DeskPRO\Entity\ArticleCategory();
    $DEFAULT_ARTICLE_CAT['title'] = $translate->phrase('user.defaults.article_category_general');
    $em->persist($DEFAULT_ARTICLE_CAT);
    $em->flush();

    $DEFAULT_ARTICLE          = new \Application\DeskPRO\Entity\Article();
    $DEFAULT_ARTICLE->person  = $AGENT;
    $DEFAULT_ARTICLE->title   = $translate->phrase('user.defaults.article_example_title');
    $DEFAULT_ARTICLE->content = $translate->phrase('user.defaults.article_example_content');
    $DEFAULT_ARTICLE->status  = 'published';
    $DEFAULT_ARTICLE->addToCategory($DEFAULT_ARTICLE_CAT);
    $em->persist($DEFAULT_ARTICLE);
    $em->flush();
}

################################################################################
# Downloads
################################################################################

##BEGIN:create_download_cat.default##
if (!$IMPORT_INSTALL) {
    $q          = new \Application\DeskPRO\Entity\DownloadCategory();
    $q['title'] = $translate->phrase('user.defaults.downloads_category_general');
    $em->persist($q);
    $em->flush();
}

################################################################################
# News
################################################################################

##BEGIN:create_news.default##
if (!$IMPORT_INSTALL) {
    $DEFAULT_NEWS_CAT          = new \Application\DeskPRO\Entity\NewsCategory();
    $DEFAULT_NEWS_CAT['title'] = $translate->phrase('user.defaults.news_category_general');
    $em->persist($DEFAULT_NEWS_CAT);
    $em->flush();

    $DEFAULT_NEWS           = new \Application\DeskPRO\Entity\News();
    $DEFAULT_NEWS->person   = $AGENT;
    $DEFAULT_NEWS->title    = $translate->phrase('user.defaults.news_example_title');
    $DEFAULT_NEWS->content  = $translate->phrase('user.defaults.news_example_content');
    $DEFAULT_NEWS->status   = 'published';
    $DEFAULT_NEWS->category = $DEFAULT_NEWS_CAT;
    $em->persist($DEFAULT_NEWS);
    $em->flush();
}

################################################################################
# Feedback
################################################################################

$em->getConnection()->executeUpdate(
    "
INSERT INTO `feedback_categories` (`id`, `parent_id`, `title`, `slug`, `display_order`, `depth`, `root`) VALUES
(1, NULL, 'Suggestion', 'suggestion', 0, 0, NULL),
(2, NULL, 'Feature Request', 'feature-request', 0, 0, NULL),
(3, NULL, 'Bug Report', 'bug-report', 0, 0, NULL);
"
);

$em->getConnection()->executeUpdate(
    "
INSERT INTO `custom_def_feedback` (`id`, `parent_id`, `app_id`, `sys_name`, `js_class`, `has_form_template`, `has_display_template`, `title`, `description`, `handler_class`, `options`, `is_user_enabled`, `is_enabled`, `display_order`, `default_value`, `is_agent_field`) VALUES
(1, NULL, NULL, 'cat', '', 0, 0, 'Category', 'e.g., maybe Windows, Mac, Linux.', 'Application\\\DeskPRO\\\CustomFields\\\Handler\\\Text', 'a:0:{}', 1, 1, 0, NULL, 1);
"
);

$em->getConnection()->executeUpdate(
    "
INSERT INTO `feedback_status_categories` (`id`, `status_type`, `title`, `display_order`) VALUES
(1, 'active', 'Gathering Feedback', 0),
(2, 'active', 'Planning', 0),
(3, 'active', 'Started', 0),
(4, 'active', 'Under Review', 0),
(5, 'closed', 'Completed', 0),
(6, 'closed', 'Duplicate', 0),
(7, 'closed', 'Declined', 0);
"
);

$em->getConnection()->executeUpdate(
    "
    INSERT INTO `feedback`
      (`id`, `status_category_id`, `category_id`, `person_id`, `language_id`, `hidden_status`, `validating`, `popularity`, `slug`, `title`, `content`, `view_count`, `total_rating`, `num_comments`, `num_ratings`, `status`, `date_created`, `date_published`, `date_updated`, `date_last_comment`)
    VALUES
      (1, 5, 1, 1, NULL, 'validating', NULL, 0, 'Example Suggestion', 'example-suggestion', 'This is an example suggestion. Feel free to edit or delete it from the agent interface.', 0, 1, 0, 2, 'new', '2015-04-13 11:33:33', '2015-04-13 11:33:33', '0000-00-00 00:00:00', NULL),
      (2, 1, 1, 1, NULL, 'deleted', NULL, 0, 'slug-to-feedback-1', 'Test feedback 1', 'Content of test feedback 1', 0, 3, 0, 4, 'hidden', '2015-05-01 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
      (3, 1, 2, 1, NULL, NULL, NULL, 0, 'slug-to-feedback-2', 'Test feedback 2', 'Content of test feedback 2', 0, 5, 0, 6, 'active', '2015-06-02 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
      (4, 2, 3, 1, NULL, 'validating', NULL, 0, 'slug-to-feedback-3', 'Test feedback 3', 'Content of test feedback 3', 0, 0, 0, 0, 'active', '2015-07-03 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
      (5, 1, 1, 1, NULL, 'spam', NULL, 0, 'slug-to-feedback-4', 'Test feedback 4', 'Content of test feedback 4', 0, 1, 0, 1, 'hidden', '2015-08-04 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
      (6, 5, 1, 1, NULL, 'validating', NULL, 0, 'slug-to-feedback-5', 'Test feedback 5', 'Content of test feedback 5', 0, 2, 0, 1, 'closed', '2015-09-05 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
      (7, 1, 2, 1, NULL, 'validating', NULL, 15, 'slug-to-feedback-6', 'Test feedback 6', 'I am trying to implement Infinite Scrolling on a gridview to speed up my web application, since the gridview is being bound to a sql query that returns thousands of records at start (its the clients wish, and I cant change that.)', 0, 3, 0, 1, 'new', '2015-10-10 00:00:00', NULL, '0000-00-00 00:00:00', NULL);
    "
);

$em->getConnection()->executeUpdate(
    "
INSERT INTO `labels_feedback` (`feedback_id`, `label`) VALUES
(1, 'label1'),
(1, 'label2'),
(2, 'label1'),
(3, 'another');
"
);

$em->getConnection()->executeUpdate(
    "
INSERT INTO `custom_data_feedback` (`id`, `feedback_id`, `field_id`, `root_field_id`, `value`, `input`) VALUES
(1, 1, 1, NULL, 0, 'Windows'),
(2, 2, 1, NULL, 0, 'Linux'),
(3, 3, 1, NULL, 0, 'Linux'),
(4, 4, 1, NULL, 0, 'Mac');
"
);

$em->getConnection()->executeUpdate(
    "
INSERT INTO `feedback_comments` (`id`, `feedback_id`, `person_id`, `ip_address`, `email`, `name`, `website`, `content`, `status`, `validating`, `is_reviewed`, `date_created`, `visitor_id`) VALUES
(1, 1, 1, '', NULL, NULL, NULL, 'Some comment for the first feedback. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce ultrices sem ac risus efficitur, vitae.', 'validating', NULL, 0, '2015-08-17 00:00:00', ''),
(2, 1, 1, '', NULL, NULL, NULL, 'One more comment for the first feedback', 'validating', '0', 0, '2015-09-08 00:00:00', ''),
(3, 2, 1, '', NULL, NULL, NULL, 'Some comment for the second feedback. Quisque id malesuada urna. Aliquam erat volutpat. Duis risus odio, faucibus ac lacus nec, dapibus.', 'validating', NULL, 0, '2015-09-23 00:00:00', ''),
(4, 3, 1, '', NULL, NULL, NULL, 'Some comment for the third feedback. Proin enim mauris, faucibus sit amet pretium non, sagittis ut eros. Praesent non sem ut.', 'user_validating', '0', 0, '2015-10-01 00:00:00', '');
"
);

################################################################################
# Person Setting
################################################################################

$em->getConnection()->executeUpdate(
    "
    INSERT INTO `person_settings` (`person_id`, `name`, `value`)
      VALUES
      (1, 'feedback_display_fields', '{\"isStored\":null,\"isChanged\":\"1\",\"card\":{\"id\":{\"isShown\":\"1\"},\"hidden_status\":{\"isShown\":\"1\"},\"status_category\":{\"isShown\":\"1\"},\"custom_category\":{\"isShown\":\"1\"},\"type\":{\"isShown\":\"1\"},\"date_created\":{\"isShown\":null},\"total_rating\":{\"isShown\":\"1\"},\"num_ratings\":{\"isShown\":\"1\"},\"num_comments\":{\"isShown\":\"1\"}},\"table\":{\"id\":{\"isShown\":\"1\"},\"num_ratings\":{\"isShown\":\"1\"},\"title\":{\"isShown\":\"1\"},\"content\":{\"isShown\":\"1\"},\"hidden_status\":{\"isShown\":\"1\"},\"status_category\":{\"isShown\":\"1\"},\"type\":{\"isShown\":\"1\"},\"custom_category\":{\"isShown\":\"1\"},\"labels\":{\"isShown\":\"1\"},\"author_name\":{\"isShown\":\"1\"},\"num_comments\":{\"isShown\":\"1\"},\"date_created\":{\"isShown\":\"1\"},\"total_rating\":{\"isShown\":\"1\"},\"validating\":{\"isShown\":\"1\"}}}');
    "
);

################################################################################
# Agent Teams
################################################################################

##BEGIN:agent_teams.default1##
$t         = new \Application\DeskPRO\Entity\AgentTeam();
$t['name'] = $translate->phrase('agent.defaults.team_support_managers');
$em->persist($t);
$em->flush();

##BEGIN:agent_teams.default2##
$t         = new \Application\DeskPRO\Entity\AgentTeam();
$t['name'] = $translate->phrase('agent.defaults.team_lvl1_support');
$em->persist($t);
$em->flush();

##BEGIN:agent_teams.default3##
$t         = new \Application\DeskPRO\Entity\AgentTeam();
$t['name'] = $translate->phrase('agent.defaults.team_lvl2_support');
$em->persist($t);
$em->flush();

##BEGIN:agent_teams.setting##
$t          = new \Application\DeskPRO\Entity\Setting();
$t['name']  = 'core.use_agent_team';
$t['value'] = '1';
$em->persist($t);
$em->flush();

################################################################################
# Usergroups
################################################################################

##BEGIN:usergroups.everyone##
$g             = new \Application\DeskPRO\Entity\Usergroup();
$g['title']    = $translate->phrase('agent.defaults.usergroup_everyone');
$g['note']     = $translate->phrase('agent.defaults.usergroup_everyone_note');
$g['sys_name'] = 'everyone';
$em->persist($g);
$em->flush();
$USERGROUP_EVERYONE = $g;

##BEGIN:usergroups.register##
$g             = new \Application\DeskPRO\Entity\Usergroup();
$g['title']    = $translate->phrase('agent.defaults.usergroup_registered');
$g['note']     = $translate->phrase('agent.defaults.usergroup_registered_note');
$g['sys_name'] = 'registered';
$em->persist($g);
$em->flush();
$USERGROUP_REG = $g;

##BEGIN:usergroups.agent_all##
$AGENTGROUP_ALL                   = new \Application\DeskPRO\Entity\Usergroup();
$AGENTGROUP_ALL['title']          = $translate->phrase('agent.defaults.usergroup_agent_all_perms');
$AGENTGROUP_ALL['note']           = $translate->phrase('agent.defaults.usergroup_agent_all_perms_note');
$AGENTGROUP_ALL['is_agent_group'] = true;
$AGENTGROUP_ALL['sys_name']       = 'agent_all_perms';
$em->persist($AGENTGROUP_ALL);
$em->flush();

##BEGIN:usergroups.agent_all_nondestructive##
$AGENTGROUP_ALL_ND                   = new \Application\DeskPRO\Entity\Usergroup();
$AGENTGROUP_ALL_ND['title']          = $translate->phrase('agent.defaults.usergroup_agent_all_non_destructive');
$AGENTGROUP_ALL_ND['note']           = $translate->phrase('agent.defaults.usergroup_agent_all_non_destructive_note');
$AGENTGROUP_ALL_ND['is_agent_group'] = true;
$AGENTGROUP_ALL_ND['sys_name']       = 'agent_all_safe_perms';
$em->persist($AGENTGROUP_ALL_ND);
$em->flush();

// Permissions for ND group
$ugid = $AGENTGROUP_ALL_ND->getId();
$em->getConnection()->executeUpdate(
    "
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
        ($ugid, NULL, '1', 'agent_general.signature_rte')
"
);

################################################################################
# TEMPORARY TEST DATA: People
################################################################################

if (!function_exists('create_user')) {
    function create_user($fname, $lname, $email, $pass, $agent = false, $admin = false, $is_deleted = false)
    {
        $user             = new \Application\DeskPRO\Entity\Person();
        $user->first_name = $fname;
        $user->last_name  = $lname;
        $user->setEmail($email, true);
        $user->setPassword($pass);
        $user->is_user      = true;
        $user->is_confirmed = true;
        $user->is_deleted   = $is_deleted;

        if ($agent || $admin) {
            $user->is_agent_confirmed = true;
            $user->is_agent           = true;
            $user->can_agent          = true;
        }

        if ($admin) {
            $user->can_admin   = true;
            $user->can_billing = true;
            $user->can_reports = true;
        }

        return $user;
    }
}

$em->persist(create_user('John', 'Doe', 'john@doe.lo', '11111111', true, true));
$em->persist(create_user('Jane', 'Doe', 'jane@doe.lo', '11111111', true, false));
$em->persist(create_user('Jack', 'Doe', 'jack@doe.lo', '11111111', false, false));
$em->persist(create_user('Friedrich', 'Doe', 'fred@doe.lo', '11111111', true, false, true));
$em->flush();

################################################################################
# TEMPORARY TEST DATA: Organizations
################################################################################

$em->getConnection()->executeUpdate(
    "
    INSERT INTO `organizations`
        (`picture_blob_id`, `name`, `summary`, `importance`, `date_created`)
    VALUES
        (NULL, 'Organization 1', 'test organization', 1, '2015-08-03 00:00:00'),
        (NULL, 'Organization 2', 'test organization', 2, '2015-08-07 00:00:00')
    ;
"
);

################################################################################
# TEMPORARY TEST DATA: Groups
################################################################################

$em->getConnection()->executeUpdate(
    "
    INSERT INTO `usergroups`
        (`title`, `note`, `is_agent_group`, `sys_name`, `is_enabled`)
    VALUES
        ('Group 1', 'test', 0, 'g1', 1),
        ('Group 2 (disabled)', 'test', 0, 'g2', 0),
        ('Group 3', 'test', 0, 'g3', 1),
        ('Group 4', 'test', 0, 'g4', 1)
    ;

    INSERT INTO `person2usergroups`
        (`person_id`, `usergroup_id`)
    VALUES
        (1, 1),
        (1, 2),
        (2, 2),
        (2, 3),
        (3, 3),
        (4, 4)
    ;
"
);

################################################################################
# TEMPORARY TEST DATA: Chats
################################################################################

$em->getConnection()->executeUpdate(
    "
    INSERT INTO `chat_conversations`
        (`department_id`, `agent_id`, `subject`, `status`, `person_name`, `person_email`, `rating_comment`,
         `is_agent`, `is_window`, `date_created`, `should_send_transcript`, `total_to_ended`, `ended_by`)

    VALUES

        (1, 1, 'Test chat 1', 'test', 'test', 'test', '', 1, 1, '2010-08-01 10:19:00', 1, 1, 'test'),
        (1, 1, 'Test chat 2', 'test', 'test', 'test', '', 1, 1, '2011-08-02 10:19:00', 1, 1, 'test'),
        (1, 2, 'Test chat 3', 'test', 'test', 'test', '', 1, 1, '2015-08-03 10:19:00', 1, 1, 'test'),
        (2, 2, 'Test chat 4', 'test', 'test', 'test', '', 1, 1, '2015-08-04 10:19:00', 1, 1, 'test'),
        (2, 2, 'Test chat 5', 'test', 'test', 'test', '', 1, 1, '2015-08-05 10:19:00', 1, 1, 'test')
    ;
"
);

################################################################################
# TEMPORARY TEST DATA: Articles, News, Downloads and their Categories
################################################################################

$em->getConnection()->executeUpdate(
    "
    INSERT INTO `articles`
        (`id`, `person_id`, `slug`, `title`, `content`, `view_count`, `total_rating`, `num_comments`,
         `num_ratings`, `status`, `hidden_status`, `date_created`, `date_published`, `date_updated`)
    VALUES
        (2, 1, '2', 'Test Article #2', 'Test Article #2', 0, 0, 0, 0, 'published', NULL, '2011-08-03 00:00:00', NULL, NULL),
        (3, 2, '3', 'Test Article #3', 'Test Article #3', 0, 0, 0, 0, 'published', NULL, '2011-08-05 00:00:00', NULL, '2012-08-10 00:00:00'),
        (4, 2, '4', 'Test Article #4', 'Test Article #4', 0, 0, 0, 0, 'archived', NULL, '2011-08-04 00:00:00', NULL, '2012-08-06 00:00:00'),
        (5, 2, '5', 'Test Article #5', 'Test Article #5', 0, 0, 0, 0, 'hidden', 'draft', '2011-08-11 00:00:00', NULL, NULL),
        (6, 3, '6', 'Test Article #6', 'Test Article #6', 0, 0, 0, 0, 'published', NULL, '2011-08-12 00:00:00', NULL, '2012-08-13 00:00:00'),
        (7, 3, '7', 'Test Article #7', 'Test Article #7', 0, 0, 0, 0, 'published', NULL, '2011-08-13 00:00:00', NULL, NULL),
        (8, 1, '8', 'Test Article #8', 'Test Article #8', 0, 0, 0, 0, 'published', NULL, '2011-08-02 00:00:00', NULL, '2012-03-03 00:00:00')
    ;

    INSERT INTO `article_categories`
        (`id`, `parent_id`, `is_agent`, `is_book`, `template_suffix`, `title`, `slug`, `display_order`, `depth`)
    VALUES
        (1, NULL, 1, 1, NULL, 'Test Category #1', '1', 1, 1),
        (2, NULL, 0, 0, NULL, 'Test Category #2', '2', 2, 1),
        (3, 1, 1, 1, NULL, 'Test Category #3', '3', 1, 1),
        (4, 1, 1, 1, NULL, 'Test Category #4', '4', 1, 1),
        (5, 2, 1, 1, NULL, 'Test Category #5', '5', 1, 1),
        (6, 2, 1, 1, NULL, 'Test Category #6', '6', 1, 1),
        (7, 3, 1, 1, NULL, 'Test Category #7', '7', 1, 1),
        (8, 3, 1, 1, NULL, 'Test Category #8', '8', 1, 1),
        (9, 7, 1, 1, NULL, 'Test Category #9', '9', 1, 1),
        (10, 7, 1, 1, NULL, 'Test Category #10', '10', 1, 1),
        (11, 7, 1, 1, NULL, 'Test Category #11', '11', 1, 1)
    ;

    INSERT INTO `article_to_categories`
        (`article_id`, `category_id`)
    VALUES
        (2, 1),
        (3, 1),
        (3, 2),
        (4, 2),
        (5, 1),
        (5, 2),
        (6, 1),
        (7, 1),
        (8, 1),
        (8, 9)
    ;

    INSERT INTO `news_categories`
        (`id`, `parent_id`, `title`, `slug`, `display_order`, `depth`)
    VALUES
        (1, NULL, 'Test Category #1', '1', 1, 1),
        (2, NULL, 'Test Category #2', '2', 2, 1),
        (3, 1, 'Test Category #3', '3', 2, 1),
        (4, 1, 'Test Category #4', '4', 2, 1),
        (5, 2, 'Test Category #5', '5', 2, 1),
        (6, 2, 'Test Category #6', '6', 2, 1),
        (7, 3, 'Test Category #7', '7', 2, 1),
        (8, 3, 'Test Category #8', '8', 2, 1),
        (9, 7, 'Test Category #9', '9', 2, 1),
        (10, 7, 'Test Category #10', '10', 2, 1),
        (11, 7, 'Test Category #11', '11', 2, 1)
    ;

    INSERT INTO `news`
        (`id`, `category_id`, `person_id`, `slug`, `title`, `content`, `view_count`, `total_rating`, `num_comments`,
         `num_ratings`, `status`, `hidden_status`, `date_created`, `date_published`, `date_updated`)
    VALUES
        (1, 1, 1, '1', 'Test News #1', 'Test News #1', 0, 0, 0, 0, 'published', NULL, '2011-08-02 00:00:00', NULL, '2012-03-03 00:00:00'),
        (2, 1, 1, '2', 'Test News #2', 'Test News #2', 0, 0, 0, 0, 'published', NULL, '2011-08-03 00:00:00', NULL, NULL),
        (3, 1, 2, '3', 'Test News #3', 'Test News #3', 0, 0, 0, 0, 'published', NULL, '2011-08-05 00:00:00', NULL, '2012-08-10 00:00:00'),
        (4, 1, 2, '4', 'Test News #4', 'Test News #4', 0, 0, 0, 0, 'archived', NULL, '2011-08-04 00:00:00', NULL, '2012-08-06 00:00:00'),
        (5, 1, 2, '5', 'Test News #5', 'Test News #5', 0, 0, 0, 0, 'hidden', 'draft', '2011-08-11 00:00:00', NULL, NULL),
        (6, 1, 3, '6', 'Test News #6', 'Test News #6', 0, 0, 0, 0, 'published', NULL, '2011-08-12 00:00:00', NULL, '2012-08-13 00:00:00'),
        (7, 2, 3, '7', 'Test News #7', 'Test News #7', 0, 0, 0, 0, 'published', NULL, '2011-08-13 00:00:00', NULL, NULL),
        (8, 9, 3, '8', 'Test News #8', 'Test News #8', 0, 0, 0, 0, 'hidden', NULL, '2011-08-15 00:00:00', NULL, '2012-08-16 00:00:00')
    ;

    INSERT INTO `download_categories`
        (`id`, `parent_id`, `title`, `slug`, `display_order`, `depth`)
    VALUES
        (1, NULL, 'Test Category #1', '1', 1, 1),
        (2, NULL, 'Test Category #2', '2', 2, 1),
        (3, 1, 'Test Category #3', '3', 2, 1),
        (4, 1, 'Test Category #4', '4', 2, 1),
        (5, 2, 'Test Category #5', '5', 2, 1),
        (6, 2, 'Test Category #6', '6', 2, 1),
        (7, 3, 'Test Category #7', '7', 2, 1),
        (8, 3, 'Test Category #8', '8', 2, 1),
        (9, 7, 'Test Category #9', '9', 2, 1),
        (10, 7, 'Test Category #10', '10', 2, 1),
        (11, 7, 'Test Category #11', '11', 2, 1)
    ;

    INSERT INTO `downloads`
        (`id`, `category_id`, `person_id`, `slug`, `title`, `content`, `view_count`, `total_rating`, `num_comments`,
         `num_ratings`, `status`, `hidden_status`, `date_created`, `date_published`, `date_updated`, `num_downloads`)
    VALUES
        (1, 1, 1, '1', 'Test Download #1', 'Test Download #1', 0, 0, 0, 0, 'published', NULL, '2011-08-02 00:00:00', NULL, '2012-03-03 00:00:00', 0),
        (2, 1, 1, '2', 'Test Download #2', 'Test Download #2', 0, 0, 0, 0, 'published', NULL, '2011-08-03 00:00:00', NULL, NULL, 0),
        (3, 1, 2, '3', 'Test Download #3', 'Test Download #3', 0, 0, 0, 0, 'published', NULL, '2011-08-05 00:00:00', NULL, '2012-08-10 00:00:00', 0),
        (4, 1, 2, '4', 'Test Download #4', 'Test Download #4', 0, 0, 0, 0, 'archived', NULL, '2011-08-04 00:00:00', NULL, '2012-08-06 00:00:00', 0),
        (5, 1, 2, '5', 'Test Download #5', 'Test Download #5', 0, 0, 0, 0, 'hidden', 'draft', '2011-08-11 00:00:00', NULL, NULL, 0),
        (6, 1, 3, '6', 'Test Download #6', 'Test Download #6', 0, 0, 0, 0, 'published', NULL, '2011-08-12 00:00:00', NULL, '2012-08-13 00:00:00', 0),
        (7, 2, 3, '7', 'Test Download #7', 'Test Download #7', 0, 0, 0, 0, 'published', NULL, '2011-08-13 00:00:00', NULL, NULL, 0),
        (8, 9, 3, '8', 'Test Download #8', 'Test Download #8', 0, 0, 0, 0, 'hidden', NULL, '2011-08-15 00:00:00', NULL, '2012-08-16 00:00:00', 0)
    ;
"
);

################################################################################
# TEMPORARY TEST DATA: Glossary
################################################################################

$em->getConnection()->executeUpdate(
    "
    INSERT INTO `glossary_word_definitions`
        (`id`, `definition`)
    VALUES
        (1, 'Definition Text')
    ;


    INSERT INTO `glossary_words`
        (`id`, `definition_id`, `word`)
    VALUES
        (1, 1, 'Word 1'),
        (2, 1, 'Word 2')
    ;
"
);

################################################################################
# TEMPORARY TEST DATA: ArticlePendingCreate
################################################################################

$em->getConnection()->executeUpdate(
    "
    INSERT INTO `article_pending_create`
        (`person_id`, `ticket_id`, `ticket_message_id`, `comment`, `date_created`, `assigned_person_id`)
    VALUES
        (1, NULL, NULL, 'ArticlePendingCreate #1', '2015-09-01 10:05:30', 2),
        (2, NULL, NULL, 'ArticlePendingCreate #2', '2015-09-02 04:12:25', 3)
    ;
"
);

################################################################################
# TEMPORARY TEST DATA: Comments
################################################################################

$em->getConnection()->executeUpdate(
    "
    INSERT INTO `article_comments`
        (`id`, `article_id`, `person_id`, `ip_address`, `email`, `name`, `website`, `content`, `status`, `validating`, `is_reviewed`, `date_created`)
    VALUES
        (1, 1, 1, '', NULL, NULL, NULL, 'Article comment #1', 'visible', NULL, 1, '2011-08-01 00:00:00'),
        (2, 1, 2, '', NULL, NULL, NULL, 'Article comment #2', 'validating', NULL, 0, '2011-08-01 00:00:00'),
        (3, 2, 3, '', NULL, NULL, NULL, 'Article comment #3', 'validating', NULL, 0, '2011-08-01 00:00:00')
    ;

    INSERT INTO `news_comments`
        (`id`, `news_id`, `person_id`, `ip_address`, `email`, `name`, `website`, `content`, `status`, `validating`, `is_reviewed`, `date_created`)
    VALUES
        (1, 1, 1, '', NULL, NULL, NULL, 'News comment #1', 'visible', NULL, 1, '2011-08-01 00:00:00'),
        (2, 1, 2, '', NULL, NULL, NULL, 'News comment #2', 'validating', NULL, 0, '2011-08-01 00:00:00'),
        (3, 2, 3, '', NULL, NULL, NULL, 'News comment #3', 'validating', NULL, 0, '2011-08-01 00:00:00')
    ;

    INSERT INTO `download_comments`
        (`id`, `download_id`, `person_id`, `ip_address`, `email`, `name`, `website`, `content`, `status`, `validating`, `is_reviewed`, `date_created`)
    VALUES
        (1, 1, 1, '', NULL, NULL, NULL, 'Download comment #1', 'visible', NULL, 1, '2011-08-01 00:00:00'),
        (2, 1, 2, '', NULL, NULL, NULL, 'Download comment #2', 'validating', NULL, 0, '2011-08-01 00:00:00'),
        (3, 2, 3, '', NULL, NULL, NULL, 'Download comment #3', 'validating', NULL, 0, '2011-08-01 00:00:00')
    ;
"
);

################################################################################
# TEMPORARY TEST DATA: Blobs
################################################################################
$em->getConnection()->executeUpdate(
    "
    INSERT INTO `blobs`
        (`id`, `original_blob_id`, `sys_name`, `storage_loc`, `storage_loc_pref`, `storage_loc_specific`, `save_path`, `file_url`, `filename`, `filesize`, `content_type`, `authcode`, `blob_hash`, `is_media_upload`, `title`, `dim_w`, `dim_h`, `date_created`, `is_temp`)
    VALUES
        (1, NULL, NULL, 'db', NULL, NULL, '1/1WCRRCQJQMCXWXMJ0', NULL, 'app_256.png', 55189, 'image/png', '1WCRRCQJQMCXWXMJ0', '9ace9725e1eddb81702043db2522374c', 0, '', 256, 256, '2015-07-07 11:11:47', 0);
"
);

################################################################################
# TEMPORARY TEST DATA: Projects
################################################################################

$em->getConnection()->executeUpdate(
    "
    INSERT INTO `task_projects`
        (`id`, `title`)
    VALUES
        (1, 'Example Project'),
        (2, 'Example Project 2'),
        (3, 'Example Project 3'),
        (4, 'Example Project 4'),
        (5, 'Example Project 5'),
        (6, 'Example Project 6'),
        (7, 'Example Project 7')
    ;

    INSERT INTO `task_lists`
        (`id`, `title`, `project_id`, `display_order`)
    VALUES
        (1, 'To Do', 1, 1),
        (2, 'Doing', 1, 2),
        (3, 'Done', 1, 3)
    ;

    INSERT INTO `task_members`
        (`id`, `person_id`, `team_id`, `department_id`, `project_id`)
    VALUES
        (1, 1, NULL, NULL, 1),
        (2, NULL, 1, NULL, 2),
        (3, NULL, NULL, 1, 3)
    ;
"
);

################################################################################
# TEMPORARY TEST DATA: Tasks
################################################################################

$em->getConnection()->executeUpdate(
    "
    INSERT INTO `tasks_new`
        (`id`, `creator_person_id`, `project_id`, `list_id`, `title`, `percent_complete`, `date_created`, `task_type`, `date_due`, `date_event_start`, `date_event_end`, `visibility`, `urgency`, `is_done`, `date_done`, `display_order`)
    VALUES
        (1, 1, 1, 2, 'Test task 1', 0, '2015-09-04 15:30:00', 'task', '2015-09-29 04:12:25', NULL, NULL, 'private', 5, 0, NULL, 1),
        (2, 1, 1, 1, 'Test task 2', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 2),
        (3, 1, 1, 3, 'Test task 3', 100, '2015-09-04 15:30:00', 'task', NULL, NULL, NULL, 'project', 5, 1, '2015-09-04 16:00:00', 3),
        (4, 1, 1, 1, 'Test task 4', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 4),
        (5, 1, 1, 1, 'Test task 5', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 5),
        (6, 1, 2, 1, 'Test task 6', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 6),
        (7, 1, 2, 1, 'Test task 7', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 7),
        (8, 1, 2, 1, 'Test task 8', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 8),
        (9, 1, 2, 1, 'Test task 9', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 9),
        (10, 1, 3, 1, 'Test task 10', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 10),
        (11, 1, 3, 1, 'Test task 11', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 11),
        (12, 1, 4, 1, 'Test task 12', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 12),
        (13, 1, 4, 1, 'Test task 13', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 13),
        (14, 1, 5, 1, 'Test task 14', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 14),
        (15, 1, 5, 1, 'Test task 15', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 15),
        (16, 1, 5, 1, 'Test task 16', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 16),
        (17, 1, 5, 1, 'Test task 17', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 17),
        (18, 1, 5, 1, 'Test task 18', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 18),
        (19, 1, 5, 1, 'Test task 19', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 19),
        (20, 1, 5, 1, 'Test task 20', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 20),
        (21, 1, 5, 1, 'Test task 21', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 21),
        (22, 1, 5, 1, 'Test task 22', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 22),
        (23, 1, 5, 1, 'Test task 23', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 23),
        (24, 1, 5, 1, 'Test task 24', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 24),
        (25, 1, 5, 1, 'Test task 25', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 25),
        (26, 1, 5, 1, 'Test task 26', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 26),
        (27, 1, 5, 1, 'Test task 27', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 27),
        (28, 1, 6, 1, 'Test task 28', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 28),
        (29, 1, 6, 1, 'Test task 29', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 29),
        (30, 1, 6, 1, 'Test task 30', 0, '2015-09-04 15:30:00', 'task', '2015-09-30 04:12:25', NULL, NULL, 'public', 5, 0, NULL, 30)
    ;

    INSERT INTO `task_labels`
        (`id`, `task_id`, `label`)
    VALUES
        (1, 1, 'Example'),
        (2, 1, 'Demo'),
        (3, 2, 'Example')
    ;

    INSERT INTO `task_assignments`
        (`id`, `task_id`, `person_id`, `team_id`, `department_id`)
    VALUES
        (1, 1, 1, NULL, NULL),
        (2, 2, NULL, NULL, 1),
        (3, 3, NULL, 1, NULL)
    ;

    INSERT INTO `task_subtask`
        (`id`, `task_id`, `creator_id`, `title`, `is_done`, `date_created`, `display_order`, `date_completed`)
    VALUES
        (1, 1, 1, 'Example subtask', 0, '2015-09-04 15:45:00', 1, NULL)
    ;

    INSERT INTO `task_comments_new`
        (`id`, `person_id`, `date_created`, `comment`, `task_id`)
    VALUES
        (1, 1, '2015-09-04 15:40:00', 'An example comment', 1)
    ;

    INSERT INTO `task_attachments`
        (`id`, `task_id`, `task_comment_id`, `person_id`, `blob_id`, `date_created`)
    VALUES
        (1, 1, 1, 1, 1, '2015-09-04 15:30:00')
    ;

    INSERT INTO `task_links`
        (`id`, `task_id`, `ticket_id`, `chat_id`, `article_id`)
    VALUES
        (1, 1, 1, NULL, NULL),
        (2, 2, NULL, NULL, 1)
    ;
"
);

########################################################
# TEMP DATA
########################################################

$faker = \Faker\Factory::create();

// the content publisher agent guy
$publisher            = new \Application\DeskPRO\Entity\Person();
$publisher->name      = 'Corporate Content';
$publisher->can_agent = true;
$publisher->is_agent  = true;
$publisher->addEmailAddressString('content.publisher@deskprodemo.com');
$publisher->setPassword('publisher');

$em->persist($publisher);
$em->flush($publisher);

// a regular dude
$person       = new \Application\DeskPRO\Entity\Person();
$person->name = 'Joe Kool';
$person->addEmailAddressString('joe@deskprodemo.com');
$person->setPassword('joe');

$em->persist($person);
$em->flush($person);

//////////////////////////////////////////////////////////////
// articles
//////////////////////////////////////////////////////////////

$ac        = new \Application\DeskPRO\Entity\ArticleCategory();
$ac->title = 'Germany Info';
$ac->addUsergroup($USERGROUP_EVERYONE);
$em->persist($ac);

for ($i = 0; $i < 15; ++$i) {
    $a = new \Application\DeskPRO\Entity\Article();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000).'<br><br>'.$faker->text(3000));
    $a->setCategories(array($ac));
    $a->setStatus(\Application\DeskPRO\Entity\ContentAbstract::STATUS_PUBLISHED);
    $a->setPerson($publisher);
    $em->persist($a);
}

$ac        = new \Application\DeskPRO\Entity\ArticleCategory();
$ac->title = 'Finland Info';
$ac->addUsergroup($USERGROUP_EVERYONE);
$em->persist($ac);

for ($i = 0; $i < 15; ++$i) {
    $a = new \Application\DeskPRO\Entity\Article();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000).'<br><br>'.$faker->text(3000));
    $a->setCategories(array($ac, $em->getRepository('DeskPRO:ArticleCategory')->find(1)));
    $a->setStatus(\Application\DeskPRO\Entity\ContentAbstract::STATUS_PUBLISHED);
    $a->setPerson($publisher);
    $em->persist($a);
}

$ac        = new \Application\DeskPRO\Entity\ArticleCategory();
$ac->title = 'Japan Info';
$ac->addUsergroup($USERGROUP_EVERYONE);
$em->persist($ac);

for ($i = 0; $i < 15; ++$i) {
    $a = new \Application\DeskPRO\Entity\Article();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000).'<br><br>'.$faker->text(3000));
    $a->setCategories(array($ac));
    $a->setStatus(\Application\DeskPRO\Entity\ContentAbstract::STATUS_PUBLISHED);
    $a->setPerson($publisher);
    $em->persist($a);
}

//////////////////////////////////////////////////////////////
// news
//////////////////////////////////////////////////////////////

$ac        = new \Application\DeskPRO\Entity\NewsCategory();
$ac->title = 'Canada Info';
$ac->addUsergroup($USERGROUP_EVERYONE);
$em->persist($ac);

for ($i = 0; $i < 15; ++$i) {
    $a = new \Application\DeskPRO\Entity\News();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000).'<br><br>'.$faker->text(3000));
    $a->setCategory($ac);
    $a->setStatus(\Application\DeskPRO\Entity\ContentAbstract::STATUS_PUBLISHED);
    $a->setPerson($publisher);
    $em->persist($a);
}

$ac        = new \Application\DeskPRO\Entity\NewsCategory();
$ac->title = 'U.S. Info';
$ac->addUsergroup($USERGROUP_EVERYONE);
$em->persist($ac);

for ($i = 0; $i < 15; ++$i) {
    $a = new \Application\DeskPRO\Entity\News();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000).'<br><br>'.$faker->text(3000));
    $a->setCategory($ac);
    $a->setStatus(\Application\DeskPRO\Entity\ContentAbstract::STATUS_PUBLISHED);
    $a->setPerson($publisher);
    $em->persist($a);
}

$ac = $em->getRepository('DeskPRO:NewsCategory')->find(1);

for ($i = 0; $i < 15; ++$i) {
    $a = new \Application\DeskPRO\Entity\News();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000).'<br><br>'.$faker->text(3000));
    $a->setCategory($ac);
    $a->setStatus(\Application\DeskPRO\Entity\ContentAbstract::STATUS_PUBLISHED);
    $a->setPerson($publisher);
    $em->persist($a);
}

//////////////////////////////////////////////////////////////
// downloads
//////////////////////////////////////////////////////////////

if (!function_exists('make_blob')) {
    function make_blob(\Doctrine\ORM\EntityManager $em)
    {
        $storage = new \Application\DeskPRO\BlobStorage\DeskproBlobStorage($em);

        $blob = $storage->createBlobRecordFromFile(
            realpath(__DIR__.'/../../../../../web/images/dp-logo-130.png'),
            'dp-logo-130.png',
            'image/png'
        );

        $blob->authcode = rand(0, 18).rand(0, 18).rand(0, 18).rand(0, 18).rand(0, 18).rand(0, 18);

        return $blob;
    }
}

$ac        = new \Application\DeskPRO\Entity\DownloadCategory();
$ac->title = 'Canada Info';
$ac->addUsergroup($USERGROUP_EVERYONE);
$em->persist($ac);

for ($i = 0; $i < 15; ++$i) {
    $blob = make_blob($em);
    $a    = new \Application\DeskPRO\Entity\Download();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000).'<br><br>'.$faker->text(3000));
    $a->setCategory($ac);
    $a->setStatus(\Application\DeskPRO\Entity\ContentAbstract::STATUS_PUBLISHED);
    $a->setPerson($publisher);
    $a->setBlob($blob);
    $em->persist($a);
}

$ac        = new \Application\DeskPRO\Entity\DownloadCategory();
$ac->title = 'U.S. Info';
$ac->addUsergroup($USERGROUP_EVERYONE);
$em->persist($ac);

for ($i = 0; $i < 15; ++$i) {
    $blob = make_blob($em);
    $a    = new \Application\DeskPRO\Entity\Download();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000).'<br><br>'.$faker->text(3000));
    $a->setCategory($ac);
    $a->setStatus(\Application\DeskPRO\Entity\ContentAbstract::STATUS_PUBLISHED);
    $a->setPerson($publisher);
    $a->setBlob($blob);
    $em->persist($a);
}

$ac = $em->getRepository('DeskPRO:DownloadCategory')->find(1);

for ($i = 0; $i < 15; ++$i) {
    $blob = make_blob($em);
    $a    = new \Application\DeskPRO\Entity\Download();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000).'<br><br>'.$faker->text(3000));
    $a->setCategory($ac);
    $a->setStatus(\Application\DeskPRO\Entity\ContentAbstract::STATUS_PUBLISHED);
    $a->setPerson($publisher);
    $a->setBlob($blob);
    $em->persist($a);
}

//////////////////////////////////////////////////////////////
// feedback
//////////////////////////////////////////////////////////////

// these are inserted already
$DEFAULT_IDEA_CAT = $em->getRepository('DeskPRO:FeedbackCategory')->find(1);
$FEEDBACK_FEATURE = $em->getRepository('DeskPRO:FeedbackCategory')->find(2);
$FEEDBACK_FEATURE->addUsergroup($USERGROUP_EVERYONE);
$FEEDBACK_BUG = $em->getRepository('DeskPRO:FeedbackCategory')->find(3);
$FEEDBACK_BUG->addUsergroup($USERGROUP_EVERYONE);
$em->flush($FEEDBACK_FEATURE);
$em->flush($FEEDBACK_BUG);

if (!function_exists('rand_fb_status_pair')) {
    function rand_fb_status_pair(\Doctrine\ORM\EntityManager $em)
    {
        $array = array();

        $opts = array(
            \Application\DeskPRO\Entity\Feedback::STATUS_ACTIVE,
            \Application\DeskPRO\Entity\Feedback::STATUS_CLOSED,
        );
        $array['status'] = $opts[rand(0, (count($opts) - 1))];

        $scs = $em->getRepository('DeskPRO:FeedbackStatusCategory')->findBy(
            array(
                'status_type' => $array['status'],
            )
        );

        $array['status_category'] = $scs[rand(0, (count($scs) - 1))];

        return $array;
    }
}

for ($i = 0; $i < 30; ++$i) {
    $a = new \Application\DeskPRO\Entity\Feedback();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(750).'<br><br>'.$faker->text(1000));
    $a->setCategory($DEFAULT_IDEA_CAT);
    $fbinfo = rand_fb_status_pair($em);
    $a->setStatus($fbinfo['status']);
    $a->setStatusCategory($fbinfo['status_category']);
    $a->setPerson($publisher);
    $em->persist($a);
}

for ($i = 0; $i < 30; ++$i) {
    $a = new \Application\DeskPRO\Entity\Feedback();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(750).'<br><br>'.$faker->text(1000));
    $a->setCategory($FEEDBACK_BUG);
    $fbinfo = rand_fb_status_pair($em);
    $a->setStatus($fbinfo['status']);
    $a->setStatusCategory($fbinfo['status_category']);
    $a->setPerson($publisher);
    $em->persist($a);
}

$ac = $em->getRepository('DeskPRO:DownloadCategory')->find(1);

for ($i = 0; $i < 30; ++$i) {
    $a = new \Application\DeskPRO\Entity\Feedback();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(750).'<br><br>'.$faker->text(1000));
    $a->setCategory($FEEDBACK_FEATURE);
    $fbinfo = rand_fb_status_pair($em);
    $a->setStatus($fbinfo['status']);
    $a->setStatusCategory($fbinfo['status_category']);
    $a->setPerson($publisher);
    $em->persist($a);
}

$em->flush();

//////////////////////////////////////////////////////////////
// widgets
//////////////////////////////////////////////////////////////

foreach (array('default', 'foo', 'bar', 'baz') as $type) {
    for ($i = 1; $i <= 10; ++$i) {
        $a = new \DeskPRO\Bundle\AppBundle\Entity\SandboxWidget();
        $a->setType($type);
        $a->setName(ucfirst($type).' '.$i);
        $a->setInventory(5);
        $em->persist($a);
    }
}

$em->flush();

################################################################################
# TEMPORARY TEST DATA: Labels
################################################################################

$em->getConnection()->executeUpdate(
    "
    INSERT INTO `label_defs`
        (`label_type`, `label`, `color`, `total`)
    VALUES
        ('feedback', 'First feedback label', 'red', 0),
        ('feedback', 'Second feedback label', 'white', 0),
        ('feedback', 'Third feedback label', 'red', 0),
        ('organization', 'First organization label', 'red', 1),
        ('organization', 'Second organization label', 'blue', 2),
        ('organization', 'Third organization label', 'green', 42),
        ('person', 'First person label', 'white', 1),
        ('person', 'Second person label', 'red', 3),
        ('person', 'Third person label', 'yellow', 3)
    ;
"
);

################################################################################
# Add some brands to test different themes
################################################################################
//INSERT INTO brands (name, theme_id) VALUES ('Default Brand', 'standard')
$em->getConnection()->executeUpdate(
    "
INSERT INTO `brands` (`id`, `logo_blob_id`, `name`, `theme_id`)
VALUES
	(1, NULL, 'Standard Theme', 'standard'),
	(2, NULL, 'Sidebar Theme', 'sidebar'),
	(3, NULL, 'Simple Theme', 'simple'),
	(4, NULL, 'Tab Bar Theme', 'tabbar');
"
);

################################################################################
# Ticket Form
################################################################################
$em->getConnection()->executeUpdate(
    "
INSERT INTO `custom_def_ticket` (`id`, `parent_id`, `app_id`, `js_class`, `has_form_template`, `has_display_template`, `title`, `description`, `handler_class`, `options`, `is_user_enabled`, `is_enabled`, `display_order`, `default_value`, `is_agent_field`)
VALUES
	(1, NULL, NULL, '', 0, 0, 'Custom Text Box', 'This is a custom text box description', 'Application\\\\DeskPRO\\\\CustomFields\\\\Handler\\\\Text', X'613A303A7B7D', 1, 1, 0, NULL, 0),
	(2, NULL, NULL, '', 0, 0, 'Custom Multi-Line Text Box', 'The description of a custom multi-line text box', 'Application\\\\DeskPRO\\\\CustomFields\\\\Handler\\\\Textarea', X'613A303A7B7D', 1, 1, 0, NULL, 0),
	(3, NULL, NULL, '', 0, 0, 'Custom Single Checkbox', 'Custom (toggle type) checkbox description', 'Application\\\\DeskPRO\\\\CustomFields\\\\Handler\\\\Toggle', X'613A303A7B7D', 1, 1, 0, '', 0),
	(4, NULL, NULL, '', 0, 0, 'Custom Date', 'Custom date description', 'Application\\\\DeskPRO\\\\CustomFields\\\\Handler\\\\Date', X'613A333A7B733A383A227265717569726564223B623A303B733A31343A226167656E745F7265717569726564223B623A303B733A31393A22646174655F76616C69645F74696D657A6F6E65223B733A31363A22416D65726963612F4E65775F596F726B223B7D', 1, 1, 0, NULL, 0),
	(5, NULL, NULL, '', 0, 0, 'Custom Date Time', 'Custom date/time description', 'Application\\\\DeskPRO\\\\CustomFields\\\\Handler\\\\DateTime', X'613A333A7B733A383A227265717569726564223B623A303B733A31343A226167656E745F7265717569726564223B623A303B733A31393A22646174655F76616C69645F74696D657A6F6E65223B733A31363A22416D65726963612F4E65775F596F726B223B7D', 1, 1, 0, NULL, 0),
	(6, NULL, NULL, '', 0, 0, 'Custom Display', 'This is a custom HTML display', 'Application\\\\DeskPRO\\\\CustomFields\\\\Handler\\\\Display', X'613A313A7B733A343A2268746D6C223B733A33343A223C68313E437573746F6D2048544D4C20446973706C6179204669656C643C2F68313E223B7D', 1, 1, 0, NULL, 0),
	(7, NULL, NULL, '', 0, 0, 'Custom Hidden', 'this is a custom hidden field description', 'Application\\\\DeskPRO\\\\CustomFields\\\\Handler\\\\Hidden', X'613A303A7B7D', 1, 1, 0, 'VALUE', 0),
	(8, NULL, NULL, '', 0, 0, 'Custom Radio Group', 'these are a custom pre-defined \"radio\" choices', 'Application\\\\DeskPRO\\\\CustomFields\\\\Handler\\\\Choice', X'613A323A7B733A383A226D756C7469706C65223B623A303B733A383A22657870616E646564223B623A313B7D', 1, 1, 0, NULL, 0),
	(9, 8, NULL, '', 0, 0, 'Radio 1', '', NULL, X'613A313A7B733A323A226362223B733A323A223139223B7D', 1, 1, 10, NULL, 0),
	(10, 8, NULL, '', 0, 0, 'Radio 2', '', NULL, X'613A313A7B733A323A226362223B733A323A223230223B7D', 1, 1, 20, NULL, 0),
	(11, 8, NULL, '', 0, 0, 'Radio 3', '', NULL, X'613A313A7B733A323A226362223B733A323A223231223B7D', 1, 1, 30, NULL, 0),
	(12, 8, NULL, '', 0, 0, 'Radio 4', '', NULL, X'613A313A7B733A323A226362223B733A323A223232223B7D', 1, 1, 40, NULL, 0),
	(13, 8, NULL, '', 0, 0, 'Radio 5', '', NULL, X'613A313A7B733A323A226362223B733A323A223233223B7D', 1, 1, 50, NULL, 0),
	(14, 8, NULL, '', 0, 0, 'Radio 6', '', NULL, X'613A313A7B733A323A226362223B733A323A223234223B7D', 1, 1, 60, NULL, 0),
	(15, 8, NULL, '', 0, 0, 'Radio 7', '', NULL, X'613A313A7B733A323A226362223B733A323A223235223B7D', 1, 1, 70, NULL, 0),
	(16, 8, NULL, '', 0, 0, 'Radio 8', '', NULL, X'613A313A7B733A323A226362223B733A323A223236223B7D', 1, 1, 80, NULL, 0),
	(17, NULL, NULL, '', 0, 0, 'Custom Select Box', 'these are a custom pre-defined \"select box\" choices', 'Application\\\\DeskPRO\\\\CustomFields\\\\Handler\\\\Choice', X'613A323A7B733A383A226D756C7469706C65223B623A303B733A383A22657870616E646564223B623A303B7D', 1, 1, 0, NULL, 0),
	(18, 17, NULL, '', 0, 0, 'Select 1', '', NULL, X'613A313A7B733A323A226362223B733A323A223237223B7D', 1, 1, 10, NULL, 0),
	(19, 17, NULL, '', 0, 0, 'Select 2', '', NULL, X'613A313A7B733A323A226362223B733A323A223238223B7D', 1, 1, 20, NULL, 0),
	(20, 17, NULL, '', 0, 0, 'Select 3', '', NULL, X'613A313A7B733A323A226362223B733A323A223239223B7D', 1, 1, 30, NULL, 0),
	(21, 17, NULL, '', 0, 0, 'Select 4', '', NULL, X'613A313A7B733A323A226362223B733A323A223330223B7D', 1, 1, 40, NULL, 0),
	(22, 17, NULL, '', 0, 0, 'Select 5', '', NULL, X'613A313A7B733A323A226362223B733A323A223331223B7D', 1, 1, 50, NULL, 0),
	(23, 17, NULL, '', 0, 0, 'Select 6', '', NULL, X'613A313A7B733A323A226362223B733A323A223332223B7D', 1, 1, 60, NULL, 0),
	(24, 17, NULL, '', 0, 0, 'Select 7', '', NULL, X'613A313A7B733A323A226362223B733A323A223333223B7D', 1, 1, 70, NULL, 0),
	(25, NULL, NULL, '', 0, 0, 'Custom Checkbox Group', 'these are a custom pre-defined \"checkbox\" choices', 'Application\\\\DeskPRO\\\\CustomFields\\\\Handler\\\\Choice', X'613A323A7B733A383A226D756C7469706C65223B623A313B733A383A22657870616E646564223B623A313B7D', 1, 1, 0, NULL, 0),
	(26, 25, NULL, '', 0, 0, 'Checkbox 1', '', NULL, X'613A313A7B733A323A226362223B733A323A223334223B7D', 1, 1, 10, NULL, 0),
	(27, 25, NULL, '', 0, 0, 'Checkbox 2', '', NULL, X'613A313A7B733A323A226362223B733A323A223335223B7D', 1, 1, 20, NULL, 0),
	(28, 25, NULL, '', 0, 0, 'Checkbox 3', '', NULL, X'613A313A7B733A323A226362223B733A323A223336223B7D', 1, 1, 30, NULL, 0),
	(29, 25, NULL, '', 0, 0, 'Checkbox 4', '', NULL, X'613A313A7B733A323A226362223B733A323A223337223B7D', 1, 1, 40, NULL, 0),
	(30, 25, NULL, '', 0, 0, 'Checkbox 5', '', NULL, X'613A313A7B733A323A226362223B733A323A223338223B7D', 1, 1, 50, NULL, 0),
	(31, 25, NULL, '', 0, 0, 'Checkbox 6', '', NULL, X'613A313A7B733A323A226362223B733A323A223339223B7D', 1, 1, 60, NULL, 0),
	(32, 25, NULL, '', 0, 0, 'Checkbox 7', '', NULL, X'613A313A7B733A323A226362223B733A323A223430223B7D', 1, 1, 70, NULL, 0),
	(33, 25, NULL, '', 0, 0, 'Checkbox 8', '', NULL, X'613A313A7B733A323A226362223B733A323A223431223B7D', 1, 1, 80, NULL, 0),
	(34, NULL, NULL, '', 0, 0, 'Custom Multi-Select Box', 'these are a custom pre-defined \"multi-select box\" choices', 'Application\\\\DeskPRO\\\\CustomFields\\\\Handler\\\\Choice', X'613A323A7B733A383A226D756C7469706C65223B623A313B733A383A22657870616E646564223B623A303B7D', 1, 1, 0, NULL, 0),
	(35, 34, NULL, '', 0, 0, 'Choice 1', '', NULL, X'613A313A7B733A323A226362223B733A323A223432223B7D', 1, 1, 10, NULL, 0),
	(36, 34, NULL, '', 0, 0, 'Choice 2', '', NULL, X'613A313A7B733A323A226362223B733A323A223433223B7D', 1, 1, 20, NULL, 0),
	(37, 34, NULL, '', 0, 0, 'Choice 3', '', NULL, X'613A313A7B733A323A226362223B733A323A223434223B7D', 1, 1, 30, NULL, 0),
	(38, 34, NULL, '', 0, 0, 'Choice 4', '', NULL, X'613A313A7B733A323A226362223B733A323A223435223B7D', 1, 1, 40, NULL, 0),
	(39, 34, NULL, '', 0, 0, 'Choice 5', '', NULL, X'613A313A7B733A323A226362223B733A323A223436223B7D', 1, 1, 50, NULL, 0),
	(40, 34, NULL, '', 0, 0, 'Choice 6', '', NULL, X'613A313A7B733A323A226362223B733A323A223437223B7D', 1, 1, 60, NULL, 0),
	(41, 34, NULL, '', 0, 0, 'Choice 7', '', NULL, X'613A313A7B733A323A226362223B733A323A223438223B7D', 1, 1, 70, NULL, 0),
	(42, 34, NULL, '', 0, 0, 'Choice 8', '', NULL, X'613A313A7B733A323A226362223B733A323A223439223B7D', 1, 1, 80, NULL, 0);

"
);
$em->getConnection()->executeUpdate(
    "
REPLACE INTO `ticket_layouts` (`id`, `department_id`, `is_enabled`, `user_layout`, `agent_layout`, `date_updated`)
VALUES
	(1, NULL, 1, '{\"@CLASS\":\"Application\\\\\\\\DeskPRO\\\\\\\\TicketLayout\\\\\\\\Layout\",\"@DATA\":{\"version\":1,\"fields\":[{\"version\":1,\"field_type\":\"department\",\"field_id\":null,\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"always\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"subject\",\"field_id\":null,\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"always\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"priority\",\"field_id\":null,\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"category\",\"field_id\":null,\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"product\",\"field_id\":null,\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"ticket_field\",\"field_id\":\"1\",\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"ticket_field\",\"field_id\":\"2\",\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"ticket_field\",\"field_id\":\"3\",\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"ticket_field\",\"field_id\":\"4\",\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"ticket_field\",\"field_id\":\"5\",\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"ticket_field\",\"field_id\":\"6\",\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"ticket_field\",\"field_id\":\"7\",\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"ticket_field\",\"field_id\":\"8\",\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"ticket_field\",\"field_id\":\"17\",\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"ticket_field\",\"field_id\":\"25\",\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"ticket_field\",\"field_id\":\"34\",\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"product\",\"field_id\":null,\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"category\",\"field_id\":null,\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"priority\",\"field_id\":null,\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"message\",\"field_id\":null,\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"always\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"user_email\",\"field_id\":null,\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"always\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"attach\",\"field_id\":null,\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"always\",\"on_editticket\":true}}]}}', '{\"@CLASS\":\"Application\\\\\\\\DeskPRO\\\\\\\\TicketLayout\\\\\\\\Layout\",\"@DATA\":{\"version\":1,\"fields\":[{\"version\":1,\"field_type\":\"department\",\"field_id\":null,\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"always\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"subject\",\"field_id\":null,\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"always\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"ticket_field\",\"field_id\":\"1\",\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"ticket_field\",\"field_id\":\"2\",\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"ticket_field\",\"field_id\":\"3\",\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"ticket_field\",\"field_id\":\"4\",\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"ticket_field\",\"field_id\":\"5\",\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"ticket_field\",\"field_id\":\"6\",\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"ticket_field\",\"field_id\":\"7\",\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"ticket_field\",\"field_id\":\"8\",\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"ticket_field\",\"field_id\":\"17\",\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"ticket_field\",\"field_id\":\"25\",\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"ticket_field\",\"field_id\":\"34\",\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"value\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"message\",\"field_id\":null,\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"always\",\"on_editticket\":true}},{\"version\":1,\"field_type\":\"user_email\",\"field_id\":null,\"options\":{\"criteria\":null,\"on_newticket\":true,\"on_viewticket\":true,\"on_viewticket_mode\":\"always\",\"on_editticket\":true}}]}}', '2015-10-29 14:51:32');

"
);

$em->getConnection()->executeUpdate(
    "
INSERT INTO `products` (`id`, `parent_id`, `title`, `display_order`, `depth`, `root`)
VALUES
	(1, NULL, 'Product 1', 10, 0, NULL),
	(2, NULL, 'Product 2', 20, 0, NULL),
	(3, NULL, 'Product 3', 30, 0, NULL);
INSERT INTO `ticket_priorities` (`id`, `title`, `priority`)
VALUES
	(1, 'Priority 1', 10),
	(2, 'Priority 2', 20),
	(3, 'Priority 3', 30);
INSERT INTO `ticket_categories` (`id`, `parent_id`, `title`, `display_order`)
VALUES
	(1, NULL, 'Category 1', 10),
	(2, NULL, 'Category 2', 20),
	(3, NULL, 'Category 3', 30);
INSERT INTO `ticket_workflows` (`id`, `title`, `display_order`)
VALUES
	(4, 'Workflow 1', 10),
	(5, 'Workflow 2', 20),
	(6, 'Workflow 3', 30);
"
);
