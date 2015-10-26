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
INSERT INTO `feedback` (`id`, `status_category_id`, `category_id`, `person_id`, `language_id`, `hidden_status`, `validating`, `popularity`, `title`, `slug`, `content`, `view_count`, `total_rating`, `num_comments`, `num_ratings`, `status`, `date_created`, `date_published`) VALUES
(1, 5, 1, 1, NULL, 'validating', NULL, 0, 'example-suggestion', 'Example Suggestion', 'This is an example suggestion. Feel free to edit or delete it from the agent interface.', 0, 1, 0, 2, 'new', '2015-08-13 11:33:33', '2015-08-13 11:33:33'),
(2, 1, 1, 1, NULL, 'deleted', NULL, 0, 'Test feedback 1', 'slug-to-feedback-1', 'Content of test feedback 1', 0, 3, 0, 4, 'hidden', '2015-08-01 00:00:00', NULL),
(3, 1, 2, 1, NULL, NULL, NULL, 0, 'Test feedback 2', 'slug-to-feedback-2', 'Content of test feedback 2', 0, 5, 0, 6, 'active', '2015-08-02 00:00:00', NULL),
(4, 2, 3, 1, NULL, 'validating', NULL, 0, 'Test feedback 3', 'slug-to-feedback-3', 'Content of test feedback 3', 0, 0, 0, 0, 'active', '2015-08-03 00:00:00', NULL),
(5, 1, 1, 1, NULL, 'spam', NULL, 0, 'Test feedback 4', 'slug-to-feedback-4', 'Content of test feedback 4', 0, 1, 0, 1, 'hidden', '2015-08-04 00:00:00', NULL),
(6, 5, 1, 1, NULL, 'validating', NULL, 0, 'Test feedback 5', 'slug-to-feedback-5', 'Content of test feedback 5', 0, 2, 0, 1, 'closed', '2015-08-05 00:00:00', NULL),
(7, 1, 2, 1, NULL, 'validating', NULL, 15, 'Test feedback 6', 'slug-to-feedback-6', 'I am trying to implement Infinite Scrolling on a gridview to speed up my web application, since the gridview is being bound to a sql query that returns thousands of records at start (its the clients wish, and I cant change that.)', 0, 3, 0, 1, 'new', '2015-08-10 00:00:00', NULL);
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

################################################################################
# TEMPORARY TEST DATA: Tickets
################################################################################

$em->getConnection()->executeUpdate("
SET FOREIGN_KEY_CHECKS=0;

INSERT INTO `tickets`
(`id`, `parent_ticket_id`, `language_id`, `department_id`, `category_id`, `priority_id`, `workflow_id`, `product_id`, `person_id`, `person_email_id`, `person_email_validating_id`, `agent_id`, `agent_team_id`, `organization_id`, `linked_chat_id`, `email_account_id`, `locked_by_agent`, `ref`, `auth`, `sent_to_address`, `email_account_address`, `creation_system`, `creation_system_option`, `ticket_hash`, `status`, `hidden_status`, `validating`, `is_hold`, `urgency`, `count_agent_replies`, `count_user_replies`, `feedback_rating`, `date_feedback_rating`, `date_created`, `date_resolved`, `date_archived`, `date_first_agent_assign`, `date_first_agent_reply`, `date_last_agent_reply`, `date_last_user_reply`, `date_agent_waiting`, `date_user_waiting`, `date_status`, `total_user_waiting`, `total_to_first_reply`, `date_locked`, `has_attachments`, `subject`, `original_subject`, `properties`, `worst_sla_status`, `waiting_times`)

VALUES
(25, NULL, 1, 3, NULL, NULL, NULL, NULL, 2, 1, NULL, 2, 1, 1, 4, NULL, NULL, '0.41111157965949885', '', '', '', '', '', '', 'awaiting_user', 'temp', NULL, 0, 2, 5, 5, 4, '2015-04-10 10:27:01', '2014-07-04 10:27:01', '2015-01-19 10:27:01', '2014-08-07 10:27:01', '2014-10-23 10:27:01', '2014-06-26 10:27:01', '2014-12-14 10:27:01', '2015-07-19 10:27:01', '2015-09-27 10:27:01', '2014-12-01 10:27:01', '2015-09-13 10:27:01', 2, 4, NULL, 3, 'Test ticket #25', 'Test ticket #25', NULL, NULL, NULL),
(26, NULL, 1, 2, NULL, NULL, NULL, NULL, 4, 2, NULL, 4, 2, 1, 1, NULL, NULL, '0.06711542333207599', '', '', '', '', '', '', 'awaiting_user', 'validating', NULL, 1, 4, 5, 7, 4, '2015-03-28 10:27:01', '2015-04-18 10:27:01', '2015-01-01 10:27:01', '2014-09-20 10:27:01', '2015-07-22 10:27:01', '2015-02-12 10:27:01', '2014-07-09 10:27:01', '2015-08-01 10:27:01', '2014-06-21 10:27:01', '2015-04-27 10:27:01', '2014-09-02 10:27:01', 1, 3, NULL, 2, 'Test ticket #26', 'Test ticket #26', NULL, NULL, NULL),
(27, 70, 1, 3, NULL, NULL, NULL, NULL, NULL, 2, NULL, 4, 1, 1, 1, NULL, NULL, '0.885407295902639', '', '', '', '', '', '', 'awaiting_user', 'temp', NULL, 0, 3, 10, 5, 1, '2015-03-06 10:27:01', '2014-10-01 10:27:01', '2015-03-10 10:27:01', '2014-07-20 10:27:01', NULL, '2015-06-24 10:27:01', '2015-01-14 10:27:01', '2015-09-19 10:27:01', '2014-12-21 10:27:01', '2014-09-02 10:27:01', '2015-05-29 10:27:01', 4, 4, NULL, 1, 'Test ticket #27', 'Test ticket #27', NULL, NULL, NULL),
(28, 107, 1, 3, NULL, NULL, NULL, NULL, 2, 1, NULL, 5, 2, 1, 1, NULL, 1, '0.9761235192195732', '', '', '', '', '', '', 'awaiting_user', 'validating', NULL, 1, 3, 9, 5, 3, '2014-10-07 10:27:01', '2014-07-20 10:27:01', '2015-05-30 10:27:01', '2014-11-25 10:27:01', '2015-03-28 10:27:01', '2015-09-17 10:27:01', '2015-09-17 10:27:01', '2015-08-24 10:27:01', '2015-05-01 10:27:01', '2015-04-23 10:27:01', '2014-10-12 10:27:01', 3, 4, NULL, 1, 'Test ticket #28', 'Test ticket #28', NULL, NULL, NULL),
(29, NULL, 1, 1, NULL, NULL, NULL, NULL, 3, 2, NULL, 1, 1, 1, 3, NULL, NULL, '0.5586969708639169', '', '', '', '', '', '', 'resolved', 'temp', NULL, 1, 1, 10, 3, 3, '2014-08-21 10:27:01', '2015-01-21 10:27:01', '2015-08-07 10:27:01', '2015-09-08 10:27:01', '2014-06-30 10:27:01', '2015-02-16 10:27:01', '2015-01-03 10:27:01', '2015-04-06 10:27:01', '2015-07-06 10:27:01', '2014-08-17 10:27:01', '2014-12-10 10:27:01', 3, 4, NULL, 2, 'Test ticket #29', 'Test ticket #29', NULL, NULL, NULL),
(30, NULL, 1, 2, NULL, NULL, NULL, NULL, 2, 1, NULL, 5, 2, 1, 4, NULL, 1, '0.23944560926355943', '', '', '', '', '', '', 'awaiting_agent', 'deleted', NULL, 1, 2, 5, 1, 1, '2015-07-01 10:27:01', '2014-10-20 10:27:01', '2014-06-28 10:27:01', '2015-01-05 10:27:01', NULL, '2015-07-19 10:27:01', '2014-07-05 10:27:01', '2015-08-11 10:27:01', '2014-08-24 10:27:01', '2014-09-26 10:27:01', '2015-05-08 10:27:01', 2, 2, NULL, 2, 'Test ticket #30', 'Test ticket #30', NULL, NULL, NULL),
(31, 111, 1, 3, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, 1, 1, 2, NULL, NULL, '0.6169171590515572', '', '', '', '', '', '', 'archived', 'temp', NULL, 0, 3, 8, 7, 1, '2015-05-15 10:27:01', '2015-05-28 10:27:01', '2015-02-22 10:27:01', '2015-02-06 10:27:01', '2015-09-02 10:27:01', '2014-07-19 10:27:01', '2015-06-04 10:27:01', '2014-12-22 10:27:01', '2015-07-25 10:27:01', '2015-10-05 10:27:01', '2014-12-22 10:27:01', 4, 3, NULL, 3, 'Test ticket #31', 'Test ticket #31', NULL, NULL, NULL),
(32, NULL, 1, 2, NULL, NULL, NULL, NULL, 4, 1, NULL, 1, 1, 1, 1, NULL, NULL, '0.23951134853019504', '', '', '', '', '', '', 'awaiting_agent', 'validating', NULL, 1, 3, 7, 4, 4, '2014-07-02 10:27:01', '2015-05-17 10:27:01', '2014-11-10 10:27:01', '2015-02-19 10:27:01', NULL, '2015-07-09 10:27:01', '2014-06-08 10:27:01', '2015-04-30 10:27:01', '2014-10-28 10:27:01', '2015-02-07 10:27:01', '2015-04-10 10:27:01', 2, 3, NULL, 1, 'Test ticket #32', 'Test ticket #32', NULL, NULL, NULL),
(33, NULL, 1, 2, NULL, NULL, NULL, NULL, 5, 1, NULL, 2, 2, 1, 1, NULL, NULL, '0.4386885105061238', '', '', '', '', '', '', 'awaiting_agent', 'deleted', '0', 0, 3, 3, 5, 1, '2014-12-18 10:27:01', '2015-02-22 10:27:01', '2015-01-21 10:27:01', '2015-06-14 10:27:01', NULL, '2015-05-08 10:27:01', '2014-10-05 10:27:01', '2014-09-25 10:27:01', '2014-12-26 10:27:01', '2014-12-15 10:27:01', '2015-06-03 10:27:01', 2, 3, NULL, 1, 'Test ticket #33', 'Test ticket #33', NULL, NULL, NULL),
(34, NULL, 1, 1, NULL, NULL, NULL, NULL, 2, 4, NULL, 4, NULL, 1, 3, NULL, NULL, '0.7841362783537584', '', '', '', '', '', '', 'hidden', 'spam', NULL, 0, 1, 10, 5, 2, '2015-09-08 10:27:01', '2015-02-14 10:27:01', '2015-07-10 10:27:01', '2015-02-07 10:27:01', NULL, '2014-06-02 10:27:01', '2015-05-30 10:27:01', '2015-04-14 10:27:01', '2014-05-31 10:27:01', '2014-07-21 10:27:01', '2015-02-17 10:27:01', 3, 4, NULL, 3, 'Test ticket #34', 'Test ticket #34', NULL, NULL, NULL),
(35, NULL, 1, 3, NULL, NULL, NULL, NULL, 2, 4, NULL, NULL, NULL, 1, 3, NULL, 1, '0.1288598078571817', '', '', '', '', '', '', 'awaiting_user', 'deleted', NULL, 1, 4, 8, 2, 3, '2015-06-06 10:27:01', '2014-11-13 10:27:01', '2015-01-07 10:27:01', '2014-09-19 10:27:01', NULL, '2014-11-01 10:27:01', '2014-08-24 10:27:01', '2015-09-09 10:27:01', '2014-08-20 10:27:01', '2014-06-10 10:27:01', '2015-04-07 10:27:01', 4, 3, NULL, 3, 'Test ticket #35', 'Test ticket #35', NULL, NULL, NULL),
(36, NULL, 1, 1, NULL, NULL, NULL, NULL, 1, 1, NULL, 5, NULL, 1, 4, NULL, 3, '0.8686743014200351', '', '', '', '', '', '', 'archived', 'temp', NULL, 1, 4, 3, 1, 3, '2015-09-10 10:27:01', '2015-05-25 10:27:01', '2015-07-04 10:27:01', '2015-07-26 10:27:01', NULL, '2015-04-04 10:27:01', '2015-03-31 10:27:01', '2014-09-09 10:27:01', '2014-09-07 10:27:01', '2014-12-12 10:27:01', '2014-11-30 10:27:01', 2, 3, NULL, 3, 'Test ticket #36', 'Test ticket #36', NULL, NULL, NULL),
(37, NULL, 1, 2, NULL, NULL, NULL, NULL, 4, 4, NULL, 1, 1, 1, 2, NULL, NULL, '0.16051921170253233', '', '', '', '', '', '', 'resolved', 'deleted', NULL, 1, 3, 10, 1, 2, '2014-12-15 10:27:01', '2014-07-11 10:27:01', '2014-09-24 10:27:01', '2015-09-05 10:27:01', NULL, '2015-08-25 10:27:01', '2015-05-24 10:27:01', '2015-08-18 10:27:01', '2014-10-26 10:27:01', '2015-07-17 10:27:01', '2014-09-30 10:27:01', 1, 4, NULL, 1, 'Test ticket #37', 'Test ticket #37', NULL, NULL, NULL),
(38, 43, 1, 1, NULL, NULL, NULL, NULL, 2, 2, NULL, 4, 2, 1, 2, NULL, NULL, '0.16649208047100536', '', '', '', '', '', '', 'archived', 'deleted', NULL, 1, 4, 4, 4, 4, '2015-09-06 10:27:01', '2014-09-15 10:27:01', '2014-10-28 10:27:01', '2015-08-10 10:27:01', NULL, '2015-05-21 10:27:01', '2014-08-26 10:27:01', '2015-06-08 10:27:01', '2014-09-17 10:27:01', '2015-08-04 10:27:01', '2015-04-22 10:27:01', 2, 2, NULL, 2, 'Test ticket #38', 'Test ticket #38', NULL, NULL, NULL),
(39, NULL, 1, 2, NULL, NULL, NULL, NULL, 5, 2, NULL, 4, 1, 1, 2, NULL, 3, '0.6796906904128294', '', '', '', '', '', '', 'hidden', 'validating', '1', 1, 1, 3, 7, 2, '2015-03-12 10:27:01', '2014-10-25 10:27:01', '2015-06-16 10:27:01', '2015-09-15 10:27:01', '2015-01-05 10:27:01', '2014-12-03 10:27:01', '2015-03-02 10:27:01', '2015-04-18 10:27:01', '2015-03-17 10:27:01', '2015-10-03 10:27:01', '2014-08-21 10:27:01', 1, 1, NULL, 3, 'Test ticket #39', 'Test ticket #39', NULL, NULL, NULL),
(40, NULL, 1, 1, NULL, NULL, NULL, NULL, 3, 1, NULL, 5, 1, 1, 4, NULL, 1, '0.2509627968547519', '', '', '', '', '', '', 'awaiting_agent', 'temp', NULL, 0, 2, 5, 10, 2, '2014-10-24 10:27:01', '2014-12-12 10:27:01', '2014-07-09 10:27:01', '2014-09-25 10:27:01', NULL, '2014-06-10 10:27:01', '2014-10-21 10:27:01', '2014-12-03 10:27:01', '2014-06-09 10:27:01', '2015-10-02 10:27:01', '2015-07-28 10:27:01', 3, 4, NULL, 3, 'Test ticket #40', 'Test ticket #40', NULL, NULL, NULL),
(41, NULL, 1, 2, NULL, NULL, NULL, NULL, NULL, 3, NULL, 5, 1, 1, 4, NULL, NULL, '0.7697162365258823', '', '', '', '', '', '', 'awaiting_user', 'deleted', NULL, 1, 3, 4, 6, 4, '2015-08-13 10:27:01', '2015-05-24 10:27:01', '2015-09-21 10:27:01', '2015-04-16 10:27:01', '2014-11-18 10:27:01', '2015-07-03 10:27:01', '2015-09-24 10:27:01', '2015-01-01 10:27:01', '2014-10-17 10:27:01', '2014-07-22 10:27:01', '2015-05-13 10:27:01', 4, 1, NULL, 3, 'Test ticket #41', 'Test ticket #41', NULL, NULL, NULL),
(42, NULL, 1, 3, NULL, NULL, NULL, NULL, 2, 1, NULL, 5, 2, 1, 4, NULL, NULL, '0.6769206977234414', '', '', '', '', '', '', 'awaiting_agent', 'validating', NULL, 1, 4, 10, 9, 2, '2014-07-27 10:27:01', '2014-12-08 10:27:01', '2015-03-14 10:27:01', '2015-06-03 10:27:01', NULL, '2015-04-11 10:27:01', '2014-10-07 10:27:01', '2014-12-22 10:27:01', '2014-10-20 10:27:01', '2014-09-10 10:27:01', '2014-08-26 10:27:01', 3, 1, NULL, 3, 'Test ticket #42', 'Test ticket #42', NULL, NULL, NULL),
(43, NULL, 1, 3, NULL, NULL, NULL, NULL, 4, 4, NULL, NULL, 2, 1, 3, NULL, NULL, '0.5881117429473547', '', '', '', '', '', '', 'archived', 'validating', NULL, 0, 4, 6, 1, 2, '2014-07-03 10:27:01', '2015-03-02 10:27:01', '2015-03-05 10:27:01', '2014-08-09 10:27:01', NULL, '2015-06-30 10:27:01', '2015-07-22 10:27:01', '2015-07-13 10:27:01', '2015-03-19 10:27:01', '2015-01-26 10:27:01', '2015-04-26 10:27:01', 1, 3, NULL, 3, 'Test ticket #43', 'Test ticket #43', NULL, NULL, NULL),
(44, NULL, 1, 3, NULL, NULL, NULL, NULL, 3, 3, NULL, 4, 2, 1, 3, NULL, NULL, '0.8737814034091136', '', '', '', '', '', '', 'resolved', 'validating', NULL, 0, 2, 6, 5, 3, '2015-06-19 10:27:01', '2014-07-01 10:27:01', '2014-06-09 10:27:01', '2015-08-30 10:27:01', NULL, '2015-07-31 10:27:01', '2015-05-27 10:27:01', '2014-06-30 10:27:01', '2014-08-13 10:27:01', '2015-03-09 10:27:01', '2014-12-10 10:27:01', 3, 4, NULL, 1, 'Test ticket #44', 'Test ticket #44', NULL, NULL, NULL),
(45, NULL, 1, 2, NULL, NULL, NULL, NULL, 2, 4, NULL, 3, NULL, 1, 4, NULL, NULL, '0.4262621294951645', '', '', '', '', '', '', 'hidden', 'validating', NULL, 0, 1, 1, 10, 4, '2015-09-07 10:27:01', '2014-07-14 10:27:01', '2015-04-29 10:27:01', '2014-07-05 10:27:01', '2014-11-30 10:27:01', '2015-04-09 10:27:01', '2014-06-19 10:27:01', '2014-11-09 10:27:01', '2015-02-11 10:27:01', '2015-03-27 10:27:01', '2015-01-23 10:27:01', 2, 3, NULL, 2, 'Test ticket #45', 'Test ticket #45', NULL, NULL, NULL),
(46, NULL, 1, 2, NULL, NULL, NULL, NULL, 4, 2, NULL, 3, 2, 1, 1, NULL, NULL, '0.5560071929879553', '', '', '', '', '', '', 'archived', 'validating', NULL, 0, 1, 5, 1, 3, '2015-04-15 10:27:01', '2014-10-26 10:27:01', '2015-03-12 10:27:01', '2015-09-27 10:27:01', NULL, '2015-07-16 10:27:01', '2015-05-11 10:27:01', '2015-10-07 10:27:01', '2015-08-15 10:27:01', '2015-01-13 10:27:01', '2015-04-14 10:27:01', 1, 4, NULL, 1, 'Test ticket #46', 'Test ticket #46', NULL, NULL, NULL),
(47, NULL, 1, 1, NULL, NULL, NULL, NULL, 2, 4, NULL, 5, NULL, 1, 4, NULL, NULL, '0.30855933791823625', '', '', '', '', '', '', 'hidden', 'spam', NULL, 1, 1, 5, 4, 3, '2015-03-07 10:27:01', '2014-05-31 10:27:01', '2014-11-11 10:27:01', '2015-04-19 10:27:01', NULL, '2014-12-03 10:27:01', '2014-07-12 10:27:01', '2014-11-06 10:27:01', '2014-11-20 10:27:01', '2015-06-26 10:27:01', '2015-08-14 10:27:01', 4, 2, NULL, 2, 'Test ticket #47', 'Test ticket #47', NULL, NULL, NULL),
(48, NULL, 1, 2, NULL, NULL, NULL, NULL, 1, 1, NULL, 4, 2, 1, 3, NULL, NULL, '0.3717731427138421', '', '', '', '', '', '', 'hidden', 'spam', NULL, 0, 4, 10, 4, 4, '2014-10-31 10:27:35', '2015-01-11 10:27:35', '2014-11-18 10:27:35', '2014-12-01 10:27:35', '2015-07-19 10:27:35', '2014-06-19 10:27:35', '2015-05-26 10:27:35', '2015-02-02 10:27:35', '2014-11-06 10:27:35', '2014-07-31 10:27:35', '2015-04-25 10:27:35', 1, 2, NULL, 1, 'Test ticket #48', 'Test ticket #48', NULL, NULL, NULL),
(49, NULL, 1, 1, NULL, NULL, NULL, NULL, 2, 1, NULL, 4, 1, 1, 3, NULL, 4, '0.43426297179820295', '', '', '', '', '', '', 'hidden', 'spam', NULL, 0, 2, 9, 4, 2, '2014-12-16 10:27:35', '2014-07-10 10:27:35', '2014-09-17 10:27:35', '2015-08-01 10:27:35', NULL, '2015-03-21 10:27:35', '2014-07-03 10:27:35', '2015-03-15 10:27:35', '2015-05-09 10:27:35', '2015-05-23 10:27:35', '2015-02-15 10:27:35', 3, 1, NULL, 2, 'Test ticket #49', 'Test ticket #49', NULL, NULL, NULL),
(50, NULL, 1, 2, NULL, NULL, NULL, NULL, 4, 4, NULL, 3, 2, 1, 1, NULL, NULL, '0.1424235935718041', '', '', '', '', '', '', 'archived', 'temp', NULL, 1, 4, 1, 6, 3, '2015-06-10 10:27:35', '2015-02-23 10:27:35', '2015-01-07 10:27:35', '2015-04-01 10:27:35', NULL, '2015-07-27 10:27:35', '2014-06-14 10:27:35', '2015-04-06 10:27:35', '2014-06-11 10:27:35', '2014-10-10 10:27:35', '2014-10-06 10:27:35', 2, 1, NULL, 3, 'Test ticket #50', 'Test ticket #50', NULL, NULL, NULL),
(51, NULL, 1, 3, NULL, NULL, NULL, NULL, 1, 2, NULL, 3, 1, 1, 3, NULL, NULL, '0.8945972536640217', '', '', '', '', '', '', 'hidden', 'spam', NULL, 1, 2, 2, 7, 3, '2015-04-18 10:27:35', '2014-08-25 10:27:35', '2015-09-13 10:27:35', '2014-09-05 10:27:35', NULL, '2014-09-20 10:27:35', '2015-04-21 10:27:35', '2015-03-22 10:27:35', '2014-06-03 10:27:35', '2014-10-14 10:27:35', '2014-11-24 10:27:35', 1, 2, NULL, 1, 'Test ticket #51', 'Test ticket #51', NULL, NULL, NULL),
(52, NULL, 1, 1, NULL, NULL, NULL, NULL, 3, 2, NULL, 5, 1, 1, 3, NULL, 2, '0.13998764486982268', '', '', '', '', '', '', 'resolved', 'validating', NULL, 1, 3, 10, 8, 1, '2015-05-01 10:27:35', '2015-07-09 10:27:35', '2014-06-21 10:27:35', '2015-07-01 10:27:35', '2015-07-26 10:27:35', '2015-07-27 10:27:35', '2015-05-16 10:27:35', '2015-10-04 10:27:35', '2015-07-15 10:27:35', '2014-08-18 10:27:35', '2014-11-17 10:27:35', 4, 4, NULL, 3, 'Test ticket #52', 'Test ticket #52', NULL, NULL, NULL),
(53, NULL, 1, 2, NULL, NULL, NULL, NULL, 2, 4, NULL, 2, NULL, 1, 2, NULL, 4, '0.4257998479770495', '', '', '', '', '', '', 'resolved', 'validating', NULL, 0, 4, 7, 6, 4, '2014-11-10 10:27:35', '2014-10-15 10:27:35', '2014-12-19 10:27:35', '2014-09-09 10:27:35', NULL, '2015-01-03 10:27:35', '2015-06-18 10:27:35', '2015-02-21 10:27:35', '2014-12-04 10:27:35', '2014-10-17 10:27:35', '2014-10-18 10:27:35', 2, 4, NULL, 2, 'Test ticket #53', 'Test ticket #53', NULL, NULL, NULL),
(54, NULL, 1, 2, NULL, NULL, NULL, NULL, 2, 2, NULL, 3, 2, 1, 1, NULL, 1, '0.4750070259673586', '', '', '', '', '', '', 'awaiting_agent', 'temp', NULL, 1, 3, 4, 1, 1, '2015-01-27 10:27:35', '2015-07-03 10:27:35', '2015-02-28 10:27:35', '2014-11-23 10:27:35', NULL, '2015-03-18 10:27:35', '2015-02-26 10:27:35', '2015-10-01 10:27:35', '2014-10-09 10:27:35', '2014-12-10 10:27:35', '2014-08-18 10:27:35', 2, 2, NULL, 1, 'Test ticket #54', 'Test ticket #54', NULL, NULL, NULL),
(55, NULL, 1, 2, NULL, NULL, NULL, NULL, 2, 4, NULL, 4, 2, 1, 4, NULL, NULL, '0.740610443745377', '', '', '', '', '', '', 'resolved', 'deleted', NULL, 1, 2, 9, 6, 4, '2014-11-11 10:27:35', '2014-08-30 10:27:35', '2015-09-11 10:27:35', '2014-08-10 10:27:35', NULL, '2014-08-06 10:27:35', '2015-09-19 10:27:35', '2014-11-27 10:27:35', '2015-09-23 10:27:35', '2015-05-28 10:27:35', '2015-06-12 10:27:35', 2, 1, NULL, 3, 'Test ticket #55', 'Test ticket #55', NULL, NULL, NULL),
(56, NULL, 1, 1, NULL, NULL, NULL, NULL, 1, 2, NULL, 1, 1, 1, 1, NULL, 4, '0.9354460480953064', '', '', '', '', '', '', 'awaiting_user', 'validating', NULL, 1, 4, 9, 6, 1, '2014-09-28 10:27:35', '2014-12-16 10:27:35', '2014-10-16 10:27:35', '2014-09-06 10:27:35', NULL, '2014-09-18 10:27:35', '2015-04-13 10:27:35', '2015-02-13 10:27:35', '2015-05-11 10:27:35', '2015-08-28 10:27:35', '2015-01-27 10:27:35', 2, 4, NULL, 3, 'Test ticket #56', 'Test ticket #56', NULL, NULL, NULL),
(57, NULL, 1, 2, NULL, NULL, NULL, NULL, NULL, 2, NULL, NULL, 2, 1, 2, NULL, NULL, '0.4012943146762385', '', '', '', '', '', '', 'hidden', 'spam', NULL, 1, 3, 4, 1, 1, '2014-11-29 10:27:35', '2014-10-21 10:27:35', '2014-11-22 10:27:35', '2015-08-25 10:27:35', NULL, '2015-04-15 10:27:35', '2015-07-04 10:27:35', '2014-07-13 10:27:35', '2014-06-24 10:27:35', '2015-10-08 10:27:35', '2015-07-13 10:27:35', 4, 4, NULL, 2, 'Test ticket #57', 'Test ticket #57', NULL, NULL, NULL),
(58, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, NULL, 1, 2, NULL, NULL, '0.13400620141439717', '', '', '', '', '', '', 'awaiting_agent', 'temp', NULL, 1, 4, 3, 3, 4, '2014-07-27 10:27:35', '2015-08-05 10:27:35', '2015-10-05 10:27:35', '2014-11-17 10:27:35', '2015-06-12 10:27:35', '2015-06-16 10:27:35', '2015-03-03 10:27:35', '2015-01-31 10:27:35', '2015-07-04 10:27:35', '2015-02-18 10:27:35', '2014-10-03 10:27:35', 2, 2, NULL, 3, 'Test ticket #58', 'Test ticket #58', NULL, NULL, NULL),
(59, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, 4, NULL, 3, 2, 1, 1, NULL, 3, '0.3541848001574956', '', '', '', '', '', '', 'awaiting_user', 'validating', NULL, 1, 2, 8, 6, 2, '2014-08-22 10:27:35', '2014-11-24 10:27:35', '2014-10-20 10:27:35', '2014-11-28 10:27:35', '2015-09-29 10:27:35', '2015-06-24 10:27:35', '2015-10-07 10:27:35', '2015-04-04 10:27:35', '2014-08-01 10:27:35', '2015-06-24 10:27:35', '2015-02-17 10:27:35', 3, 1, NULL, 1, 'Test ticket #59', 'Test ticket #59', NULL, NULL, NULL),
(60, NULL, 1, 3, NULL, NULL, NULL, NULL, 3, 3, NULL, 3, NULL, 1, 1, NULL, NULL, '0.5002871393228762', '', '', '', '', '', '', 'awaiting_user', 'deleted', NULL, 1, 3, 8, 1, 1, '2014-07-21 10:27:35', '2014-09-18 10:27:35', '2015-07-05 10:27:35', '2014-11-22 10:27:35', NULL, '2015-06-20 10:27:35', '2015-06-23 10:27:35', '2015-03-14 10:27:35', '2015-03-02 10:27:35', '2014-06-20 10:27:35', '2015-03-08 10:27:35', 2, 1, NULL, 1, 'Test ticket #60', 'Test ticket #60', NULL, NULL, NULL),
(61, NULL, 1, 3, NULL, NULL, NULL, NULL, 3, 1, NULL, 1, NULL, 1, 1, NULL, NULL, '0.42835759411413', '', '', '', '', '', '', 'hidden', 'deleted', NULL, 1, 3, 2, 10, 2, '2015-05-23 10:27:35', '2015-09-26 10:27:35', '2015-05-14 10:27:35', '2015-03-21 10:27:35', NULL, '2015-03-05 10:27:35', '2014-09-09 10:27:35', '2014-11-21 10:27:35', '2014-08-12 10:27:35', '2015-05-13 10:27:35', '2014-06-20 10:27:35', 4, 2, NULL, 3, 'Test ticket #61', 'Test ticket #61', NULL, NULL, NULL),
(62, NULL, 1, 2, NULL, NULL, NULL, NULL, 1, 1, NULL, 5, 2, 1, 2, NULL, NULL, '0.1901891177428785', '', '', '', '', '', '', 'archived', 'temp', NULL, 1, 1, 9, 5, 4, '2015-03-10 10:27:35', '2015-06-26 10:27:35', '2014-09-17 10:27:35', '2015-06-13 10:27:35', NULL, '2014-12-19 10:27:35', '2015-04-14 10:27:35', '2015-09-29 10:27:35', '2015-09-26 10:27:35', '2015-09-02 10:27:35', '2015-05-16 10:27:35', 1, 2, NULL, 3, 'Test ticket #62', 'Test ticket #62', NULL, NULL, NULL),
(63, NULL, 1, 2, NULL, NULL, NULL, NULL, 2, 1, NULL, NULL, 2, 1, 4, NULL, NULL, '0.06757662637864857', '', '', '', '', '', '', 'awaiting_user', 'deleted', NULL, 0, 2, 10, 7, 3, '2014-08-30 10:27:35', '2015-04-29 10:27:35', '2015-07-03 10:27:35', '2015-10-08 10:27:35', NULL, '2015-08-29 10:27:35', '2015-07-21 10:27:35', '2015-01-09 10:27:35', '2015-06-03 10:27:35', '2014-11-22 10:27:35', '2015-03-03 10:27:35', 2, 1, NULL, 1, 'Test ticket #63', 'Test ticket #63', NULL, NULL, NULL),
(64, NULL, 1, 2, NULL, NULL, NULL, NULL, 2, 1, NULL, NULL, 2, 1, 4, NULL, NULL, '0.5904969029040047', '', '', '', '', '', '', 'hidden', 'deleted', NULL, 0, 1, 1, 8, 4, '2015-09-22 10:27:35', '2015-01-11 10:27:35', '2014-12-12 10:27:35', '2015-03-31 10:27:35', NULL, '2015-03-13 10:27:35', '2014-09-27 10:27:35', '2015-01-28 10:27:35', '2015-05-21 10:27:35', '2014-07-24 10:27:35', '2014-12-29 10:27:35', 1, 1, NULL, 2, 'Test ticket #64', 'Test ticket #64', NULL, NULL, NULL),
(65, 99, 1, 2, NULL, NULL, NULL, NULL, 1, 2, NULL, 2, NULL, 1, 4, NULL, NULL, '0.8073989570209746', '', '', '', '', '', '', 'hidden', 'spam', NULL, 0, 3, 7, 2, 4, '2015-04-30 10:27:35', '2014-10-04 10:27:35', '2014-10-08 10:27:35', '2015-03-06 10:27:35', NULL, '2015-01-05 10:27:35', '2014-08-06 10:27:35', '2014-12-01 10:27:35', '2015-01-09 10:27:35', '2014-08-07 10:27:35', '2014-11-22 10:27:35', 3, 2, NULL, 2, 'Test ticket #65', 'Test ticket #65', NULL, NULL, NULL),
(66, NULL, 1, 2, NULL, NULL, NULL, NULL, NULL, 4, NULL, 3, 2, 1, 3, NULL, NULL, '0.3628699438300635', '', '', '', '', '', '', 'awaiting_agent', 'deleted', NULL, 0, 2, 9, 9, 3, '2015-02-13 10:27:35', '2014-11-25 10:27:35', '2014-10-02 10:27:35', '2014-08-29 10:27:35', NULL, '2014-10-23 10:27:35', '2015-09-26 10:27:35', '2015-09-26 10:27:35', '2015-09-16 10:27:35', '2015-07-22 10:27:35', '2014-11-21 10:27:35', 3, 4, NULL, 3, 'Test ticket #66', 'Test ticket #66', NULL, NULL, NULL),
(67, 58, 1, 2, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, 1, 1, 1, NULL, 1, '0.8760656955429034', '', '', '', '', '', '', 'awaiting_user', 'temp', NULL, 1, 2, 5, 9, 4, '2014-06-12 10:27:35', '2015-08-12 10:27:35', '2014-11-06 10:27:35', '2015-09-28 10:27:35', NULL, '2015-04-06 10:27:35', '2014-12-15 10:27:35', '2014-08-02 10:27:35', '2015-01-14 10:27:35', '2015-08-30 10:27:35', '2014-09-09 10:27:35', 3, 1, NULL, 3, 'Test ticket #67', 'Test ticket #67', NULL, NULL, NULL),
(68, NULL, 1, 1, NULL, NULL, NULL, NULL, 2, 4, NULL, 5, 1, 1, 3, NULL, NULL, '0.2685719907922409', '', '', '', '', '', '', 'hidden', 'temp', NULL, 1, 1, 2, 5, 3, '2015-09-11 10:27:35', '2015-06-19 10:27:35', '2014-06-20 10:27:35', '2015-08-25 10:27:35', NULL, '2014-10-28 10:27:35', '2014-11-04 10:27:35', '2015-05-06 10:27:35', '2015-01-20 10:27:35', '2014-10-31 10:27:35', '2014-08-08 10:27:35', 1, 3, NULL, 3, 'Test ticket #68', 'Test ticket #68', NULL, NULL, NULL),
(69, NULL, 1, 3, NULL, NULL, NULL, NULL, 4, 2, NULL, 5, NULL, 1, 2, NULL, 4, '0.8518243179207894', '', '', '', '', '', '', 'hidden', 'spam', NULL, 1, 3, 6, 3, 2, '2014-09-28 10:27:35', '2014-07-04 10:27:35', '2015-04-11 10:27:35', '2015-09-19 10:27:35', NULL, '2015-03-05 10:27:35', '2014-08-14 10:27:35', '2014-07-15 10:27:35', '2014-06-05 10:27:35', '2015-06-29 10:27:35', '2015-09-02 10:27:35', 4, 3, NULL, 2, 'Test ticket #69', 'Test ticket #69', NULL, NULL, NULL),
(70, NULL, 1, 1, NULL, NULL, NULL, NULL, 1, 2, NULL, 3, NULL, 1, 3, NULL, 1, '0.16292262744430697', '', '', '', '', '', '', 'archived', 'validating', NULL, 1, 2, 6, 4, 2, '2015-07-28 10:27:35', '2014-06-21 10:27:35', '2015-05-05 10:27:35', '2014-10-18 10:27:35', NULL, '2014-06-19 10:27:35', '2014-07-06 10:27:35', '2014-10-09 10:27:35', '2014-07-20 10:27:35', '2015-05-29 10:27:35', '2014-11-16 10:27:35', 2, 2, NULL, 2, 'Test ticket #70', 'Test ticket #70', NULL, NULL, NULL),
(71, NULL, 1, 3, NULL, NULL, NULL, NULL, 2, 3, NULL, 2, 1, 1, 4, NULL, NULL, '0.005990987649179052', '', '', '', '', '', '', 'awaiting_agent', 'validating', NULL, 1, 4, 6, 2, 1, '2014-09-08 10:29:08', '2014-08-04 10:29:08', '2014-07-02 10:29:08', '2015-09-13 10:29:08', NULL, '2015-06-24 10:29:08', '2014-11-20 10:29:08', '2014-12-19 10:29:08', '2014-05-27 10:29:08', '2015-06-13 10:29:08', '2015-07-12 10:29:08', 1, 2, NULL, 1, 'Test ticket #71', 'Test ticket #71', NULL, NULL, NULL),
(72, NULL, 1, 1, NULL, NULL, NULL, NULL, 1, 3, NULL, 5, 1, 1, 2, NULL, NULL, '0.7432434938319432', '', '', '', '', '', '', 'hidden', 'deleted', NULL, 0, 2, 7, 4, 3, '2014-10-23 10:29:08', '2015-05-05 10:29:08', '2015-02-20 10:29:08', '2015-04-08 10:29:08', NULL, '2015-07-18 10:29:08', '2015-02-20 10:29:08', '2014-08-28 10:29:08', '2014-11-03 10:29:08', '2014-06-16 10:29:08', '2014-09-24 10:29:08', 4, 2, NULL, 3, 'Test ticket #72', 'Test ticket #72', NULL, NULL, NULL),
(73, NULL, 1, 3, NULL, NULL, NULL, NULL, 1, 2, NULL, 2, 1, 1, 2, NULL, NULL, '0.5695560179367252', '', '', '', '', '', '', 'resolved', 'validating', NULL, 0, 3, 3, 5, 2, '2015-08-16 10:29:08', '2014-10-24 10:29:08', '2015-07-15 10:29:08', '2014-09-25 10:29:08', NULL, '2015-08-27 10:29:08', '2014-12-07 10:29:08', '2014-09-04 10:29:08', '2015-07-18 10:29:08', '2015-03-10 10:29:08', '2014-11-26 10:29:08', 4, 3, NULL, 1, 'Test ticket #73', 'Test ticket #73', NULL, NULL, NULL),
(74, NULL, 1, 3, NULL, NULL, NULL, NULL, 3, 4, NULL, 4, NULL, 1, 4, NULL, NULL, '0.5806537834747264', '', '', '', '', '', '', 'hidden', 'deleted', NULL, 1, 3, 6, 6, 1, '2015-09-29 10:29:08', '2014-10-16 10:29:08', '2015-01-22 10:29:08', '2015-02-26 10:29:08', '2014-10-29 10:29:08', '2015-08-21 10:29:08', '2015-03-15 10:29:08', '2014-09-09 10:29:08', '2014-10-25 10:29:08', '2015-08-12 10:29:08', '2015-02-07 10:29:08', 1, 4, NULL, 2, 'Test ticket #74', 'Test ticket #74', NULL, NULL, NULL),
(75, NULL, 1, 1, NULL, NULL, NULL, NULL, 4, 1, NULL, 2, 1, 1, 4, NULL, NULL, '0.016006498612469527', '', '', '', '', '', '', 'hidden', 'spam', NULL, 1, 2, 6, 5, 2, '2015-08-29 10:29:08', '2015-10-05 10:29:08', '2014-09-08 10:29:08', '2014-06-26 10:29:08', '2015-05-03 10:29:08', '2014-09-20 10:29:08', '2014-07-22 10:29:08', '2015-08-04 10:29:08', '2014-05-29 10:29:08', '2014-12-22 10:29:08', '2014-07-07 10:29:08', 4, 2, NULL, 3, 'Test ticket #75', 'Test ticket #75', NULL, NULL, NULL),
(76, 78, 1, 3, NULL, NULL, NULL, NULL, 5, 4, NULL, NULL, 1, 1, 1, NULL, 4, '0.6333784271342479', '', '', '', '', '', '', 'awaiting_agent', 'validating', '1', 1, 1, 3, 4, 3, '2015-01-11 10:29:08', '2014-11-28 10:29:08', '2015-01-23 10:29:08', '2014-10-24 10:29:08', NULL, '2014-11-30 10:29:08', '2015-05-17 10:29:08', '2014-12-26 10:29:08', '2014-05-27 10:29:08', '2015-05-25 10:29:08', '2015-04-07 10:29:08', 1, 1, NULL, 1, 'Test ticket #76', 'Test ticket #76', NULL, NULL, NULL),
(77, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 5, 1, 1, 1, NULL, NULL, '0.6474071681940995', '', '', '', '', '', '', 'hidden', 'spam', NULL, 1, 1, 10, 5, 1, '2014-09-05 10:29:08', '2015-04-24 10:29:08', '2015-05-16 10:29:08', '2015-03-01 10:29:08', NULL, '2015-03-31 10:29:08', '2014-07-20 10:29:08', '2015-05-10 10:29:08', '2014-08-14 10:29:08', '2015-05-11 10:29:08', '2014-06-08 10:29:08', 4, 4, NULL, 3, 'Test ticket #77', 'Test ticket #77', NULL, NULL, NULL),
(78, 81, 1, 2, NULL, NULL, NULL, NULL, 5, 3, NULL, 2, 1, 1, 4, NULL, 4, '0.20890957136536908', '', '', '', '', '', '', 'archived', 'temp', '0', 1, 2, 1, 1, 1, '2014-06-24 10:29:08', '2014-10-18 10:29:08', '2014-10-12 10:29:08', '2015-02-06 10:29:08', NULL, '2014-07-13 10:29:08', '2014-10-23 10:29:08', '2014-09-10 10:29:08', '2014-08-18 10:29:08', '2014-09-03 10:29:08', '2015-01-25 10:29:08', 1, 1, NULL, 3, 'Test ticket #78', 'Test ticket #78', NULL, NULL, NULL),
(79, NULL, 1, 1, NULL, NULL, NULL, NULL, 4, 1, NULL, 3, NULL, 1, 1, NULL, NULL, '0.5912753311835931', '', '', '', '', '', '', 'awaiting_user', 'validating', NULL, 1, 3, 5, 6, 2, '2014-07-29 10:29:08', '2015-02-17 10:29:08', '2014-10-15 10:29:08', '2015-07-09 10:29:08', NULL, '2015-05-29 10:29:08', '2015-09-19 10:29:08', '2015-03-23 10:29:08', '2014-07-25 10:29:08', '2015-06-29 10:29:08', '2015-04-06 10:29:08', 1, 1, NULL, 3, 'Test ticket #79', 'Test ticket #79', NULL, NULL, NULL),
(80, NULL, 1, 1, NULL, NULL, NULL, NULL, 3, 2, NULL, 4, 2, 1, 3, NULL, 2, '0.5031709759525684', '', '', '', '', '', '', 'hidden', 'spam', NULL, 1, 4, 2, 2, 2, '2015-05-19 10:29:08', '2015-04-16 10:29:08', '2014-07-17 10:29:08', '2015-03-05 10:29:08', NULL, '2015-08-06 10:29:08', '2015-07-13 10:29:08', '2015-02-03 10:29:08', '2014-06-22 10:29:08', '2015-06-04 10:29:08', '2015-03-10 10:29:08', 2, 3, NULL, 2, 'Test ticket #80', 'Test ticket #80', NULL, NULL, NULL),
(81, NULL, 1, 1, NULL, NULL, NULL, NULL, 3, 4, NULL, 4, NULL, 1, 1, NULL, NULL, '0.7606918753690011', '', '', '', '', '', '', 'archived', 'deleted', NULL, 1, 2, 8, 7, 4, '2014-08-09 10:29:08', '2015-04-06 10:29:08', '2015-05-12 10:29:08', '2015-04-01 10:29:08', NULL, '2014-07-04 10:29:08', '2014-12-26 10:29:08', '2015-08-20 10:29:08', '2014-09-15 10:29:08', '2014-12-18 10:29:08', '2014-12-08 10:29:08', 2, 3, NULL, 1, 'Test ticket #81', 'Test ticket #81', NULL, NULL, NULL),
(82, NULL, 1, 2, NULL, NULL, NULL, NULL, NULL, 3, NULL, NULL, 2, 1, 2, NULL, NULL, '0.7393224646703549', '', '', '', '', '', '', 'resolved', 'temp', NULL, 0, 4, 10, 1, 2, '2014-10-12 10:29:08', '2015-01-01 10:29:08', '2014-11-24 10:29:08', '2015-02-02 10:29:08', NULL, '2015-03-21 10:29:08', '2015-05-21 10:29:08', '2015-07-02 10:29:08', '2015-07-31 10:29:08', '2015-08-18 10:29:08', '2015-08-20 10:29:08', 1, 3, NULL, 2, 'Test ticket #82', 'Test ticket #82', NULL, NULL, NULL),
(83, 79, 1, 1, NULL, NULL, NULL, NULL, 2, 1, NULL, 3, 2, 1, 4, NULL, NULL, '0.5385482902997623', '', '', '', '', '', '', 'awaiting_agent', 'deleted', NULL, 1, 4, 7, 8, 4, '2014-05-27 10:29:08', '2015-03-01 10:29:08', '2015-06-23 10:29:08', '2014-09-29 10:29:08', NULL, '2015-05-29 10:29:08', '2015-09-30 10:29:08', '2015-05-15 10:29:08', '2015-03-15 10:29:08', '2015-07-04 10:29:08', '2014-10-14 10:29:08', 1, 4, NULL, 2, 'Test ticket #83', 'Test ticket #83', NULL, NULL, NULL),
(84, NULL, 1, 1, NULL, NULL, NULL, NULL, 1, 3, NULL, 1, 2, 1, 3, NULL, NULL, '0.14333091875848447', '', '', '', '', '', '', 'archived', 'validating', NULL, 1, 2, 5, 10, 3, '2015-09-22 10:29:08', '2015-03-11 10:29:08', '2015-09-30 10:29:08', '2014-08-26 10:29:08', NULL, '2014-10-11 10:29:08', '2015-01-23 10:29:08', '2015-03-18 10:29:08', '2015-02-03 10:29:08', '2015-06-09 10:29:08', '2014-10-05 10:29:08', 4, 3, NULL, 3, 'Test ticket #84', 'Test ticket #84', NULL, NULL, NULL),
(85, NULL, 1, 3, NULL, NULL, NULL, NULL, 1, 3, NULL, 1, 2, 1, 3, NULL, NULL, '0.4114594435426029', '', '', '', '', '', '', 'archived', 'temp', NULL, 1, 3, 5, 2, 2, '2015-09-10 10:29:08', '2014-09-06 10:29:08', '2014-08-30 10:29:08', '2014-11-13 10:29:08', '2014-07-30 10:29:08', '2015-04-04 10:29:08', '2015-05-29 10:29:08', '2015-06-29 10:29:08', '2015-06-24 10:29:08', '2015-02-19 10:29:08', '2014-11-08 10:29:08', 4, 4, NULL, 1, 'Test ticket #85', 'Test ticket #85', NULL, NULL, NULL),
(86, NULL, 1, 1, NULL, NULL, NULL, NULL, 2, 2, NULL, 3, 2, 1, 2, NULL, NULL, '0.26548461827028974', '', '', '', '', '', '', 'resolved', 'temp', NULL, 0, 2, 2, 6, 2, '2015-01-07 10:29:08', '2015-04-10 10:29:08', '2015-07-15 10:29:08', '2014-09-21 10:29:08', NULL, '2015-05-31 10:29:08', '2015-04-04 10:29:08', '2015-08-26 10:29:08', '2015-05-05 10:29:08', '2015-05-10 10:29:08', '2014-12-24 10:29:08', 4, 1, NULL, 3, 'Test ticket #86', 'Test ticket #86', NULL, NULL, NULL),
(87, NULL, 1, 3, NULL, NULL, NULL, NULL, 4, 3, NULL, 2, 1, 1, 4, NULL, NULL, '0.28244184542674744', '', '', '', '', '', '', 'archived', 'deleted', NULL, 1, 4, 4, 9, 2, '2015-06-06 10:29:08', '2015-08-26 10:29:08', '2014-10-30 10:29:08', '2015-07-11 10:29:08', NULL, '2014-12-05 10:29:08', '2014-12-22 10:29:08', '2015-09-06 10:29:08', '2014-12-21 10:29:08', '2014-10-14 10:29:08', '2014-08-13 10:29:08', 1, 4, NULL, 3, 'Test ticket #87', 'Test ticket #87', NULL, NULL, NULL),
(88, NULL, 1, 1, NULL, NULL, NULL, NULL, 2, 3, NULL, 3, 2, 1, 1, NULL, NULL, '0.6465564301671054', '', '', '', '', '', '', 'hidden', 'spam', NULL, 0, 2, 10, 6, 1, '2015-07-22 10:29:08', '2015-06-18 10:29:08', '2014-11-16 10:29:08', '2014-12-18 10:29:08', '2014-06-02 10:29:08', '2015-07-16 10:29:08', '2014-07-24 10:29:08', '2014-07-11 10:29:08', '2014-07-16 10:29:08', '2014-09-23 10:29:08', '2015-08-13 10:29:08', 2, 1, NULL, 2, 'Test ticket #88', 'Test ticket #88', NULL, NULL, NULL),
(89, NULL, 1, 1, NULL, NULL, NULL, NULL, 1, 1, NULL, 1, NULL, 1, 4, NULL, NULL, '0.3146346316809157', '', '', '', '', '', '', 'hidden', 'temp', NULL, 1, 2, 8, 6, 1, '2014-11-18 10:29:08', '2015-01-27 10:29:08', '2014-12-16 10:29:08', '2015-03-01 10:29:08', NULL, '2014-08-06 10:29:08', '2014-06-09 10:29:08', '2015-05-17 10:29:08', '2015-01-15 10:29:08', '2014-09-07 10:29:08', '2015-04-09 10:29:08', 2, 1, NULL, 2, 'Test ticket #89', 'Test ticket #89', NULL, NULL, NULL),
(90, NULL, 1, 2, NULL, NULL, NULL, NULL, 4, 2, NULL, 1, 1, 1, 2, NULL, 4, '0.4138187732676219', '', '', '', '', '', '', 'awaiting_user', 'validating', NULL, 1, 4, 3, 5, 3, '2015-04-15 10:29:08', '2015-07-16 10:29:08', '2014-09-11 10:29:08', '2015-03-13 10:29:08', '2014-10-04 10:29:08', '2015-03-05 10:29:08', '2014-06-14 10:29:08', '2015-01-28 10:29:08', '2014-11-19 10:29:08', '2014-10-18 10:29:08', '2014-12-04 10:29:08', 4, 4, NULL, 2, 'Test ticket #90', 'Test ticket #90', NULL, NULL, NULL),
(91, 91, 1, 2, NULL, NULL, NULL, NULL, NULL, 4, NULL, 2, 1, 1, 1, NULL, NULL, '0.0821626056750888', '', '', '', '', '', '', 'hidden', 'spam', NULL, 1, 2, 8, 5, 4, '2015-08-17 10:29:08', '2014-08-10 10:29:08', '2014-06-29 10:29:08', '2015-08-11 10:29:08', NULL, '2014-12-29 10:29:08', '2015-02-07 10:29:08', '2014-10-06 10:29:08', '2015-06-22 10:29:08', '2014-07-29 10:29:08', '2014-10-15 10:29:08', 4, 3, NULL, 2, 'Test ticket #91', 'Test ticket #91', NULL, NULL, NULL),
(92, NULL, 1, 1, NULL, NULL, NULL, NULL, 5, 3, NULL, NULL, NULL, 1, 1, NULL, 4, '0.5307471691917136', '', '', '', '', '', '', 'awaiting_user', 'deleted', '1', 0, 2, 2, 7, 4, '2014-10-25 10:29:08', '2014-11-25 10:29:08', '2015-08-27 10:29:08', '2015-01-20 10:29:08', NULL, '2015-06-23 10:29:08', '2015-10-07 10:29:08', '2015-04-07 10:29:08', '2014-08-17 10:29:08', '2015-09-04 10:29:08', '2014-08-13 10:29:08', 1, 3, NULL, 2, 'Test ticket #92', 'Test ticket #92', NULL, NULL, NULL),
(93, NULL, 1, 3, NULL, NULL, NULL, NULL, 2, 4, NULL, 4, 2, 1, 3, NULL, 4, '0.9492199215565063', '', '', '', '', '', '', 'awaiting_agent', 'validating', NULL, 1, 4, 1, 5, 2, '2014-07-19 10:29:08', '2014-11-26 10:29:08', '2015-02-09 10:29:08', '2015-01-24 10:29:08', NULL, '2015-08-19 10:29:08', '2015-08-13 10:29:08', '2015-05-28 10:29:08', '2014-05-31 10:29:08', '2015-07-22 10:29:08', '2014-08-27 10:29:08', 3, 3, NULL, 3, 'Test ticket #93', 'Test ticket #93', NULL, NULL, NULL),
(94, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 4, NULL, 1, 2, NULL, NULL, '0.6213062821154541', '', '', '', '', '', '', 'archived', 'deleted', NULL, 1, 3, 3, 8, 4, '2014-06-15 10:29:08', '2015-05-18 10:29:08', '2015-01-07 10:29:08', '2014-07-24 10:29:08', '2014-09-21 10:29:08', '2015-07-09 10:29:08', '2014-12-05 10:29:08', '2015-01-19 10:29:08', '2014-09-11 10:29:08', '2015-04-17 10:29:08', '2015-03-28 10:29:08', 4, 2, NULL, 3, 'Test ticket #94', 'Test ticket #94', NULL, NULL, NULL),
(95, NULL, 1, 2, NULL, NULL, NULL, NULL, 3, 4, NULL, 2, 1, 1, 4, NULL, NULL, '0.18798401596768202', '', '', '', '', '', '', 'resolved', 'deleted', NULL, 0, 4, 4, 5, 1, '2014-11-09 10:29:08', '2014-10-17 10:29:08', '2015-01-01 10:29:08', '2014-11-08 10:29:08', NULL, '2015-05-09 10:29:08', '2015-01-11 10:29:08', '2014-09-10 10:29:08', '2015-05-06 10:29:08', '2015-07-02 10:29:08', '2015-09-15 10:29:08', 3, 1, NULL, 2, 'Test ticket #95', 'Test ticket #95', NULL, NULL, NULL),
(96, 108, 1, 1, NULL, NULL, NULL, NULL, 4, 3, NULL, NULL, 2, 1, 4, NULL, 1, '0.011756496514898256', '', '', '', '', '', '', 'archived', 'deleted', NULL, 0, 2, 8, 6, 3, '2015-05-21 10:29:08', '2014-12-09 10:29:08', '2015-07-02 10:29:08', '2015-07-19 10:29:08', NULL, '2014-12-04 10:29:08', '2015-03-10 10:29:08', '2015-05-24 10:29:08', '2015-08-20 10:29:08', '2014-11-08 10:29:08', '2015-09-16 10:29:08', 1, 4, NULL, 1, 'Test ticket #96', 'Test ticket #96', NULL, NULL, NULL),
(97, NULL, 1, 3, NULL, NULL, NULL, NULL, 3, 3, NULL, 1, NULL, 1, 4, NULL, 3, '0.5069844988239784', '', '', '', '', '', '', 'hidden', 'deleted', NULL, 0, 1, 1, 1, 1, '2015-07-17 10:29:08', '2014-11-18 10:29:08', '2014-10-03 10:29:08', '2014-09-24 10:29:08', NULL, '2014-12-20 10:29:08', '2015-06-25 10:29:08', '2015-05-14 10:29:08', '2014-08-15 10:29:08', '2015-05-03 10:29:08', '2015-09-07 10:29:08', 2, 3, NULL, 1, 'Test ticket #97', 'Test ticket #97', NULL, NULL, NULL),
(98, NULL, 1, 3, NULL, NULL, NULL, NULL, 4, 2, NULL, 4, NULL, 1, 3, NULL, NULL, '0.1772404864218463', '', '', '', '', '', '', 'awaiting_user', 'temp', NULL, 1, 2, 7, 1, 2, '2015-08-10 10:29:08', '2015-06-29 10:29:08', '2014-11-14 10:29:08', '2014-11-05 10:29:08', NULL, '2014-05-30 10:29:08', '2014-09-25 10:29:08', '2014-08-30 10:29:08', '2014-09-18 10:29:08', '2015-03-10 10:29:08', '2014-08-29 10:29:08', 4, 2, NULL, 1, 'Test ticket #98', 'Test ticket #98', NULL, NULL, NULL),
(99, NULL, 1, 2, NULL, NULL, NULL, NULL, 5, 4, NULL, 1, 2, 1, 1, NULL, 2, '0.4274705969053047', '', '', '', '', '', '', 'awaiting_agent', 'deleted', NULL, 1, 3, 4, 6, 3, '2014-11-21 10:29:08', '2015-05-20 10:29:08', '2015-02-09 10:29:08', '2014-12-31 10:29:08', NULL, '2015-07-15 10:29:08', '2014-10-06 10:29:08', '2015-07-21 10:29:08', '2014-12-15 10:29:08', '2015-02-01 10:29:08', '2014-10-21 10:29:08', 1, 1, NULL, 1, 'Test ticket #99', 'Test ticket #99', NULL, NULL, NULL),
(100, 117, 1, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 2, NULL, 1, 1, NULL, NULL, '0.9052907860887152', '', '', '', '', '', '', 'archived', 'validating', NULL, 1, 2, 2, 1, 2, '2015-04-16 10:29:08', '2015-04-29 10:29:08', '2014-12-30 10:29:08', '2014-08-09 10:29:08', NULL, '2015-05-31 10:29:08', '2014-12-04 10:29:08', '2015-05-10 10:29:08', '2014-11-10 10:29:08', '2015-03-14 10:29:08', '2015-08-27 10:29:08', 1, 3, NULL, 2, 'Test ticket #100', 'Test ticket #100', NULL, NULL, NULL),
(101, NULL, 1, 1, NULL, NULL, NULL, NULL, 1, 1, NULL, 3, 1, 1, 3, NULL, 4, '0.17658438456857986', '', '', '', '', '', '', 'awaiting_agent', 'deleted', NULL, 1, 4, 7, 5, 1, '2014-08-25 10:29:08', '2015-09-02 10:29:08', '2014-07-11 10:29:08', '2015-04-28 10:29:08', NULL, '2015-01-09 10:29:08', '2015-10-06 10:29:08', '2015-03-24 10:29:08', '2014-06-12 10:29:08', '2014-11-22 10:29:08', '2015-05-10 10:29:08', 3, 1, NULL, 2, 'Test ticket #101', 'Test ticket #101', NULL, NULL, NULL),
(102, NULL, 1, 2, NULL, NULL, NULL, NULL, 3, 4, NULL, 5, NULL, 1, 3, NULL, NULL, '0.7662077963037489', '', '', '', '', '', '', 'resolved', 'validating', NULL, 1, 4, 8, 3, 4, '2015-04-29 10:29:08', '2015-05-29 10:29:08', '2015-04-18 10:29:08', '2014-06-27 10:29:08', NULL, '2015-03-16 10:29:08', '2015-08-03 10:29:08', '2015-03-08 10:29:08', '2014-10-04 10:29:08', '2015-03-17 10:29:08', '2014-08-15 10:29:08', 4, 2, NULL, 2, 'Test ticket #102', 'Test ticket #102', NULL, NULL, NULL),
(103, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, 4, NULL, 2, 2, 1, 4, NULL, NULL, '0.7506339519756231', '', '', '', '', '', '', 'awaiting_user', 'temp', NULL, 1, 1, 3, 3, 3, '2015-07-03 10:29:08', '2015-08-01 10:29:08', '2015-08-21 10:29:08', '2015-08-30 10:29:08', '2015-08-20 10:29:08', '2015-06-02 10:29:08', '2014-06-03 10:29:08', '2015-07-21 10:29:08', '2014-08-16 10:29:08', '2014-10-18 10:29:08', '2015-09-18 10:29:08', 1, 1, NULL, 3, 'Test ticket #103', 'Test ticket #103', NULL, NULL, NULL),
(104, NULL, 1, 1, NULL, NULL, NULL, NULL, 2, 1, NULL, 5, 2, 1, 3, NULL, NULL, '0.5698776147979103', '', '', '', '', '', '', 'resolved', 'temp', NULL, 1, 3, 5, 5, 4, '2015-01-16 10:29:08', '2014-10-30 10:29:08', '2014-08-11 10:29:08', '2015-07-16 10:29:08', NULL, '2015-09-30 10:29:08', '2015-07-14 10:29:08', '2014-08-27 10:29:08', '2015-01-03 10:29:08', '2015-04-23 10:29:08', '2015-10-02 10:29:08', 1, 2, NULL, 1, 'Test ticket #104', 'Test ticket #104', NULL, NULL, NULL),
(105, NULL, 1, 3, NULL, NULL, NULL, NULL, 1, 4, NULL, 1, NULL, 1, 1, NULL, 2, '0.7226341876412129', '', '', '', '', '', '', 'resolved', 'temp', NULL, 1, 3, 7, 2, 1, '2014-11-03 10:29:08', '2015-05-21 10:29:08', '2015-04-06 10:29:08', '2015-10-04 10:29:08', NULL, '2014-12-02 10:29:08', '2015-05-05 10:29:08', '2014-10-20 10:29:08', '2014-12-17 10:29:08', '2014-08-15 10:29:08', '2015-03-13 10:29:08', 3, 3, NULL, 2, 'Test ticket #105', 'Test ticket #105', NULL, NULL, NULL),
(106, NULL, 1, 1, NULL, NULL, NULL, NULL, 1, 3, NULL, 4, 1, 1, 4, NULL, NULL, '0.19032545871131631', '', '', '', '', '', '', 'resolved', 'temp', NULL, 0, 3, 7, 5, 2, '2014-11-13 10:29:08', '2015-09-02 10:29:08', '2015-03-27 10:29:08', '2014-10-05 10:29:08', '2015-01-27 10:29:08', '2015-04-24 10:29:08', '2015-07-27 10:29:08', '2014-10-08 10:29:08', '2015-06-22 10:29:08', '2014-07-21 10:29:08', '2014-09-08 10:29:08', 2, 1, NULL, 2, 'Test ticket #106', 'Test ticket #106', NULL, NULL, NULL),
(107, NULL, 1, 1, NULL, NULL, NULL, NULL, 4, 3, NULL, NULL, 1, 1, 1, NULL, NULL, '0.6776979413607138', '', '', '', '', '', '', 'archived', 'validating', NULL, 1, 3, 1, 3, 2, '2014-10-11 10:29:08', '2014-10-27 10:29:08', '2015-05-16 10:29:08', '2015-04-05 10:29:08', NULL, '2014-08-15 10:29:08', '2015-06-18 10:29:08', '2014-12-08 10:29:08', '2015-04-09 10:29:08', '2015-10-08 10:29:08', '2014-07-11 10:29:08', 3, 4, NULL, 1, 'Test ticket #107', 'Test ticket #107', NULL, NULL, NULL),
(108, NULL, 1, 1, NULL, NULL, NULL, NULL, 3, 1, NULL, 2, 1, 1, 3, NULL, NULL, '0.20429537557465524', '', '', '', '', '', '', 'hidden', 'temp', NULL, 1, 1, 2, 10, 2, '2014-07-31 10:29:08', '2015-04-24 10:29:08', '2015-09-02 10:29:08', '2015-04-11 10:29:08', NULL, '2014-08-24 10:29:08', '2015-04-10 10:29:08', '2015-04-16 10:29:08', '2014-11-11 10:29:08', '2015-05-28 10:29:08', '2015-04-20 10:29:08', 4, 3, NULL, 3, 'Test ticket #108', 'Test ticket #108', NULL, NULL, NULL),
(109, NULL, 1, 1, NULL, NULL, NULL, NULL, 2, 4, NULL, 2, NULL, 1, 3, NULL, NULL, '0.5089145409957642', '', '', '', '', '', '', 'hidden', 'spam', NULL, 0, 1, 6, 7, 3, '2015-08-10 10:29:08', '2014-09-22 10:29:08', '2015-02-21 10:29:08', '2015-10-07 10:29:08', NULL, '2015-07-09 10:29:08', '2014-05-28 10:29:08', '2015-03-03 10:29:08', '2015-06-28 10:29:08', '2014-10-18 10:29:08', '2014-06-27 10:29:08', 3, 4, NULL, 1, 'Test ticket #109', 'Test ticket #109', NULL, NULL, NULL),
(110, NULL, 1, 2, NULL, NULL, NULL, NULL, NULL, 3, NULL, 2, NULL, 1, 4, NULL, NULL, '0.15022727209164508', '', '', '', '', '', '', 'archived', 'validating', NULL, 1, 4, 2, 4, 2, '2014-08-25 10:29:08', '2014-07-04 10:29:08', '2015-07-20 10:29:08', '2015-09-21 10:29:08', NULL, '2015-04-14 10:29:08', '2014-10-22 10:29:08', '2015-02-28 10:29:08', '2015-08-12 10:29:08', '2015-06-08 10:29:08', '2014-07-27 10:29:08', 3, 3, NULL, 1, 'Test ticket #110', 'Test ticket #110', NULL, NULL, NULL),
(111, 99, 1, 1, NULL, NULL, NULL, NULL, 4, 4, NULL, 2, 2, 1, 2, NULL, NULL, '0.7028177172903136', '', '', '', '', '', '', 'hidden', 'spam', NULL, 1, 4, 3, 5, 2, '2015-10-02 10:29:08', '2014-11-04 10:29:08', '2015-04-21 10:29:08', '2014-11-04 10:29:08', '2015-04-12 10:29:08', '2014-09-23 10:29:08', '2014-10-06 10:29:08', '2015-03-28 10:29:08', '2014-10-02 10:29:08', '2015-01-09 10:29:08', '2015-02-02 10:29:08', 4, 4, NULL, 1, 'Test ticket #111', 'Test ticket #111', NULL, NULL, NULL),
(112, 76, 1, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, NULL, 1, 3, NULL, NULL, '0.8959202206655594', '', '', '', '', '', '', 'hidden', 'deleted', NULL, 0, 3, 5, 9, 2, '2014-12-23 10:29:08', '2015-07-30 10:29:08', '2014-06-13 10:29:08', '2015-03-21 10:29:08', NULL, '2015-03-24 10:29:08', '2014-12-04 10:29:08', '2014-07-17 10:29:08', '2014-11-25 10:29:08', '2015-02-07 10:29:08', '2015-01-18 10:29:08', 1, 2, NULL, 3, 'Test ticket #112', 'Test ticket #112', NULL, NULL, NULL),
(113, NULL, 1, 3, NULL, NULL, NULL, NULL, 3, 3, NULL, NULL, NULL, 1, 1, NULL, NULL, '0.5482711554954491', '', '', '', '', '', '', 'awaiting_agent', 'deleted', NULL, 1, 4, 9, 8, 2, '2015-06-04 10:29:08', '2015-04-20 10:29:08', '2014-06-17 10:29:08', '2014-09-27 10:29:08', NULL, '2015-06-27 10:29:08', '2015-04-16 10:29:08', '2015-07-30 10:29:08', '2014-11-16 10:29:08', '2014-08-16 10:29:08', '2015-06-16 10:29:08', 3, 2, NULL, 2, 'Test ticket #113', 'Test ticket #113', NULL, NULL, NULL),
(114, NULL, 1, 1, NULL, NULL, NULL, NULL, 3, 1, NULL, 3, NULL, 1, 2, NULL, NULL, '0.030533124721174244', '', '', '', '', '', '', 'awaiting_user', 'temp', NULL, 0, 4, 10, 3, 3, '2014-09-26 10:29:08', '2014-12-14 10:29:08', '2014-10-15 10:29:08', '2014-09-08 10:29:08', '2014-08-30 10:29:08', '2014-11-06 10:29:08', '2014-06-26 10:29:08', '2014-11-03 10:29:08', '2014-12-27 10:29:08', '2014-08-25 10:29:08', '2015-03-31 10:29:08', 2, 1, NULL, 1, 'Test ticket #114', 'Test ticket #114', NULL, NULL, NULL),
(115, 80, 1, 1, NULL, NULL, NULL, NULL, 3, 2, NULL, 3, 2, 1, 2, NULL, NULL, '0.33909249709834577', '', '', '', '', '', '', 'awaiting_user', 'validating', NULL, 1, 3, 8, 8, 3, '2014-09-12 10:29:08', '2015-08-24 10:29:08', '2015-08-14 10:29:08', '2015-05-20 10:29:08', NULL, '2015-01-10 10:29:08', '2015-02-11 10:29:08', '2014-09-20 10:29:08', '2015-03-22 10:29:08', '2014-10-24 10:29:08', '2015-05-16 10:29:08', 2, 4, NULL, 2, 'Test ticket #115', 'Test ticket #115', NULL, NULL, NULL),
(116, NULL, 1, 1, NULL, NULL, NULL, NULL, 4, 3, NULL, NULL, NULL, 1, 3, NULL, NULL, '0.41129274425217205', '', '', '', '', '', '', 'resolved', 'validating', NULL, 1, 3, 3, 4, 4, '2014-08-03 10:29:08', '2015-10-08 10:29:08', '2015-03-14 10:29:08', '2015-08-27 10:29:08', NULL, '2014-12-06 10:29:08', '2015-01-09 10:29:08', '2014-07-24 10:29:08', '2014-09-13 10:29:08', '2015-06-02 10:29:08', '2014-06-26 10:29:08', 4, 4, NULL, 1, 'Test ticket #116', 'Test ticket #116', NULL, NULL, NULL),
(117, NULL, 1, 3, NULL, NULL, NULL, NULL, 3, 3, NULL, 4, 2, 1, 3, NULL, 4, '0.9975021397671682', '', '', '', '', '', '', 'hidden', 'spam', NULL, 0, 3, 10, 10, 3, '2015-06-09 10:29:08', '2015-03-02 10:29:08', '2015-02-13 10:29:08', '2015-09-13 10:29:08', NULL, '2015-10-05 10:29:08', '2015-01-14 10:29:08', '2014-11-16 10:29:08', '2014-11-12 10:29:08', '2015-04-19 10:29:08', '2014-10-03 10:29:08', 3, 1, NULL, 2, 'Test ticket #117', 'Test ticket #117', NULL, NULL, NULL),
(118, NULL, 1, 3, NULL, NULL, NULL, NULL, 2, 3, NULL, 2, 2, 1, 4, NULL, NULL, '0.43987088505166666', '', '', '', '', '', '', 'resolved', 'temp', NULL, 1, 2, 2, 10, 1, '2014-10-29 10:29:08', '2015-06-23 10:29:08', '2015-10-07 10:29:08', '2015-04-05 10:29:08', NULL, '2015-07-27 10:29:08', '2015-07-13 10:29:08', '2015-03-07 10:29:08', '2014-11-25 10:29:08', '2014-07-24 10:29:08', '2015-01-29 10:29:08', 4, 4, NULL, 3, 'Test ticket #118', 'Test ticket #118', NULL, NULL, NULL),
(119, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, 3, NULL, 4, NULL, 1, 2, NULL, 1, '0.6801729832591238', '', '', '', '', '', '', 'awaiting_agent', 'deleted', NULL, 1, 3, 3, 7, 1, '2015-06-30 10:29:08', '2015-04-06 10:29:08', '2015-06-01 10:29:08', '2015-07-13 10:29:08', NULL, '2014-06-01 10:29:08', '2014-11-25 10:29:08', '2015-06-25 10:29:08', '2015-07-27 10:29:08', '2015-08-19 10:29:08', '2015-09-04 10:29:08', 1, 4, NULL, 3, 'Test ticket #119', 'Test ticket #119', NULL, NULL, NULL),
(120, NULL, 1, 2, NULL, NULL, NULL, NULL, 1, 4, NULL, 2, 1, 1, 2, NULL, 4, '0.5024416665569336', '', '', '', '', '', '', 'hidden', 'temp', NULL, 1, 3, 8, 4, 3, '2014-09-18 10:29:08', '2015-07-08 10:29:08', '2014-12-05 10:29:08', '2015-01-25 10:29:08', '2014-10-11 10:29:08', '2015-08-29 10:29:08', '2015-06-13 10:29:08', '2014-07-01 10:29:08', '2014-06-25 10:29:08', '2014-07-09 10:29:08', '2014-09-29 10:29:08', 1, 4, NULL, 3, 'Test ticket #120', 'Test ticket #120', NULL, NULL, NULL),
(121, NULL, 1, 3, NULL, NULL, NULL, NULL, 2, 2, NULL, 1, NULL, 1, 2, NULL, NULL, '0.4550679963594936', '', '', '', '', '', '', 'hidden', 'spam', NULL, 1, 1, 1, 7, 1, '2015-05-08 10:29:08', '2015-03-28 10:29:08', '2015-09-24 10:29:08', '2014-06-07 10:29:08', NULL, '2015-09-17 10:29:08', '2014-08-17 10:29:08', '2015-09-18 10:29:08', '2014-10-25 10:29:08', '2015-04-07 10:29:08', '2014-09-28 10:29:08', 3, 4, NULL, 1, 'Test ticket #121', 'Test ticket #121', NULL, NULL, NULL),
(122, NULL, 1, 1, NULL, NULL, NULL, NULL, 4, 4, NULL, NULL, 1, 1, 1, NULL, NULL, '0.7638918010181671', '', '', '', '', '', '', 'hidden', 'deleted', NULL, 0, 4, 3, 10, 4, '2015-01-26 10:29:08', '2015-10-06 10:29:08', '2015-02-02 10:29:08', '2015-02-16 10:29:08', NULL, '2014-07-31 10:29:08', '2014-09-12 10:29:08', '2015-05-11 10:29:08', '2015-07-22 10:29:08', '2014-07-22 10:29:08', '2014-06-16 10:29:08', 1, 4, NULL, 2, 'Test ticket #122', 'Test ticket #122', NULL, NULL, NULL),
(123, 27, 1, 2, NULL, NULL, NULL, NULL, 1, 1, NULL, 3, NULL, 1, 1, NULL, 3, '0.6850316959293854', '', '', '', '', '', '', 'resolved', 'validating', NULL, 1, 3, 4, 9, 1, '2015-01-09 10:29:08', '2015-09-25 10:29:08', '2015-01-29 10:29:08', '2015-03-02 10:29:08', '2014-10-30 10:29:08', '2015-08-10 10:29:08', '2015-01-17 10:29:08', '2015-05-16 10:29:08', '2014-08-01 10:29:08', '2015-02-21 10:29:08', '2014-10-26 10:29:08', 1, 2, NULL, 3, 'Test ticket #123', 'Test ticket #123', NULL, NULL, NULL),
(124, NULL, 1, 3, NULL, NULL, NULL, NULL, 3, 2, NULL, 2, 1, 1, 3, NULL, 1, '0.8481277738214729', '', '', '', '', '', '', 'archived', 'validating', NULL, 0, 2, 2, 1, 4, '2014-12-22 10:29:08', '2014-11-19 10:29:08', '2015-02-05 10:29:08', '2015-01-24 10:29:08', '2015-08-20 10:29:08', '2014-06-21 10:29:08', '2015-02-27 10:29:08', '2015-03-28 10:29:08', '2014-12-09 10:29:08', '2014-08-02 10:29:08', '2015-01-31 10:29:08', 4, 1, NULL, 2, 'Test ticket #124', 'Test ticket #124', NULL, NULL, NULL),
(125, NULL, 1, 2, NULL, NULL, NULL, NULL, 2, 3, NULL, 2, NULL, 1, 4, NULL, NULL, '0.4021120205541253', '', '', '', '', '', '', 'hidden', 'spam', NULL, 1, 4, 2, 2, 2, '2015-07-23 10:29:08', '2014-09-11 10:29:08', '2015-02-21 10:29:08', '2014-06-23 10:29:08', NULL, '2014-07-11 10:29:08', '2015-01-31 10:29:08', '2014-09-18 10:29:08', '2015-04-13 10:29:08', '2015-02-16 10:29:08', '2015-05-24 10:29:08', 4, 1, NULL, 1, 'Test ticket #125', 'Test ticket #125', NULL, NULL, NULL),
(126, NULL, 1, 1, NULL, NULL, NULL, NULL, 3, 2, NULL, 2, 1, 1, 3, NULL, NULL, '0.38575677330210506', '', '', '', '', '', '', 'awaiting_user', 'deleted', NULL, 1, 2, 6, 5, 4, '2015-03-11 10:29:08', '2014-09-10 10:29:08', '2014-11-09 10:29:08', '2014-06-07 10:29:08', NULL, '2015-02-19 10:29:08', '2014-11-03 10:29:08', '2015-10-05 10:29:08', '2014-05-28 10:29:08', '2014-06-14 10:29:08', '2014-08-24 10:29:08', 1, 3, NULL, 2, 'Test ticket #126', 'Test ticket #126', NULL, NULL, NULL);
INSERT INTO `tickets` (`id`, `parent_ticket_id`, `language_id`, `department_id`, `category_id`, `priority_id`, `workflow_id`, `product_id`, `person_id`, `person_email_id`, `person_email_validating_id`, `agent_id`, `agent_team_id`, `organization_id`, `linked_chat_id`, `email_account_id`, `locked_by_agent`, `ref`, `auth`, `sent_to_address`, `email_account_address`, `creation_system`, `creation_system_option`, `ticket_hash`, `status`, `hidden_status`, `validating`, `is_hold`, `urgency`, `count_agent_replies`, `count_user_replies`, `feedback_rating`, `date_feedback_rating`, `date_created`, `date_resolved`, `date_archived`, `date_first_agent_assign`, `date_first_agent_reply`, `date_last_agent_reply`, `date_last_user_reply`, `date_agent_waiting`, `date_user_waiting`, `date_status`, `total_user_waiting`, `total_to_first_reply`, `date_locked`, `has_attachments`, `subject`, `original_subject`, `properties`, `worst_sla_status`, `waiting_times`) VALUES
(127, NULL, 1, 3, NULL, NULL, NULL, NULL, 1, 3, NULL, 4, 2, 1, 4, NULL, NULL, '0.04767069690643875', '', '', '', '', '', '', 'hidden', 'deleted', NULL, 0, 2, 7, 1, 3, '2015-04-05 10:29:08', '2015-05-02 10:29:08', '2015-02-11 10:29:08', '2015-03-05 10:29:08', NULL, '2015-04-01 10:29:08', '2014-10-28 10:29:08', '2015-05-05 10:29:08', '2015-02-03 10:29:08', '2015-01-15 10:29:08', '2015-07-11 10:29:08', 2, 4, NULL, 3, 'Test ticket #127', 'Test ticket #127', NULL, NULL, NULL),
(128, NULL, 1, 2, NULL, NULL, NULL, NULL, 2, 2, NULL, 3, NULL, 1, 4, NULL, NULL, '0.9304656059764936', '', '', '', '', '', '', 'hidden', 'temp', NULL, 0, 2, 2, 2, 2, '2015-04-12 10:29:08', '2014-10-11 10:29:08', '2015-01-05 10:29:08', '2014-12-18 10:29:08', '2015-05-20 10:29:08', '2014-11-16 10:29:08', '2015-03-15 10:29:08', '2015-08-13 10:29:08', '2015-05-01 10:29:08', '2015-05-30 10:29:08', '2015-04-13 10:29:08', 4, 4, NULL, 2, 'Test ticket #128', 'Test ticket #128', NULL, NULL, NULL),
(129, NULL, 1, 1, NULL, NULL, NULL, NULL, 4, 2, NULL, 1, NULL, 1, 2, NULL, NULL, '0.7819689929317394', '', '', '', '', '', '', 'hidden', 'validating', NULL, 0, 4, 3, 8, 1, '2014-12-25 10:29:08', '2015-05-06 10:29:08', '2014-08-18 10:29:08', '2015-06-14 10:29:08', NULL, '2014-11-29 10:29:08', '2015-07-31 10:29:08', '2014-08-26 10:29:08', '2014-11-11 10:29:08', '2014-08-01 10:29:08', '2015-04-20 10:29:08', 1, 3, NULL, 2, 'Test ticket #129', 'Test ticket #129', NULL, NULL, NULL),
(130, NULL, 1, 1, NULL, NULL, NULL, NULL, 2, 2, NULL, 4, NULL, 1, 4, NULL, NULL, '0.6992388457984094', '', '', '', '', '', '', 'archived', 'temp', NULL, 0, 3, 7, 6, 3, '2014-06-09 10:29:08', '2014-10-05 10:29:08', '2014-09-22 10:29:08', '2014-12-08 10:29:08', NULL, '2014-06-17 10:29:08', '2015-01-23 10:29:08', '2014-10-16 10:29:08', '2015-09-30 10:29:08', '2014-06-23 10:29:08', '2014-11-06 10:29:08', 3, 3, NULL, 2, 'Test ticket #130', 'Test ticket #130', NULL, NULL, NULL),
(131, NULL, 1, 1, NULL, NULL, NULL, NULL, 1, 1, NULL, 3, 1, 1, 1, NULL, 4, '0.04396486472707695', '', '', '', '', '', '', 'archived', 'temp', NULL, 1, 3, 8, 1, 1, '2014-07-17 10:29:08', '2015-02-10 10:29:08', '2014-10-15 10:29:08', '2015-07-29 10:29:08', NULL, '2015-04-01 10:29:08', '2015-06-22 10:29:08', '2014-06-24 10:29:08', '2015-09-05 10:29:08', '2015-01-30 10:29:08', '2015-05-04 10:29:08', 1, 2, NULL, 3, 'Test ticket #131', 'Test ticket #131', NULL, NULL, NULL),
(132, 99, 1, 1, NULL, NULL, NULL, NULL, NULL, 2, NULL, 3, 2, 1, 4, NULL, NULL, '0.27281044355855366', '', '', '', '', '', '', 'archived', 'temp', NULL, 1, 1, 6, 4, 2, '2015-05-16 10:29:08', '2014-11-25 10:29:08', '2015-05-09 10:29:08', '2014-12-05 10:29:08', NULL, '2014-06-16 10:29:08', '2015-05-06 10:29:08', '2014-11-04 10:29:08', '2015-02-22 10:29:08', '2015-06-06 10:29:08', '2014-07-26 10:29:08', 3, 3, NULL, 1, 'Test ticket #132', 'Test ticket #132', NULL, NULL, NULL),
(133, 113, 1, 2, NULL, NULL, NULL, NULL, 3, 2, NULL, 1, 1, 1, 2, NULL, NULL, '0.3196987680342968', '', '', '', '', '', '', 'archived', 'deleted', NULL, 1, 4, 8, 3, 2, '2014-12-13 10:29:08', '2015-08-17 10:29:08', '2014-10-10 10:29:08', '2015-05-01 10:29:08', NULL, '2015-07-17 10:29:08', '2015-01-03 10:29:08', '2015-05-20 10:29:08', '2014-10-02 10:29:08', '2014-08-03 10:29:08', '2015-08-25 10:29:08', 4, 1, NULL, 2, 'Test ticket #133', 'Test ticket #133', NULL, NULL, NULL),
(134, NULL, 1, 2, NULL, NULL, NULL, NULL, NULL, 3, NULL, 4, 2, 1, 3, NULL, NULL, '0.47639766845516646', '', '', '', '', '', '', 'hidden', 'spam', NULL, 1, 4, 8, 7, 4, '2014-07-03 10:29:08', '2014-09-22 10:29:08', '2015-09-20 10:29:08', '2014-07-18 10:29:08', '2015-04-08 10:29:08', '2015-07-27 10:29:08', '2014-11-25 10:29:08', '2014-10-05 10:29:08', '2014-09-14 10:29:08', '2014-11-03 10:29:08', '2015-09-08 10:29:08', 2, 1, NULL, 1, 'Test ticket #134', 'Test ticket #134', NULL, NULL, NULL),
(135, NULL, 1, 3, NULL, NULL, NULL, NULL, 3, 3, NULL, 4, NULL, 1, 1, NULL, NULL, '0.3660678168442732', '', '', '', '', '', '', 'archived', 'validating', NULL, 1, 4, 7, 2, 4, '2014-07-15 10:29:08', '2014-07-15 10:29:08', '2014-09-04 10:29:08', '2015-05-15 10:29:08', NULL, '2015-02-16 10:29:08', '2015-08-01 10:29:08', '2015-05-21 10:29:08', '2014-05-30 10:29:08', '2015-08-10 10:29:08', '2014-12-06 10:29:08', 3, 3, NULL, 2, 'Test ticket #135', 'Test ticket #135', NULL, NULL, NULL),
(136, NULL, 1, 1, NULL, NULL, NULL, NULL, 3, 3, NULL, 4, 2, 1, 4, NULL, 1, '0.7672926325046389', '', '', '', '', '', '', 'hidden', 'spam', NULL, 0, 4, 10, 10, 4, '2015-02-14 10:29:08', '2014-08-26 10:29:08', '2014-11-09 10:29:08', '2014-07-25 10:29:08', NULL, '2015-03-25 10:29:08', '2014-09-30 10:29:08', '2015-01-04 10:29:08', '2015-01-16 10:29:08', '2014-05-31 10:29:08', '2015-04-11 10:29:08', 4, 1, NULL, 3, 'Test ticket #136', 'Test ticket #136', NULL, NULL, NULL),
(137, NULL, 1, 2, NULL, NULL, NULL, NULL, 2, 1, NULL, 1, 1, 1, 4, NULL, 4, '0.3265236656428535', '', '', '', '', '', '', 'hidden', 'validating', NULL, 1, 1, 5, 2, 3, '2015-04-04 10:29:08', '2015-08-04 10:29:08', '2015-01-19 10:29:08', '2015-06-14 10:29:08', NULL, '2015-05-31 10:29:08', '2015-01-19 10:29:08', '2014-08-12 10:29:08', '2014-11-16 10:29:08', '2014-10-11 10:29:08', '2014-11-08 10:29:08', 1, 4, NULL, 2, 'Test ticket #137', 'Test ticket #137', NULL, NULL, NULL),
(138, 58, 1, 3, NULL, NULL, NULL, NULL, 3, 4, NULL, 2, 2, 1, 3, NULL, NULL, '0.8990547758518297', '', '', '', '', '', '', 'hidden', 'temp', NULL, 1, 4, 1, 3, 1, '2015-05-29 10:29:08', '2014-11-07 10:29:08', '2015-01-01 10:29:08', '2014-09-06 10:29:08', '2015-05-15 10:29:08', '2015-08-29 10:29:08', '2015-01-21 10:29:08', '2015-04-09 10:29:08', '2015-05-30 10:29:08', '2015-06-22 10:29:08', '2015-05-14 10:29:08', 4, 1, NULL, 2, 'Test ticket #138', 'Test ticket #138', NULL, NULL, NULL),
(139, NULL, 1, 2, NULL, NULL, NULL, NULL, 1, 1, NULL, 4, 1, 1, 1, NULL, NULL, '0.21478203331565673', '', '', '', '', '', '', 'hidden', 'spam', NULL, 1, 3, 10, 1, 2, '2015-08-12 10:29:08', '2015-07-12 10:29:08', '2015-01-13 10:29:08', '2015-07-22 10:29:08', NULL, '2015-03-27 10:29:08', '2015-03-05 10:29:08', '2014-05-27 10:29:08', '2014-10-26 10:29:08', '2015-02-10 10:29:08', '2015-05-04 10:29:08', 1, 3, NULL, 1, 'Test ticket #139', 'Test ticket #139', NULL, NULL, NULL),
(140, NULL, 1, 3, NULL, NULL, NULL, NULL, 4, 4, NULL, 2, 2, 1, 3, NULL, NULL, '0.9083429136391198', '', '', '', '', '', '', 'awaiting_user', 'temp', NULL, 1, 4, 7, 9, 1, '2015-04-20 10:29:08', '2015-06-27 10:29:08', '2015-10-06 10:29:08', '2015-03-21 10:29:08', NULL, '2014-09-14 10:29:08', '2014-07-13 10:29:08', '2015-07-05 10:29:08', '2015-06-14 10:29:08', '2014-12-15 10:29:08', '2015-05-23 10:29:08', 3, 1, NULL, 3, 'Test ticket #140', 'Test ticket #140', NULL, NULL, NULL),
(141, NULL, 1, 2, NULL, NULL, NULL, NULL, 2, 1, NULL, 4, 2, 1, 4, NULL, NULL, '0.399512968398177', '', '', '', '', '', '', 'awaiting_agent', 'temp', NULL, 1, 3, 6, 3, 2, '2015-01-22 10:29:08', '2015-05-18 10:29:08', '2014-07-25 10:29:08', '2015-01-09 10:29:08', NULL, '2014-09-22 10:29:08', '2014-12-25 10:29:08', '2014-12-17 10:29:08', '2015-06-17 10:29:08', '2015-04-11 10:29:08', '2015-08-08 10:29:08', 3, 2, NULL, 3, 'Test ticket #141', 'Test ticket #141', NULL, NULL, NULL),
(142, NULL, 1, 2, NULL, NULL, NULL, NULL, 5, 1, NULL, 5, NULL, 1, 4, NULL, NULL, '0.34450109614478525', '', '', '', '', '', '', 'hidden', 'spam', NULL, 0, 2, 8, 7, 1, '2014-09-19 10:29:08', '2015-04-26 10:29:08', '2015-04-17 10:29:08', '2014-09-29 10:29:08', NULL, '2015-06-02 10:29:08', '2015-07-08 10:29:08', '2015-07-27 10:29:08', '2015-07-07 10:29:08', '2015-02-05 10:29:08', '2014-07-18 10:29:08', 4, 2, NULL, 2, 'Test ticket #142', 'Test ticket #142', NULL, NULL, NULL),
(143, NULL, 1, 3, NULL, NULL, NULL, NULL, 1, 4, NULL, NULL, NULL, 1, 3, NULL, NULL, '0.7774260684637596', '', '', '', '', '', '', 'awaiting_user', 'validating', NULL, 1, 3, 10, 3, 2, '2014-12-09 10:29:08', '2015-05-31 10:29:08', '2015-02-08 10:29:08', '2014-11-24 10:29:08', NULL, '2014-09-20 10:29:08', '2014-11-29 10:29:08', '2014-08-19 10:29:08', '2015-05-22 10:29:08', '2014-07-16 10:29:08', '2014-11-13 10:29:08', 3, 1, NULL, 1, 'Test ticket #143', 'Test ticket #143', NULL, NULL, NULL),
(144, NULL, 1, 1, NULL, NULL, NULL, NULL, 3, 4, NULL, 2, 1, 1, 3, NULL, NULL, '0.43016323301025033', '', '', '', '', '', '', 'resolved', 'spam', NULL, 1, 2, 3, 2, 1, '2014-07-21 10:29:08', '2015-06-29 10:29:08', '2015-04-18 10:29:08', '2015-08-04 10:29:08', NULL, '2014-11-07 10:29:08', '2015-01-22 10:29:08', '2014-12-22 10:29:08', '2015-04-17 10:29:08', '2014-05-27 10:29:08', '2014-06-21 10:29:08', 3, 4, NULL, 1, 'Test ticket #144', 'Test ticket #144', NULL, NULL, NULL),
(145, 25, 1, 1, NULL, NULL, NULL, NULL, 5, 4, NULL, 5, 1, 1, 1, NULL, NULL, '0.6949268846725364', '', '', '', '', '', '', 'resolved', 'spam', NULL, 1, 1, 2, 8, 2, '2015-01-03 10:29:08', '2014-11-22 10:29:08', '2015-01-16 10:29:08', '2014-10-05 10:29:08', NULL, '2015-06-10 10:29:08', '2014-06-28 10:29:08', '2014-06-20 10:29:08', '2014-06-19 10:29:08', '2014-07-11 10:29:08', '2014-10-28 10:29:08', 3, 3, NULL, 2, 'Test ticket #145', 'Test ticket #145', NULL, NULL, NULL),
(146, NULL, 1, 3, NULL, NULL, NULL, NULL, 1, 2, NULL, 4, 2, 1, 3, NULL, 4, '0.7676332227584284', '', '', '', '', '', '', 'resolved', 'deleted', NULL, 0, 2, 1, 3, 4, '2015-06-30 10:29:08', '2014-10-22 10:29:08', '2014-07-10 10:29:08', '2015-02-28 10:29:08', NULL, '2015-07-31 10:29:08', '2015-06-24 10:29:08', '2014-11-21 10:29:08', '2014-12-23 10:29:08', '2014-06-15 10:29:08', '2015-09-02 10:29:08', 2, 1, NULL, 2, 'Test ticket #146', 'Test ticket #146', NULL, NULL, NULL),
(147, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, 4, NULL, 3, NULL, 1, 3, NULL, NULL, '0.16298557646850642', '', '', '', '', '', '', 'awaiting_user', 'temp', NULL, 0, 2, 3, 4, 3, '2014-10-07 10:29:08', '2015-02-07 10:29:08', '2015-06-14 10:29:08', '2014-10-20 10:29:08', '2014-08-19 10:29:08', '2015-09-19 10:29:08', '2014-10-22 10:29:08', '2015-03-25 10:29:08', '2014-08-03 10:29:08', '2015-08-02 10:29:08', '2015-08-29 10:29:08', 1, 4, NULL, 3, 'Test ticket #147', 'Test ticket #147', NULL, NULL, NULL),
(148, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 5, 2, 1, 2, NULL, 3, '0.22644250115979697', '', '', '', '', '', '', 'hidden', 'temp', NULL, 1, 2, 10, 7, 2, '2014-11-25 10:29:08', '2015-06-23 10:29:08', '2015-07-13 10:29:08', '2015-06-17 10:29:08', '2014-12-08 10:29:08', '2015-04-08 10:29:08', '2015-10-03 10:29:08', '2014-06-21 10:29:08', '2014-10-16 10:29:08', '2014-10-10 10:29:08', '2015-02-05 10:29:08', 2, 4, NULL, 3, 'Test ticket #148', 'Test ticket #148', NULL, NULL, NULL),
(149, 68, 1, 3, NULL, NULL, NULL, NULL, 1, 3, NULL, 5, 1, 1, 2, NULL, NULL, '0.9597678007183297', '', '', '', '', '', '', 'hidden', 'spam', NULL, 1, 2, 10, 2, 4, '2015-02-25 10:29:08', '2014-07-06 10:29:08', '2015-06-11 10:29:08', '2015-03-03 10:29:08', NULL, '2015-08-24 10:29:08', '2015-10-05 10:29:08', '2014-09-22 10:29:08', '2014-09-06 10:29:08', '2014-10-29 10:29:08', '2015-09-10 10:29:08', 1, 4, NULL, 3, 'Test ticket #149', 'Test ticket #149', NULL, NULL, NULL),
(150, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 5, 2, 1, 1, NULL, NULL, '0.37964800035548213', '', '', '', '', '', '', 'hidden', 'spam', NULL, 1, 4, 7, 4, 4, '2014-07-08 10:29:08', '2015-06-19 10:29:08', '2015-04-03 10:29:08', '2015-06-26 10:29:08', NULL, '2014-06-10 10:29:08', '2015-08-22 10:29:08', '2015-01-02 10:29:08', '2015-01-25 10:29:08', '2014-07-24 10:29:08', '2014-07-25 10:29:08', 4, 1, NULL, 1, 'Test ticket #150', 'Test ticket #150', NULL, NULL, NULL),
(151, NULL, 1, 1, NULL, NULL, NULL, NULL, 1, 3, NULL, 4, NULL, 1, 4, NULL, NULL, '0.9855690337583134', '', '', '', '', '', '', 'hidden', 'spam', NULL, 1, 2, 5, 4, 1, '2015-08-12 10:29:08', '2014-06-28 10:29:08', '2015-04-29 10:29:08', '2014-08-24 10:29:08', NULL, '2015-06-27 10:29:08', '2014-11-25 10:29:08', '2015-01-06 10:29:08', '2014-08-10 10:29:08', '2014-12-15 10:29:08', '2015-03-09 10:29:08', 2, 2, NULL, 1, 'Test ticket #151', 'Test ticket #151', NULL, NULL, NULL),
(152, 34, 1, 1, NULL, NULL, NULL, NULL, 4, 2, NULL, 1, NULL, 1, 1, NULL, NULL, '0.642975069249957', '', '', '', '', '', '', 'awaiting_user', 'deleted', NULL, 1, 1, 2, 8, 1, '2015-03-13 10:29:08', '2014-09-22 10:29:08', '2015-01-01 10:29:08', '2015-01-27 10:29:08', NULL, '2014-08-31 10:29:08', '2015-03-06 10:29:08', '2014-10-02 10:29:08', '2015-03-14 10:29:08', '2014-08-06 10:29:08', '2015-09-20 10:29:08', 3, 4, NULL, 3, 'Test ticket #152', 'Test ticket #152', NULL, NULL, NULL),
(153, NULL, 1, 2, NULL, NULL, NULL, NULL, 3, 3, NULL, 3, 1, 1, 4, NULL, NULL, '0.2963476826402803', '', '', '', '', '', '', 'hidden', 'spam', NULL, 0, 4, 5, 2, 3, '2014-09-12 10:29:08', '2014-07-18 10:29:08', '2015-08-06 10:29:08', '2014-06-21 10:29:08', NULL, '2014-06-08 10:29:08', '2014-09-13 10:29:08', '2014-06-04 10:29:08', '2014-12-26 10:29:08', '2014-07-08 10:29:08', '2014-08-05 10:29:08', 3, 1, NULL, 1, 'Test ticket #153', 'Test ticket #153', NULL, NULL, NULL),
(154, NULL, 1, 1, NULL, NULL, NULL, NULL, 5, 3, NULL, 4, 1, 1, 4, NULL, NULL, '0.5576334228344573', '', '', '', '', '', '', 'hidden', 'spam', NULL, 1, 3, 7, 2, 4, '2014-10-08 10:29:08', '2015-07-25 10:29:08', '2014-12-31 10:29:08', '2015-04-10 10:29:08', NULL, '2015-01-10 10:29:08', '2015-04-25 10:29:08', '2015-09-20 10:29:08', '2015-07-06 10:29:08', '2014-08-17 10:29:08', '2014-12-09 10:29:08', 3, 4, NULL, 2, 'Test ticket #154', 'Test ticket #154', NULL, NULL, NULL),
(155, NULL, 1, 1, NULL, NULL, NULL, NULL, 2, 4, NULL, 2, 1, 1, 2, NULL, NULL, '0.9011738299403096', '', '', '', '', '', '', 'awaiting_user', 'deleted', NULL, 1, 3, 1, 1, 2, '2014-07-23 10:29:08', '2015-08-13 10:29:08', '2014-07-08 10:29:08', '2015-06-13 10:29:08', NULL, '2015-02-23 10:29:08', '2014-06-06 10:29:08', '2015-01-17 10:29:08', '2014-10-21 10:29:08', '2014-06-25 10:29:08', '2014-12-18 10:29:08', 1, 4, NULL, 3, 'Test ticket #155', 'Test ticket #155', NULL, NULL, NULL),
(156, 101, 1, 2, NULL, NULL, NULL, NULL, 3, 4, NULL, 5, NULL, 1, 3, NULL, NULL, '0.5678612725509902', '', '', '', '', '', '', 'awaiting_agent', 'validating', NULL, 0, 2, 5, 10, 3, '2015-07-26 10:29:08', '2014-07-15 10:29:08', '2015-09-10 10:29:08', '2014-12-20 10:29:08', NULL, '2015-10-08 10:29:08', '2014-09-26 10:29:08', '2014-09-17 10:29:08', '2014-12-12 10:29:08', '2014-10-31 10:29:08', '2014-12-02 10:29:08', 1, 2, NULL, 3, 'Test ticket #156', 'Test ticket #156', NULL, NULL, NULL),
(157, 121, 1, 3, NULL, NULL, NULL, NULL, 3, 2, NULL, 3, 1, 1, 2, NULL, NULL, '0.790519615440182', '', '', '', '', '', '', 'resolved', 'deleted', NULL, 0, 2, 9, 1, 3, '2015-01-13 10:29:08', '2015-03-03 10:29:08', '2014-12-23 10:29:08', '2014-12-21 10:29:08', NULL, '2015-07-29 10:29:08', '2015-07-11 10:29:08', '2015-02-17 10:29:08', '2014-09-05 10:29:08', '2014-12-21 10:29:08', '2015-01-20 10:29:08', 4, 4, NULL, 1, 'Test ticket #157', 'Test ticket #157', NULL, NULL, NULL),
(158, 67, 1, 1, NULL, NULL, NULL, NULL, 2, 4, NULL, 2, NULL, 1, 3, NULL, NULL, '0.9802826931516478', '', '', '', '', '', '', 'hidden', 'spam', NULL, 0, 1, 3, 5, 4, '2014-12-12 10:29:08', '2014-12-25 10:29:08', '2015-09-02 10:29:08', '2014-11-23 10:29:08', NULL, '2014-06-05 10:29:08', '2014-06-15 10:29:08', '2014-08-03 10:29:08', '2015-03-08 10:29:08', '2015-01-03 10:29:08', '2015-01-30 10:29:08', 4, 3, NULL, 3, 'Test ticket #158', 'Test ticket #158', NULL, NULL, NULL),
(159, 68, 1, 2, NULL, NULL, NULL, NULL, NULL, 4, NULL, 4, 2, 1, 4, NULL, 3, '0.6350939177303518', '', '', '', '', '', '', 'awaiting_agent', 'deleted', NULL, 0, 1, 7, 2, 4, '2014-12-25 10:29:08', '2015-03-18 10:29:08', '2015-05-03 10:29:08', '2015-04-10 10:29:08', NULL, '2015-06-20 10:29:08', '2015-01-21 10:29:08', '2014-06-25 10:29:08', '2015-07-28 10:29:08', '2014-07-17 10:29:08', '2015-09-11 10:29:08', 3, 3, NULL, 3, 'Test ticket #159', 'Test ticket #159', NULL, NULL, NULL),
(160, NULL, 1, 1, NULL, NULL, NULL, NULL, 4, 2, NULL, 1, 2, 1, 4, NULL, NULL, '0.9275645035575745', '', '', '', '', '', '', 'hidden', 'spam', NULL, 1, 2, 1, 7, 4, '2014-08-25 10:29:08', '2015-06-15 10:29:08', '2014-10-21 10:29:08', '2014-08-20 10:29:08', NULL, '2014-11-19 10:29:08', '2015-07-24 10:29:08', '2014-08-23 10:29:08', '2014-11-13 10:29:08', '2014-08-20 10:29:08', '2015-07-20 10:29:08', 2, 1, NULL, 3, 'Test ticket #160', 'Test ticket #160', NULL, NULL, NULL),
(161, NULL, 1, 3, NULL, NULL, NULL, NULL, 4, 1, NULL, 3, 2, 1, 1, NULL, NULL, '0.6934953729561487', '', '', '', '', '', '', 'resolved', 'validating', NULL, 0, 4, 5, 7, 4, '2015-01-27 10:29:08', '2014-07-11 10:29:08', '2015-09-30 10:29:08', '2015-04-11 10:29:08', NULL, '2014-11-05 10:29:08', '2015-08-08 10:29:08', '2014-12-19 10:29:08', '2014-12-28 10:29:08', '2015-08-28 10:29:08', '2014-10-20 10:29:08', 2, 2, NULL, 3, 'Test ticket #161', 'Test ticket #161', NULL, NULL, NULL),
(162, 50, 1, 3, NULL, NULL, NULL, NULL, NULL, 4, NULL, NULL, 2, 1, 1, NULL, NULL, '0.8101609962155679', '', '', '', '', '', '', 'awaiting_agent', 'deleted', NULL, 0, 2, 5, 5, 4, '2014-09-24 10:29:08', '2015-04-26 10:29:08', '2015-04-04 10:29:08', '2014-07-23 10:29:08', NULL, '2014-08-09 10:29:08', '2015-04-18 10:29:08', '2015-07-08 10:29:08', '2014-07-24 10:29:08', '2014-08-05 10:29:08', '2014-11-18 10:29:08', 3, 2, NULL, 3, 'Test ticket #162', 'Test ticket #162', NULL, NULL, NULL),
(163, NULL, 1, 2, NULL, NULL, NULL, NULL, 2, 1, NULL, 2, 1, 1, 1, NULL, 2, '0.8955084512899708', '', '', '', '', '', '', 'resolved', 'validating', NULL, 1, 2, 5, 9, 1, '2015-02-09 10:29:08', '2015-04-23 10:29:08', '2015-06-15 10:29:08', '2015-07-26 10:29:08', '2015-09-14 10:29:08', '2014-09-04 10:29:08', '2014-08-09 10:29:08', '2014-08-07 10:29:08', '2014-10-13 10:29:08', '2015-09-19 10:29:08', '2015-09-23 10:29:08', 1, 1, NULL, 2, 'Test ticket #163', 'Test ticket #163', NULL, NULL, NULL),
(164, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, 4, NULL, 5, 1, 1, 1, NULL, NULL, '0.1218730873631994', '', '', '', '', '', '', 'archived', 'deleted', NULL, 1, 3, 8, 9, 1, '2014-07-11 10:29:08', '2015-06-14 10:29:08', '2015-03-06 10:29:08', '2015-02-18 10:29:08', NULL, '2014-10-08 10:29:08', '2014-12-21 10:29:08', '2014-10-11 10:29:08', '2014-07-31 10:29:08', '2015-07-14 10:29:08', '2015-05-31 10:29:08', 4, 1, NULL, 2, 'Test ticket #164', 'Test ticket #164', NULL, NULL, NULL),
(165, NULL, 1, 2, NULL, NULL, NULL, NULL, 4, 3, NULL, 4, 1, 1, 2, NULL, NULL, '0.3913259863772672', '', '', '', '', '', '', 'awaiting_user', 'temp', NULL, 1, 1, 8, 4, 2, '2015-02-25 10:29:08', '2014-09-09 10:29:08', '2014-12-16 10:29:08', '2014-12-13 10:29:08', NULL, '2015-05-25 10:29:08', '2014-10-13 10:29:08', '2014-09-12 10:29:08', '2014-09-24 10:29:08', '2015-02-28 10:29:08', '2014-06-22 10:29:08', 2, 1, NULL, 2, 'Test ticket #165', 'Test ticket #165', NULL, NULL, NULL),
(166, 27, 1, 1, NULL, NULL, NULL, NULL, 3, 3, NULL, NULL, 2, 1, 3, NULL, NULL, '0.6223131163253571', '', '', '', '', '', '', 'awaiting_user', 'deleted', NULL, 1, 4, 4, 4, 4, '2015-08-05 10:29:08', '2015-09-03 10:29:08', '2014-06-14 10:29:08', '2014-12-11 10:29:08', NULL, '2014-08-19 10:29:08', '2014-09-19 10:29:08', '2015-04-14 10:29:08', '2015-02-15 10:29:08', '2015-05-19 10:29:08', '2015-10-01 10:29:08', 1, 1, NULL, 2, 'Test ticket #166', 'Test ticket #166', NULL, NULL, NULL),
(167, NULL, 1, 3, NULL, NULL, NULL, NULL, 4, 3, NULL, 2, NULL, 1, 1, NULL, NULL, '0.3538831866848126', '', '', '', '', '', '', 'hidden', 'deleted', NULL, 1, 3, 8, 9, 4, '2015-10-06 10:29:08', '2015-05-05 10:29:08', '2015-01-07 10:29:08', '2014-09-02 10:29:08', NULL, '2015-02-26 10:29:08', '2015-08-04 10:29:08', '2015-05-10 10:29:08', '2015-08-07 10:29:08', '2014-10-16 10:29:08', '2015-06-30 10:29:08', 4, 3, NULL, 3, 'Test ticket #167', 'Test ticket #167', NULL, NULL, NULL),
(168, NULL, 1, 1, NULL, NULL, NULL, NULL, 3, 1, NULL, 2, 2, 1, 2, NULL, 3, '0.42388213651616324', '', '', '', '', '', '', 'hidden', 'spam', NULL, 1, 2, 8, 6, 2, '2015-05-24 10:29:08', '2015-06-27 10:29:08', '2015-06-27 10:29:08', '2015-03-13 10:29:08', '2015-02-12 10:29:08', '2015-08-08 10:29:08', '2015-07-09 10:29:08', '2015-01-08 10:29:08', '2015-07-08 10:29:08', '2015-05-20 10:29:08', '2014-08-04 10:29:08', 2, 3, NULL, 1, 'Test ticket #168', 'Test ticket #168', NULL, NULL, NULL),
(169, NULL, 1, 1, NULL, NULL, NULL, NULL, 2, 1, NULL, 1, 2, 1, 3, NULL, 4, '0.4135610316075022', '', '', '', '', '', '', 'archived', 'validating', NULL, 1, 4, 10, 1, 1, '2015-08-02 10:31:24', '2014-06-27 10:31:24', '2015-05-18 10:31:24', '2014-12-01 10:31:24', '2015-06-03 10:31:24', '2015-03-18 10:31:24', '2015-05-25 10:31:24', '2015-07-31 10:31:24', '2014-07-26 10:31:24', '2014-06-05 10:31:24', '2015-05-30 10:31:24', 2, 1, NULL, 1, 'Test ticket #169', 'Test ticket #169', NULL, NULL, NULL),
(170, NULL, 1, 1, NULL, NULL, NULL, NULL, 3, 4, NULL, 2, 2, 1, 4, NULL, NULL, '0.5268171564925622', '', '', '', '', '', '', 'awaiting_user', 'spam', NULL, 1, 1, 4, 5, 2, '2014-12-09 10:31:24', '2014-09-14 10:31:24', '2015-08-31 10:31:24', '2015-09-14 10:31:24', NULL, '2014-07-04 10:31:24', '2014-12-27 10:31:24', '2015-08-24 10:31:24', '2014-10-05 10:31:24', '2015-03-15 10:31:24', '2014-08-02 10:31:24', 1, 4, NULL, 3, 'Test ticket #170', 'Test ticket #170', NULL, NULL, NULL),
(171, NULL, 1, 2, NULL, NULL, NULL, NULL, 3, 3, NULL, 4, 2, 1, 3, NULL, 4, '0.6605469599930076', '', '', '', '', '', '', 'resolved', 'deleted', NULL, 1, 4, 6, 9, 2, '2015-05-14 10:31:24', '2015-02-24 10:31:24', '2015-04-01 10:31:24', '2015-01-10 10:31:24', NULL, '2015-06-14 10:31:24', '2015-02-27 10:31:24', '2015-01-12 10:31:24', '2015-04-14 10:31:24', '2015-07-24 10:31:24', '2014-10-24 10:31:24', 1, 1, NULL, 2, 'Test ticket #171', 'Test ticket #171', NULL, NULL, NULL),
(172, NULL, 1, 2, NULL, NULL, NULL, NULL, 3, 4, NULL, 4, 1, 1, 3, NULL, NULL, '0.775181488855911', '', '', '', '', '', '', 'hidden', 'spam', NULL, 0, 1, 3, 3, 1, '2015-02-08 10:31:24', '2014-10-09 10:31:24', '2015-07-03 10:31:24', '2014-09-10 10:31:24', NULL, '2015-03-25 10:31:24', '2014-07-07 10:31:24', '2015-03-19 10:31:24', '2015-05-23 10:31:24', '2015-07-17 10:31:24', '2014-05-27 10:31:24', 2, 2, NULL, 3, 'Test ticket #172', 'Test ticket #172', NULL, NULL, NULL),
(173, NULL, 1, 2, NULL, NULL, NULL, NULL, 2, 4, NULL, 5, 1, 1, 1, NULL, 4, '0.4494348479895246', '', '', '', '', '', '', 'awaiting_user', 'validating', NULL, 1, 3, 5, 5, 3, '2014-05-27 10:31:24', '2015-09-10 10:31:24', '2015-05-16 10:31:24', '2015-05-19 10:31:24', NULL, '2014-07-22 10:31:24', '2014-09-07 10:31:24', '2015-05-08 10:31:24', '2015-07-26 10:31:24', '2014-08-21 10:31:24', '2014-10-30 10:31:24', 4, 3, NULL, 2, 'Test ticket #173', 'Test ticket #173', NULL, NULL, NULL),
(174, NULL, 1, 1, NULL, NULL, NULL, NULL, 3, 3, NULL, 4, 2, 1, 4, NULL, NULL, '0.1267726972017183', '', '', '', '', '', '', 'awaiting_agent', 'spam', NULL, 1, 4, 4, 5, 4, '2015-03-11 10:31:24', '2015-05-05 10:31:24', '2015-05-13 10:31:24', '2015-01-09 10:31:24', NULL, '2015-01-16 10:31:24', '2015-07-21 10:31:24', '2015-07-01 10:31:24', '2015-01-22 10:31:24', '2014-05-28 10:31:24', '2015-03-07 10:31:24', 1, 3, NULL, 1, 'Test ticket #174', 'Test ticket #174', NULL, NULL, NULL),
(175, NULL, 1, 1, NULL, NULL, NULL, NULL, 3, 4, NULL, 3, 1, 1, 3, NULL, NULL, '0.887962963327731', '', '', '', '', '', '', 'resolved', 'temp', NULL, 0, 4, 9, 6, 1, '2015-08-02 10:31:24', '2015-05-26 10:31:24', '2014-06-17 10:31:24', '2014-06-12 10:31:24', NULL, '2014-06-26 10:31:24', '2014-09-07 10:31:24', '2015-07-27 10:31:24', '2015-04-12 10:31:24', '2015-04-16 10:31:24', '2014-11-02 10:31:24', 2, 3, NULL, 2, 'Test ticket #175', 'Test ticket #175', NULL, NULL, NULL),
(176, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, 3, NULL, 5, 2, 1, 1, NULL, NULL, '0.2353704620482125', '', '', '', '', '', '', 'hidden', 'spam', NULL, 0, 4, 8, 9, 4, '2015-04-12 10:31:24', '2014-08-14 10:31:24', '2015-08-06 10:31:24', '2015-08-12 10:31:24', '2015-07-03 10:31:24', '2014-11-26 10:31:24', '2014-12-25 10:31:24', '2014-06-08 10:31:24', '2015-07-26 10:31:24', '2014-08-25 10:31:24', '2014-11-17 10:31:24', 4, 1, NULL, 3, 'Test ticket #176', 'Test ticket #176', NULL, NULL, NULL),
(177, NULL, 1, 3, NULL, NULL, NULL, NULL, 2, 4, NULL, 3, 2, 1, 1, NULL, NULL, '0.4341430463214806', '', '', '', '', '', '', 'awaiting_user', 'deleted', NULL, 1, 1, 2, 5, 4, '2015-06-23 10:31:24', '2015-05-18 10:31:24', '2014-09-05 10:31:24', '2015-08-07 10:31:24', NULL, '2014-09-19 10:31:24', '2015-07-22 10:31:24', '2015-02-09 10:31:24', '2014-06-24 10:31:24', '2015-05-28 10:31:24', '2015-01-31 10:31:24', 3, 1, NULL, 1, 'Test ticket #177', 'Test ticket #177', NULL, NULL, NULL),
(178, NULL, 1, 1, NULL, NULL, NULL, NULL, 2, 3, NULL, 4, 1, 1, 2, NULL, NULL, '0.6229711143513872', '', '', '', '', '', '', 'archived', 'deleted', NULL, 1, 1, 8, 9, 1, '2014-11-28 10:31:24', '2015-08-29 10:31:24', '2015-01-21 10:31:24', '2015-04-11 10:31:24', '2015-06-11 10:31:24', '2015-08-11 10:31:24', '2014-08-01 10:31:24', '2014-06-02 10:31:24', '2015-04-27 10:31:24', '2014-11-01 10:31:24', '2015-03-06 10:31:24', 1, 2, NULL, 1, 'Test ticket #178', 'Test ticket #178', NULL, NULL, NULL),
(179, NULL, 1, 2, NULL, NULL, NULL, NULL, 2, 4, NULL, 2, 1, 1, 3, NULL, NULL, '0.9043557363602852', '', '', '', '', '', '', 'hidden', 'spam', NULL, 0, 1, 9, 8, 1, '2015-06-11 10:31:24', '2014-06-07 10:31:24', '2015-07-18 10:31:24', '2014-07-17 10:31:24', NULL, '2015-06-13 10:31:24', '2015-06-20 10:31:24', '2015-03-24 10:31:24', '2015-04-29 10:31:24', '2015-03-07 10:31:24', '2015-07-08 10:31:24', 3, 3, NULL, 1, 'Test ticket #179', 'Test ticket #179', NULL, NULL, NULL),
(180, NULL, 1, 2, NULL, NULL, NULL, NULL, 3, 2, NULL, NULL, 1, 1, 3, NULL, 4, '0.9730147300036761', '', '', '', '', '', '', 'hidden', 'spam', NULL, 0, 2, 6, 5, 2, '2014-10-11 10:31:24', '2015-03-14 10:31:24', '2014-07-12 10:31:24', '2015-05-18 10:31:24', NULL, '2014-10-14 10:31:24', '2015-02-27 10:31:24', '2015-09-01 10:31:24', '2015-09-19 10:31:24', '2014-06-11 10:31:24', '2014-10-11 10:31:24', 3, 2, NULL, 3, 'Test ticket #180', 'Test ticket #180', NULL, NULL, NULL),
(181, NULL, 1, 2, NULL, NULL, NULL, NULL, 5, 3, NULL, 2, NULL, 1, 2, NULL, NULL, '0.17756314964738037', '', '', '', '', '', '', 'hidden', 'temp', NULL, 1, 4, 4, 9, 1, '2014-10-08 10:31:24', '2014-12-31 10:31:24', '2014-12-03 10:31:24', '2015-03-18 10:31:24', NULL, '2014-10-11 10:31:24', '2015-09-12 10:31:24', '2015-08-23 10:31:24', '2015-05-09 10:31:24', '2015-06-09 10:31:24', '2015-05-12 10:31:24', 4, 4, NULL, 2, 'Test ticket #181', 'Test ticket #181', NULL, NULL, NULL),
(182, NULL, 1, 3, NULL, NULL, NULL, NULL, 3, 2, NULL, 2, 2, 1, 4, NULL, 3, '0.09747704872626536', '', '', '', '', '', '', 'awaiting_user', 'deleted', NULL, 1, 4, 9, 6, 1, '2014-10-09 10:31:24', '2015-03-07 10:31:24', '2014-06-13 10:31:24', '2015-01-16 10:31:24', NULL, '2015-07-01 10:31:24', '2014-10-13 10:31:24', '2015-10-06 10:31:24', '2014-08-04 10:31:24', '2015-05-15 10:31:24', '2014-07-25 10:31:24', 3, 4, NULL, 1, 'Test ticket #182', 'Test ticket #182', NULL, NULL, NULL),
(183, 114, 1, 2, NULL, NULL, NULL, NULL, 4, 1, NULL, 4, 1, 1, 2, NULL, NULL, '0.23850262001017353', '', '', '', '', '', '', 'hidden', 'spam', NULL, 1, 4, 3, 1, 3, '2014-09-19 10:31:24', '2015-09-20 10:31:24', '2014-07-29 10:31:24', '2015-06-01 10:31:24', NULL, '2014-12-25 10:31:24', '2014-08-03 10:31:24', '2014-12-19 10:31:24', '2015-04-16 10:31:24', '2014-05-30 10:31:24', '2014-07-10 10:31:24', 3, 1, NULL, 1, 'Test ticket #183', 'Test ticket #183', NULL, NULL, NULL),
(184, NULL, 1, 3, NULL, NULL, NULL, NULL, 3, 2, NULL, 4, 2, 1, 1, NULL, 1, '0.29457166818396346', '', '', '', '', '', '', 'hidden', 'spam', NULL, 1, 1, 1, 1, 1, '2015-09-06 10:31:24', '2015-06-29 10:31:24', '2014-08-25 10:31:24', '2015-02-08 10:31:24', NULL, '2015-03-23 10:31:24', '2015-08-28 10:31:24', '2015-06-23 10:31:24', '2014-08-22 10:31:24', '2015-02-09 10:31:24', '2014-06-27 10:31:24', 1, 2, NULL, 1, 'Test ticket #184', 'Test ticket #184', NULL, NULL, NULL),
(185, NULL, 1, 2, NULL, NULL, NULL, NULL, 3, 2, NULL, 3, 2, 1, 3, NULL, 3, '0.9917736556304373', '', '', '', '', '', '', 'awaiting_agent', 'spam', NULL, 1, 1, 9, 5, 3, '2015-04-09 10:31:24', '2014-12-09 10:31:24', '2014-06-26 10:31:24', '2014-07-27 10:31:24', NULL, '2015-06-25 10:31:24', '2015-04-17 10:31:24', '2015-08-12 10:31:24', '2015-01-17 10:31:24', '2015-05-11 10:31:24', '2014-07-06 10:31:24', 3, 3, NULL, 2, 'Test ticket #185', 'Test ticket #185', NULL, NULL, NULL),
(186, NULL, 1, 3, NULL, NULL, NULL, NULL, 5, 3, NULL, 1, 1, 1, 4, NULL, NULL, '0.2981541355123316', '', '', '', '', '', '', 'resolved', 'temp', NULL, 1, 1, 8, 3, 1, '2015-05-10 10:31:24', '2015-03-10 10:31:24', '2015-06-21 10:31:24', '2014-08-26 10:31:24', '2015-03-09 10:31:24', '2014-10-29 10:31:24', '2015-07-18 10:31:24', '2014-09-28 10:31:24', '2015-05-28 10:31:24', '2015-08-29 10:31:24', '2014-12-09 10:31:24', 4, 1, NULL, 1, 'Test ticket #186', 'Test ticket #186', NULL, NULL, NULL),
(187, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, 1, NULL, 3, NULL, 1, 1, NULL, 4, '0.8694979742816631', '', '', '', '', '', '', 'awaiting_user', 'spam', NULL, 1, 3, 2, 4, 4, '2014-06-06 10:31:24', '2014-06-20 10:31:24', '2014-08-25 10:31:24', '2015-06-10 10:31:24', NULL, '2015-10-02 10:31:24', '2014-08-24 10:31:24', '2015-09-04 10:31:24', '2014-07-27 10:31:24', '2015-07-09 10:31:24', '2015-05-22 10:31:24', 4, 2, NULL, 2, 'Test ticket #187', 'Test ticket #187', NULL, NULL, NULL),
(188, NULL, 1, 2, NULL, NULL, NULL, NULL, 2, 2, NULL, 1, NULL, 1, 3, NULL, 2, '0.525136149046138', '', '', '', '', '', '', 'hidden', 'spam', NULL, 1, 4, 5, 8, 3, '2015-04-24 10:31:24', '2015-08-11 10:31:24', '2014-12-25 10:31:24', '2015-01-17 10:31:24', '2014-07-08 10:31:24', '2014-05-29 10:31:24', '2015-06-18 10:31:24', '2015-07-29 10:31:24', '2015-09-17 10:31:24', '2014-09-14 10:31:24', '2014-09-17 10:31:24', 3, 2, NULL, 3, 'Test ticket #188', 'Test ticket #188', NULL, NULL, NULL),
(189, NULL, 1, 3, NULL, NULL, NULL, NULL, 1, 2, NULL, NULL, NULL, 1, 4, NULL, NULL, '0.7580017808433639', '', '', '', '', '', '', 'awaiting_user', 'temp', NULL, 1, 2, 7, 5, 1, '2015-09-17 10:31:24', '2015-09-30 10:31:24', '2014-06-17 10:31:24', '2014-10-06 10:31:24', NULL, '2014-08-24 10:31:24', '2014-10-31 10:31:24', '2014-06-13 10:31:24', '2014-09-18 10:31:24', '2014-06-18 10:31:24', '2015-02-17 10:31:24', 2, 4, NULL, 1, 'Test ticket #189', 'Test ticket #189', NULL, NULL, NULL),
(190, NULL, 1, 2, NULL, NULL, NULL, NULL, 1, 4, NULL, 2, 1, 1, 4, NULL, NULL, '0.38816363866269926', '', '', '', '', '', '', 'archived', 'deleted', NULL, 1, 2, 9, 3, 4, '2015-04-27 10:31:24', '2015-06-27 10:31:24', '2015-09-11 10:31:24', '2014-11-17 10:31:24', '2015-08-25 10:31:24', '2015-02-02 10:31:24', '2015-06-24 10:31:24', '2014-12-26 10:31:24', '2015-06-17 10:31:24', '2015-03-13 10:31:24', '2015-03-16 10:31:24', 4, 4, NULL, 2, 'Test ticket #190', 'Test ticket #190', NULL, NULL, NULL),
(191, NULL, 1, 2, NULL, NULL, NULL, NULL, 1, 4, NULL, NULL, 1, 1, 4, NULL, NULL, '0.8954896944533006', '', '', '', '', '', '', 'awaiting_user', 'temp', NULL, 1, 3, 7, 6, 1, '2014-12-18 10:31:24', '2014-10-11 10:31:24', '2014-08-05 10:31:24', '2015-08-11 10:31:24', NULL, '2014-10-24 10:31:24', '2015-02-17 10:31:24', '2015-06-13 10:31:24', '2014-09-16 10:31:24', '2015-07-13 10:31:24', '2015-01-10 10:31:24', 1, 2, NULL, 1, 'Test ticket #191', 'Test ticket #191', NULL, NULL, NULL),
(192, NULL, 1, 1, NULL, NULL, NULL, NULL, 3, 4, NULL, 2, 2, 1, 2, NULL, NULL, '0.886650851822133', '', '', '', '', '', '', 'archived', 'deleted', NULL, 1, 1, 3, 8, 1, '2014-11-07 10:31:24', '2014-09-17 10:31:24', '2014-08-10 10:31:24', '2014-07-02 10:31:24', NULL, '2014-11-07 10:31:24', '2015-08-27 10:31:24', '2015-03-19 10:31:24', '2014-09-13 10:31:24', '2014-11-01 10:31:24', '2015-09-01 10:31:24', 2, 2, NULL, 1, 'Test ticket #192', 'Test ticket #192', NULL, NULL, NULL),
(193, NULL, 1, 1, NULL, NULL, NULL, NULL, 4, 2, NULL, 1, 2, 1, 2, NULL, 3, '0.25033771642515223', '', '', '', '', '', '', 'awaiting_user', 'deleted', NULL, 1, 3, 6, 9, 2, '2014-06-20 10:31:24', '2014-06-29 10:31:24', '2014-08-31 10:31:24', '2015-06-09 10:31:24', NULL, '2015-06-19 10:31:24', '2014-10-01 10:31:24', '2015-09-13 10:31:24', '2015-09-26 10:31:24', '2014-06-12 10:31:24', '2014-09-22 10:31:24', 4, 2, NULL, 3, 'Test ticket #193', 'Test ticket #193', NULL, NULL, NULL),
(194, NULL, 1, 2, NULL, NULL, NULL, NULL, 3, 1, NULL, 3, NULL, 1, 2, NULL, NULL, '0.033636199341748094', '', '', '', '', '', '', 'resolved', 'spam', NULL, 1, 2, 8, 6, 1, '2015-02-25 10:31:24', '2014-11-15 10:31:24', '2014-07-07 10:31:24', '2014-11-29 10:31:24', NULL, '2015-10-03 10:31:24', '2014-07-03 10:31:24', '2014-12-17 10:31:24', '2015-07-13 10:31:24', '2015-08-18 10:31:24', '2014-05-30 10:31:24', 3, 2, NULL, 2, 'Test ticket #194', 'Test ticket #194', NULL, NULL, NULL),
(195, NULL, 1, 3, NULL, NULL, NULL, NULL, 1, 4, NULL, 2, 2, 1, 2, NULL, NULL, '0.5914361817682499', '', '', '', '', '', '', 'awaiting_agent', 'deleted', NULL, 1, 1, 4, 5, 4, '2015-03-18 10:31:24', '2015-05-29 10:31:24', '2015-08-22 10:31:24', '2014-11-03 10:31:24', NULL, '2015-01-24 10:31:24', '2015-06-10 10:31:24', '2014-11-12 10:31:24', '2014-12-19 10:31:24', '2014-06-23 10:31:24', '2014-06-10 10:31:24', 1, 1, NULL, 3, 'Test ticket #195', 'Test ticket #195', NULL, NULL, NULL),
(196, 46, 1, 2, NULL, NULL, NULL, NULL, 3, 2, NULL, 1, 1, 1, 2, NULL, 1, '0.12398564733936046', '', '', '', '', '', '', 'hidden', 'deleted', NULL, 0, 3, 5, 5, 1, '2015-08-20 10:31:24', '2015-06-29 10:31:24', '2014-10-16 10:31:24', '2014-06-14 10:31:24', NULL, '2015-02-07 10:31:24', '2015-03-20 10:31:24', '2014-12-28 10:31:24', '2014-11-28 10:31:24', '2015-03-03 10:31:24', '2015-05-09 10:31:24', 1, 1, NULL, 1, 'Test ticket #196', 'Test ticket #196', NULL, NULL, NULL),
(197, NULL, 1, 3, NULL, NULL, NULL, NULL, 4, 3, NULL, NULL, NULL, 1, 2, NULL, 2, '0.44120978698247093', '', '', '', '', '', '', 'awaiting_agent', 'validating', NULL, 0, 1, 7, 8, 4, '2014-12-19 10:31:24', '2015-10-01 10:31:24', '2015-05-04 10:31:24', '2015-01-18 10:31:24', NULL, '2014-07-28 10:31:24', '2015-05-10 10:31:24', '2014-07-22 10:31:24', '2015-01-19 10:31:24', '2014-06-12 10:31:24', '2015-05-31 10:31:24', 2, 1, NULL, 1, 'Test ticket #197', 'Test ticket #197', NULL, NULL, NULL),
(198, NULL, 1, 2, NULL, NULL, NULL, NULL, 3, 2, NULL, NULL, 1, 1, 4, NULL, NULL, '0.028271395739420686', '', '', '', '', '', '', 'awaiting_agent', 'spam', NULL, 1, 4, 3, 6, 4, '2014-10-22 10:31:24', '2014-07-15 10:31:24', '2015-03-25 10:31:24', '2015-05-24 10:31:24', NULL, '2015-08-10 10:31:24', '2015-09-25 10:31:24', '2014-09-13 10:31:24', '2014-08-22 10:31:24', '2014-09-12 10:31:24', '2015-03-03 10:31:24', 4, 4, NULL, 3, 'Test ticket #198', 'Test ticket #198', NULL, NULL, NULL),
(199, NULL, 1, 1, NULL, NULL, NULL, NULL, 4, 1, NULL, 1, NULL, 1, 3, NULL, NULL, '0.30409851419189804', '', '', '', '', '', '', 'hidden', 'temp', NULL, 1, 4, 6, 9, 3, '2014-10-17 10:31:24', '2014-12-18 10:31:24', '2014-08-30 10:31:24', '2015-05-25 10:31:24', NULL, '2014-07-31 10:31:24', '2015-01-15 10:31:24', '2015-09-09 10:31:24', '2014-10-24 10:31:24', '2015-05-06 10:31:24', '2015-02-19 10:31:24', 2, 3, NULL, 2, 'Test ticket #199', 'Test ticket #199', NULL, NULL, NULL),
(200, NULL, 1, 2, NULL, NULL, NULL, NULL, NULL, 3, NULL, 4, 2, 1, 1, NULL, 4, '0.5282759596857018', '', '', '', '', '', '', 'awaiting_user', 'spam', NULL, 1, 4, 9, 10, 2, '2015-03-08 10:31:24', '2015-04-16 10:31:24', '2015-02-15 10:31:24', '2015-05-11 10:31:24', NULL, '2015-01-11 10:31:24', '2015-03-02 10:31:24', '2014-12-23 10:31:24', '2014-12-28 10:31:24', '2015-08-12 10:31:24', '2014-08-02 10:31:24', 4, 2, NULL, 2, 'Test ticket #200', 'Test ticket #200', NULL, NULL, NULL),
(201, NULL, 1, 1, NULL, NULL, NULL, NULL, 5, 4, NULL, 1, 2, 1, 1, NULL, NULL, '0.9885395234343964', '', '', '', '', '', '', 'awaiting_agent', 'deleted', NULL, 1, 3, 4, 6, 4, '2015-10-05 10:31:24', '2015-07-17 10:31:24', '2014-08-25 10:31:24', '2014-12-16 10:31:24', NULL, '2014-09-22 10:31:24', '2015-05-17 10:31:24', '2015-07-23 10:31:24', '2014-07-09 10:31:24', '2015-08-23 10:31:24', '2014-10-07 10:31:24', 2, 3, NULL, 2, 'Test ticket #201', 'Test ticket #201', NULL, NULL, NULL),
(202, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, 2, NULL, 3, 1, 1, 2, NULL, NULL, '0.49910662649116166', '', '', '', '', '', '', 'hidden', 'spam', NULL, 1, 4, 6, 2, 1, '2015-09-02 10:31:24', '2015-08-27 10:31:24', '2015-06-27 10:31:24', '2014-09-13 10:31:24', NULL, '2015-09-05 10:31:24', '2015-02-05 10:31:24', '2015-06-04 10:31:24', '2014-09-06 10:31:24', '2015-06-25 10:31:24', '2014-11-06 10:31:24', 3, 3, NULL, 1, 'Test ticket #202', 'Test ticket #202', NULL, NULL, NULL),
(203, NULL, 1, 2, NULL, NULL, NULL, NULL, 4, 3, NULL, 4, 1, 1, 2, NULL, NULL, '0.6436205279488307', '', '', '', '', '', '', 'awaiting_agent', 'deleted', NULL, 1, 2, 10, 10, 4, '2015-02-02 10:31:24', '2014-08-01 10:31:24', '2014-08-16 10:31:24', '2014-12-17 10:31:24', NULL, '2015-03-08 10:31:24', '2014-08-23 10:31:24', '2014-08-22 10:31:24', '2014-11-12 10:31:24', '2014-08-20 10:31:24', '2015-07-20 10:31:24', 2, 1, NULL, 2, 'Test ticket #203', 'Test ticket #203', NULL, NULL, NULL),
(204, 63, 1, 2, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 1, 1, 4, NULL, 3, '0.3497798362279123', '', '', '', '', '', '', 'resolved', 'deleted', NULL, 1, 4, 5, 8, 3, '2014-09-08 10:31:24', '2015-09-15 10:31:24', '2014-08-05 10:31:24', '2015-07-21 10:31:24', '2015-06-20 10:31:24', '2014-12-01 10:31:24', '2015-02-24 10:31:24', '2015-03-23 10:31:24', '2014-11-25 10:31:24', '2014-06-05 10:31:24', '2014-05-28 10:31:24', 1, 1, NULL, 3, 'Test ticket #204', 'Test ticket #204', NULL, NULL, NULL),
(205, NULL, 1, 3, NULL, NULL, NULL, NULL, 4, 1, NULL, 1, 2, 1, 1, NULL, NULL, '0.3456754194066631', '', '', '', '', '', '', 'hidden', 'temp', NULL, 1, 2, 8, 7, 1, '2015-05-15 10:31:24', '2015-05-25 10:31:24', '2015-02-06 10:31:24', '2014-12-02 10:31:24', NULL, '2015-04-14 10:31:24', '2014-08-14 10:31:24', '2015-07-30 10:31:24', '2015-07-08 10:31:24', '2015-02-03 10:31:24', '2014-07-02 10:31:24', 1, 4, NULL, 1, 'Test ticket #205', 'Test ticket #205', NULL, NULL, NULL),
(206, NULL, 1, 2, NULL, NULL, NULL, NULL, 1, 2, NULL, NULL, 1, 1, 2, NULL, NULL, '0.49527272721312265', '', '', '', '', '', '', 'archived', 'validating', NULL, 1, 1, 5, 9, 4, '2014-12-27 10:31:24', '2015-03-15 10:31:24', '2015-04-08 10:31:24', '2014-12-17 10:31:24', NULL, '2015-02-07 10:31:24', '2014-07-28 10:31:24', '2014-07-07 10:31:24', '2014-06-17 10:31:24', '2015-09-20 10:31:24', '2015-05-04 10:31:24', 2, 2, NULL, 2, 'Test ticket #206', 'Test ticket #206', NULL, NULL, NULL),
(207, 110, 1, 3, NULL, NULL, NULL, NULL, 3, 2, NULL, 5, NULL, 1, 3, NULL, NULL, '0.27663065705134593', '', '', '', '', '', '', 'awaiting_user', 'validating', NULL, 1, 4, 4, 3, 4, '2015-05-30 10:31:24', '2015-04-04 10:31:24', '2015-08-27 10:31:24', '2015-05-09 10:31:24', NULL, '2015-03-20 10:31:24', '2015-06-12 10:31:24', '2014-06-12 10:31:24', '2015-08-05 10:31:24', '2014-10-03 10:31:24', '2015-05-03 10:31:24', 2, 4, NULL, 1, 'Test ticket #207', 'Test ticket #207', NULL, NULL, NULL),
(208, NULL, 1, 2, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, 1, 1, 1, NULL, NULL, '0.26090016333470134', '', '', '', '', '', '', 'hidden', 'temp', NULL, 0, 3, 5, 3, 4, '2015-06-15 10:31:24', '2015-07-09 10:31:24', '2015-06-22 10:31:24', '2015-01-11 10:31:24', NULL, '2014-11-19 10:31:24', '2015-09-04 10:31:24', '2015-03-19 10:31:24', '2014-08-20 10:31:24', '2014-07-06 10:31:24', '2015-08-13 10:31:24', 4, 3, NULL, 1, 'Test ticket #208', 'Test ticket #208', NULL, NULL, NULL),
(209, NULL, 1, 1, NULL, NULL, NULL, NULL, 1, 4, NULL, 2, 1, 1, 2, NULL, NULL, '0.18664899858334008', '', '', '', '', '', '', 'resolved', 'temp', NULL, 1, 3, 7, 3, 2, '2014-05-31 10:31:24', '2014-07-07 10:31:24', '2014-12-05 10:31:24', '2015-05-02 10:31:24', NULL, '2014-08-29 10:31:24', '2014-09-07 10:31:24', '2015-01-15 10:31:24', '2015-05-20 10:31:24', '2014-08-27 10:31:24', '2015-06-18 10:31:24', 3, 3, NULL, 2, 'Test ticket #209', 'Test ticket #209', NULL, NULL, NULL),
(210, NULL, 1, 3, NULL, NULL, NULL, NULL, 1, 2, NULL, 4, 2, 1, 2, NULL, 2, '0.3040499242991674', '', '', '', '', '', '', 'awaiting_user', 'spam', NULL, 1, 2, 8, 10, 3, '2015-02-21 10:31:24', '2015-06-19 10:31:24', '2014-10-03 10:31:24', '2015-09-23 10:31:24', NULL, '2014-12-30 10:31:24', '2015-09-23 10:31:24', '2015-02-19 10:31:24', '2015-06-21 10:31:24', '2014-10-19 10:31:24', '2014-07-21 10:31:24', 2, 4, NULL, 2, 'Test ticket #210', 'Test ticket #210', NULL, NULL, NULL),
(211, NULL, 1, 2, NULL, NULL, NULL, NULL, 3, 3, NULL, 5, 2, 1, 4, NULL, NULL, '0.9915088545451954', '', '', '', '', '', '', 'archived', 'spam', NULL, 0, 1, 9, 4, 1, '2015-03-02 10:31:24', '2014-07-03 10:31:24', '2015-05-09 10:31:24', '2014-09-28 10:31:24', NULL, '2014-06-28 10:31:24', '2015-07-18 10:31:24', '2015-10-01 10:31:24', '2014-12-20 10:31:24', '2014-07-27 10:31:24', '2014-11-28 10:31:24', 3, 3, NULL, 3, 'Test ticket #211', 'Test ticket #211', NULL, NULL, NULL),
(212, NULL, 1, 2, NULL, NULL, NULL, NULL, 2, 1, NULL, 1, 1, 1, 3, NULL, NULL, '0.4401322346554438', '', '', '', '', '', '', 'hidden', 'validating', NULL, 1, 2, 5, 4, 2, '2015-03-06 10:31:24', '2015-05-13 10:31:24', '2015-07-07 10:31:24', '2015-09-17 10:31:24', '2014-11-14 10:31:24', '2015-07-25 10:31:24', '2014-09-10 10:31:24', '2015-02-08 10:31:24', '2015-09-08 10:31:24', '2014-08-09 10:31:24', '2015-09-02 10:31:24', 4, 4, NULL, 3, 'Test ticket #212', 'Test ticket #212', NULL, NULL, NULL),
(213, 38, 1, 2, NULL, NULL, NULL, NULL, 4, 3, NULL, NULL, NULL, 1, 3, NULL, NULL, '0.7811525136056845', '', '', '', '', '', '', 'awaiting_agent', 'validating', NULL, 1, 2, 2, 6, 1, '2015-03-02 10:31:24', '2014-12-09 10:31:24', '2014-10-17 10:31:24', '2014-10-02 10:31:24', NULL, '2014-11-09 10:31:24', '2014-12-13 10:31:24', '2014-05-31 10:31:24', '2015-07-23 10:31:24', '2014-09-02 10:31:24', '2015-01-04 10:31:24', 2, 1, NULL, 3, 'Test ticket #213', 'Test ticket #213', NULL, NULL, NULL),
(214, NULL, 1, 3, NULL, NULL, NULL, NULL, 4, 4, NULL, 3, 1, 1, 3, NULL, NULL, '0.6210760163339563', '', '', '', '', '', '', 'resolved', 'spam', NULL, 1, 2, 5, 9, 1, '2015-08-14 10:31:24', '2015-08-05 10:31:24', '2015-05-08 10:31:24', '2015-07-27 10:31:24', NULL, '2014-12-02 10:31:24', '2014-11-08 10:31:24', '2015-02-11 10:31:24', '2015-03-28 10:31:24', '2015-01-27 10:31:24', '2015-04-03 10:31:24', 2, 3, NULL, 1, 'Test ticket #214', 'Test ticket #214', NULL, NULL, NULL),
(215, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 3, NULL, 1, 1, NULL, 3, '0.930685649561403', '', '', '', '', '', '', 'archived', 'temp', NULL, 0, 1, 3, 9, 3, '2015-06-28 10:31:24', '2015-04-22 10:31:24', '2015-08-31 10:31:24', '2015-04-04 10:31:24', '2014-11-24 10:31:24', '2015-09-07 10:31:24', '2015-03-21 10:31:24', '2014-08-19 10:31:24', '2014-06-26 10:31:24', '2015-06-28 10:31:24', '2015-06-27 10:31:24', 2, 2, NULL, 1, 'Test ticket #215', 'Test ticket #215', NULL, NULL, NULL),
(216, NULL, 1, 1, NULL, NULL, NULL, NULL, 3, 4, NULL, 5, 2, 1, 1, NULL, NULL, '0.8013042265561449', '', '', '', '', '', '', 'hidden', 'validating', NULL, 1, 4, 4, 9, 1, '2014-12-06 10:31:24', '2015-09-28 10:31:24', '2015-05-26 10:31:24', '2015-05-15 10:31:24', '2014-11-18 10:31:24', '2015-04-09 10:31:24', '2014-07-26 10:31:24', '2015-05-09 10:31:24', '2014-07-25 10:31:24', '2015-02-05 10:31:24', '2014-08-27 10:31:24', 3, 3, NULL, 1, 'Test ticket #216', 'Test ticket #216', NULL, NULL, NULL),
(217, NULL, 1, 2, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, 1, NULL, NULL, '0.1798451227879572', '', '', '', '', '', '', 'archived', 'spam', NULL, 0, 3, 8, 2, 2, '2015-01-04 10:31:24', '2014-11-30 10:31:24', '2015-02-20 10:31:24', '2015-03-07 10:31:24', NULL, '2015-01-07 10:31:24', '2015-03-03 10:31:24', '2015-01-07 10:31:24', '2015-03-09 10:31:24', '2015-02-07 10:31:24', '2015-07-23 10:31:24', 2, 1, NULL, 2, 'Test ticket #217', 'Test ticket #217', NULL, NULL, NULL),
(218, NULL, 1, 1, NULL, NULL, NULL, NULL, 1, 1, NULL, 4, 2, 1, 1, NULL, NULL, '0.6844809396979221', '', '', '', '', '', '', 'resolved', 'validating', NULL, 1, 4, 10, 3, 3, '2014-07-07 10:31:24', '2014-06-20 10:31:24', '2015-10-04 10:31:24', '2015-07-08 10:31:24', NULL, '2014-06-20 10:31:24', '2015-09-14 10:31:24', '2015-03-25 10:31:24', '2014-08-23 10:31:24', '2014-06-29 10:31:24', '2015-07-03 10:31:24', 1, 2, NULL, 3, 'Test ticket #218', 'Test ticket #218', NULL, NULL, NULL),
(219, NULL, 1, 2, NULL, NULL, NULL, NULL, 1, 2, NULL, 2, NULL, 1, 3, NULL, 2, '0.5469153929007383', '', '', '', '', '', '', 'hidden', 'spam', NULL, 1, 1, 10, 9, 1, '2015-03-24 10:31:24', '2015-02-12 10:31:24', '2015-07-01 10:31:24', '2014-12-30 10:31:24', '2015-06-19 10:31:24', '2015-03-11 10:31:24', '2015-03-01 10:31:24', '2014-06-23 10:31:24', '2015-03-25 10:31:24', '2015-07-29 10:31:24', '2015-01-16 10:31:24', 1, 3, NULL, 3, 'Test ticket #219', 'Test ticket #219', NULL, NULL, NULL),
(220, NULL, 1, 1, NULL, NULL, NULL, NULL, 2, 2, NULL, 5, 1, 1, 2, NULL, NULL, '0.11610071558142147', '', '', '', '', '', '', 'awaiting_user', 'validating', NULL, 1, 2, 3, 3, 2, '2015-03-14 10:31:24', '2014-08-04 10:31:24', '2015-09-13 10:31:24', '2014-11-05 10:31:24', NULL, '2015-08-31 10:31:24', '2014-10-12 10:31:24', '2015-03-31 10:31:24', '2014-09-29 10:31:24', '2014-12-15 10:31:24', '2014-10-08 10:31:24', 4, 1, NULL, 1, 'Test ticket #220', 'Test ticket #220', NULL, NULL, NULL),
(221, 71, 1, 3, NULL, NULL, NULL, NULL, NULL, 2, NULL, 5, 2, 1, 1, NULL, NULL, '0.8338305957930447', '', '', '', '', '', '', 'hidden', 'validating', NULL, 1, 3, 9, 3, 4, '2015-08-16 10:31:24', '2015-06-29 10:31:24', '2014-10-28 10:31:24', '2014-08-10 10:31:24', '2015-07-15 10:31:24', '2015-05-09 10:31:24', '2015-09-30 10:31:24', '2015-07-16 10:31:24', '2014-09-08 10:31:24', '2015-02-25 10:31:24', '2014-07-25 10:31:24', 1, 3, NULL, 3, 'Test ticket #221', 'Test ticket #221', NULL, NULL, NULL),
(222, NULL, 1, 3, NULL, NULL, NULL, NULL, 1, 1, NULL, 2, 1, 1, 3, NULL, NULL, '0.7939378328564929', '', '', '', '', '', '', 'awaiting_agent', 'spam', NULL, 1, 4, 3, 4, 2, '2015-01-18 10:31:24', '2014-12-04 10:31:24', '2015-02-01 10:31:24', '2014-11-18 10:31:24', NULL, '2014-09-06 10:31:24', '2014-10-08 10:31:24', '2015-05-26 10:31:24', '2015-07-21 10:31:24', '2014-06-04 10:31:24', '2015-03-03 10:31:24', 1, 4, NULL, 3, 'Test ticket #222', 'Test ticket #222', NULL, NULL, NULL),
(223, NULL, 1, 1, NULL, NULL, NULL, NULL, 3, 2, NULL, 3, 2, 1, 3, NULL, NULL, '0.6409529704981977', '', '', '', '', '', '', 'hidden', 'validating', NULL, 1, 3, 1, 2, 2, '2015-08-30 10:31:24', '2014-06-20 10:31:24', '2015-01-24 10:31:24', '2014-10-13 10:31:24', NULL, '2015-07-27 10:31:24', '2015-01-09 10:31:24', '2015-05-18 10:31:24', '2014-09-04 10:31:24', '2015-08-01 10:31:24', '2015-05-20 10:31:24', 1, 1, NULL, 3, 'Test ticket #223', 'Test ticket #223', NULL, NULL, NULL),
(224, NULL, 1, 1, NULL, NULL, NULL, NULL, 1, 1, NULL, 3, 2, 1, 4, NULL, NULL, '0.33312051029235173', '', '', '', '', '', '', 'awaiting_user', 'spam', NULL, 0, 3, 1, 2, 3, '2014-08-31 10:31:24', '2015-09-23 10:31:24', '2014-10-07 10:31:24', '2014-12-27 10:31:24', NULL, '2014-12-29 10:31:24', '2014-08-01 10:31:24', '2014-11-27 10:31:24', '2015-01-05 10:31:24', '2014-07-27 10:31:24', '2014-10-10 10:31:24', 4, 4, NULL, 1, 'Test ticket #224', 'Test ticket #224', NULL, NULL, NULL),
(225, 84, 1, 3, NULL, NULL, NULL, NULL, 1, 1, NULL, 3, 2, 1, 4, NULL, NULL, '0.7732364179317247', '', '', '', '', '', '', 'awaiting_user', 'temp', NULL, 0, 1, 10, 1, 3, '2015-03-17 10:31:24', '2015-02-01 10:31:24', '2015-05-31 10:31:24', '2014-08-31 10:31:24', NULL, '2014-08-12 10:31:24', '2015-02-16 10:31:24', '2014-08-31 10:31:24', '2014-11-27 10:31:24', '2014-10-09 10:31:24', '2014-09-28 10:31:24', 3, 3, NULL, 1, 'Test ticket #225', 'Test ticket #225', NULL, NULL, NULL),
(226, NULL, 1, 2, NULL, NULL, NULL, NULL, NULL, 2, NULL, 5, 2, 1, 2, NULL, 1, '0.637565692549167', '', '', '', '', '', '', 'archived', 'deleted', NULL, 1, 1, 1, 9, 1, '2014-08-01 10:31:24', '2015-08-22 10:31:24', '2014-07-30 10:31:24', '2015-09-05 10:31:24', NULL, '2015-03-10 10:31:24', '2014-06-19 10:31:24', '2015-02-07 10:31:24', '2014-12-25 10:31:24', '2015-03-15 10:31:24', '2015-04-18 10:31:24', 2, 2, NULL, 2, 'Test ticket #226', 'Test ticket #226', NULL, NULL, NULL),
(227, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, 1, 4, NULL, 1, '0.7698439767303354', '', '', '', '', '', '', 'archived', 'deleted', NULL, 1, 3, 9, 2, 1, '2015-04-04 10:31:24', '2015-04-14 10:31:24', '2014-11-17 10:31:24', '2015-07-06 10:31:24', NULL, '2015-03-26 10:31:24', '2014-06-01 10:31:24', '2014-09-21 10:31:24', '2014-08-05 10:31:24', '2015-10-07 10:31:24', '2015-03-05 10:31:24', 1, 3, NULL, 1, 'Test ticket #227', 'Test ticket #227', NULL, NULL, NULL),
(228, NULL, 1, 2, NULL, NULL, NULL, NULL, NULL, 3, NULL, 5, NULL, 1, 2, NULL, NULL, '0.2424106488399307', '', '', '', '', '', '', 'awaiting_agent', 'spam', NULL, 0, 1, 2, 8, 1, '2015-08-13 10:31:24', '2015-03-31 10:31:24', '2014-12-27 10:31:24', '2014-10-19 10:31:24', NULL, '2015-09-25 10:31:24', '2014-11-23 10:31:24', '2015-08-11 10:31:24', '2014-11-07 10:31:24', '2015-10-08 10:31:24', '2014-05-29 10:31:24', 4, 4, NULL, 2, 'Test ticket #228', 'Test ticket #228', NULL, NULL, NULL);

INSERT INTO `tickets`
(`id`, `parent_ticket_id`, `language_id`, `department_id`, `category_id`, `priority_id`, `workflow_id`, `product_id`, `person_id`, `person_email_id`, `person_email_validating_id`, `agent_id`, `agent_team_id`, `organization_id`, `linked_chat_id`, `email_account_id`, `locked_by_agent`, `ref`, `auth`, `sent_to_address`, `email_account_address`, `creation_system`, `creation_system_option`, `ticket_hash`, `status`, `hidden_status`, `validating`, `is_hold`, `urgency`, `count_agent_replies`, `count_user_replies`, `feedback_rating`, `date_feedback_rating`, `date_created`, `date_resolved`, `date_archived`, `date_first_agent_assign`, `date_first_agent_reply`, `date_last_agent_reply`, `date_last_user_reply`, `date_agent_waiting`, `date_user_waiting`, `date_status`, `total_user_waiting`, `total_to_first_reply`, `date_locked`, `has_attachments`, `subject`, `original_subject`, `properties`, `worst_sla_status`, `waiting_times`)

VALUES
(229, NULL, 1, 2, NULL, NULL, NULL, NULL, 1, 2, NULL, 2, NULL, 1, 4, NULL, NULL, '0.07985247120247453', '', '', '', '', '', '', 'awaiting_user', 'spam', NULL, 1, 4, 5, 2, 1, '2015-10-06 10:31:24', '2014-08-15 10:31:24', '2015-07-12 10:31:24', '2015-04-06 10:31:24', NULL, '2015-01-26 10:31:24', '2014-12-24 10:31:24', '2015-04-16 10:31:24', '2015-09-24 10:31:24', '2015-08-27 10:31:24', '2015-04-19 10:31:24', 2, 2, NULL, 1, 'Test ticket #229', 'Test ticket #229', NULL, NULL, NULL),
(230, NULL, 1, 3, NULL, NULL, NULL, NULL, 2, 3, NULL, 2, 1, 1, 3, NULL, NULL, '0.7500056547578476', '', '', '', '', '', '', 'resolved', 'temp', NULL, 1, 1, 4, 7, 2, '2015-07-12 10:31:24', '2015-01-19 10:31:24', '2015-08-21 10:31:24', '2014-07-12 10:31:24', NULL, '2015-02-01 10:31:24', '2014-09-20 10:31:24', '2015-04-24 10:31:24', '2015-04-03 10:31:24', '2014-07-24 10:31:24', '2015-05-23 10:31:24', 3, 4, NULL, 1, 'Test ticket #230', 'Test ticket #230', NULL, NULL, NULL),
(231, NULL, 1, 3, NULL, NULL, NULL, NULL, 3, 3, NULL, 1, 2, 1, 4, NULL, NULL, '0.495251152194339', '', '', '', '', '', '', 'archived', 'temp', NULL, 1, 1, 1, 9, 1, '2014-12-09 10:31:24', '2015-10-01 10:31:24', '2015-06-05 10:31:24', '2015-06-24 10:31:24', '2015-05-06 10:31:24', '2014-07-07 10:31:24', '2014-11-17 10:31:24', '2015-01-30 10:31:24', '2014-12-29 10:31:24', '2015-05-01 10:31:24', '2014-07-12 10:31:24', 3, 1, NULL, 3, 'Test ticket #231', 'Test ticket #231', NULL, NULL, NULL),
(232, NULL, 1, 2, NULL, NULL, NULL, NULL, 3, 4, NULL, 3, 1, 1, 2, NULL, 1, '0.6894772161631633', '', '', '', '', '', '', 'hidden', 'validating', NULL, 0, 2, 3, 3, 3, '2015-09-11 10:31:24', '2015-01-05 10:31:24', '2014-12-09 10:31:24', '2015-04-07 10:31:24', NULL, '2015-09-19 10:31:24', '2015-08-17 10:31:24', '2015-03-20 10:31:24', '2014-10-18 10:31:24', '2015-04-21 10:31:24', '2014-12-27 10:31:24', 4, 2, NULL, 3, 'Test ticket #232', 'Test ticket #232', NULL, NULL, NULL),
(233, NULL, 1, 2, NULL, NULL, NULL, NULL, 4, 4, NULL, 2, 2, 1, 1, NULL, NULL, '0.76047285810157', '', '', '', '', '', '', 'resolved', 'deleted', NULL, 1, 3, 7, 2, 4, '2014-12-28 10:31:24', '2015-03-16 10:31:24', '2015-04-12 10:31:24', '2015-01-04 10:31:24', NULL, '2014-08-22 10:31:24', '2015-09-21 10:31:24', '2014-10-25 10:31:24', '2015-04-03 10:31:24', '2014-09-05 10:31:24', '2014-08-09 10:31:24', 4, 4, NULL, 2, 'Test ticket #233', 'Test ticket #233', NULL, NULL, NULL),
(234, NULL, 1, 2, NULL, NULL, NULL, NULL, 4, 4, NULL, 1, 1, 1, 3, NULL, 4, '0.27864285025619234', '', '', '', '', '', '', 'resolved', 'spam', NULL, 1, 2, 4, 8, 3, '2015-01-26 10:31:24', '2015-05-03 10:31:24', '2015-09-14 10:31:24', '2015-05-16 10:31:24', NULL, '2014-11-06 10:31:24', '2015-03-02 10:31:24', '2015-07-09 10:31:24', '2014-12-16 10:31:24', '2015-03-16 10:31:24', '2015-05-18 10:31:24', 1, 1, NULL, 1, 'Test ticket #234', 'Test ticket #234', NULL, NULL, NULL),
(235, NULL, 1, 3, NULL, NULL, NULL, NULL, 2, 3, NULL, 3, NULL, 1, 1, NULL, NULL, '0.9051999281208961', '', '', '', '', '', '', 'resolved', 'validating', NULL, 0, 3, 1, 7, 4, '2014-06-02 10:31:24', '2015-09-30 10:31:24', '2015-08-08 10:31:24', '2015-01-01 10:31:24', NULL, '2015-01-24 10:31:24', '2015-06-02 10:31:24', '2014-10-02 10:31:24', '2014-06-25 10:31:24', '2015-02-14 10:31:24', '2015-01-09 10:31:24', 2, 4, NULL, 2, 'Test ticket #235', 'Test ticket #235', NULL, NULL, NULL),
(236, NULL, 1, 2, NULL, NULL, NULL, NULL, 1, 1, NULL, 1, 2, 1, 2, NULL, 3, '0.5043185609433042', '', '', '', '', '', '', 'hidden', 'validating', NULL, 0, 3, 3, 6, 4, '2014-07-25 10:31:24', '2014-10-24 10:31:24', '2014-08-11 10:31:24', '2015-07-29 10:31:24', '2015-07-13 10:31:24', '2015-03-02 10:31:24', '2014-11-01 10:31:24', '2015-08-22 10:31:24', '2015-03-09 10:31:24', '2014-08-12 10:31:24', '2014-06-22 10:31:24', 1, 1, NULL, 1, 'Test ticket #236', 'Test ticket #236', NULL, NULL, NULL),
(237, NULL, 1, 1, NULL, NULL, NULL, NULL, 3, 2, NULL, 4, 1, 1, 2, NULL, NULL, '0.017010526747452587', '', '', '', '', '', '', 'archived', 'validating', NULL, 1, 3, 9, 6, 1, '2015-01-26 10:31:24', '2015-01-12 10:31:24', '2015-07-23 10:31:24', '2015-07-20 10:31:24', NULL, '2015-06-17 10:31:24', '2015-08-15 10:31:24', '2014-08-04 10:31:24', '2014-06-08 10:31:24', '2015-05-12 10:31:24', '2014-12-31 10:31:24', 4, 4, NULL, 3, 'Test ticket #237', 'Test ticket #237', NULL, NULL, NULL),
(238, NULL, 1, 2, NULL, NULL, NULL, NULL, 2, 4, NULL, 3, NULL, 1, 4, NULL, NULL, '0.7652553205986091', '', '', '', '', '', '', 'archived', 'validating', NULL, 1, 3, 2, 10, 1, '2014-07-16 10:31:24', '2015-09-09 10:31:24', '2014-12-12 10:31:24', '2014-08-20 10:31:24', NULL, '2015-06-20 10:31:24', '2015-08-28 10:31:24', '2014-09-28 10:31:24', '2015-01-29 10:31:24', '2015-05-24 10:31:24', '2014-08-06 10:31:24', 2, 3, NULL, 1, 'Test ticket #238', 'Test ticket #238', NULL, NULL, NULL),
(239, NULL, 1, 1, NULL, NULL, NULL, NULL, 4, 3, NULL, 2, 1, 1, 4, NULL, NULL, '0.474345788801411', '', '', '', '', '', '', 'awaiting_agent', 'temp', NULL, 1, 1, 5, 10, 3, '2015-01-02 10:31:24', '2014-10-27 10:31:24', '2014-09-09 10:31:24', '2014-08-03 10:31:24', NULL, '2015-07-29 10:31:24', '2014-07-25 10:31:24', '2014-06-10 10:31:24', '2015-06-24 10:31:24', '2015-07-23 10:31:24', '2015-08-04 10:31:24', 1, 3, NULL, 1, 'Test ticket #239', 'Test ticket #239', NULL, NULL, NULL),
(240, NULL, 1, 2, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, 1, 1, 1, NULL, 1, '0.14447592212304095', '', '', '', '', '', '', 'awaiting_agent', 'deleted', NULL, 1, 2, 6, 6, 1, '2014-11-21 10:31:24', '2015-09-19 10:31:24', '2015-05-28 10:31:24', '2015-06-22 10:31:24', NULL, '2014-09-15 10:31:24', '2015-09-27 10:31:24', '2014-09-09 10:31:24', '2014-07-28 10:31:24', '2015-10-04 10:31:24', '2015-03-11 10:31:24', 1, 1, NULL, 2, 'Test ticket #240', 'Test ticket #240', NULL, NULL, NULL),
(241, NULL, 1, 1, NULL, NULL, NULL, NULL, 3, 1, NULL, 5, NULL, 1, 2, NULL, NULL, '0.42237857582269106', '', '', '', '', '', '', 'resolved', 'temp', NULL, 0, 4, 1, 9, 3, '2015-04-27 10:31:24', '2015-03-20 10:31:24', '2015-09-21 10:31:24', '2014-06-17 10:31:24', '2014-10-30 10:31:24', '2015-01-01 10:31:24', '2014-09-30 10:31:24', '2015-09-12 10:31:24', '2015-09-26 10:31:24', '2014-06-15 10:31:24', '2014-10-09 10:31:24', 4, 3, NULL, 3, 'Test ticket #241', 'Test ticket #241', NULL, NULL, NULL),
(242, NULL, 1, 3, NULL, NULL, NULL, NULL, 1, 3, NULL, 2, NULL, 1, 3, NULL, NULL, '0.5285752439206236', '', '', '', '', '', '', 'resolved', 'deleted', NULL, 0, 2, 1, 10, 2, '2014-06-13 10:31:24', '2014-09-03 10:31:24', '2015-08-12 10:31:24', '2015-07-15 10:31:24', NULL, '2015-09-18 10:31:24', '2014-11-04 10:31:24', '2015-06-03 10:31:24', '2015-06-07 10:31:24', '2015-02-14 10:31:24', '2014-12-02 10:31:24', 3, 3, NULL, 3, 'Test ticket #242', 'Test ticket #242', NULL, NULL, NULL),
(243, NULL, 1, 1, NULL, NULL, NULL, NULL, 3, 1, NULL, 4, 1, 1, 4, NULL, NULL, '0.5216102930918431', '', '', '', '', '', '', 'awaiting_user', 'spam', NULL, 1, 3, 8, 1, 1, '2015-04-27 10:31:24', '2015-05-25 10:31:24', '2015-04-02 10:31:24', '2015-08-30 10:31:24', NULL, '2015-09-17 10:31:24', '2015-02-25 10:31:24', '2015-08-08 10:31:24', '2015-05-29 10:31:24', '2014-06-20 10:31:24', '2014-06-13 10:31:24', 4, 4, NULL, 3, 'Test ticket #243', 'Test ticket #243', NULL, NULL, NULL),
(244, NULL, 1, 3, NULL, NULL, NULL, NULL, 1, 4, NULL, NULL, 1, 1, 1, NULL, NULL, '0.09103957944646439', '', '', '', '', '', '', 'archived', 'deleted', NULL, 0, 3, 7, 5, 2, '2015-03-22 10:31:24', '2014-07-09 10:31:24', '2015-04-11 10:31:24', '2015-09-04 10:31:24', NULL, '2015-08-22 10:31:24', '2014-10-27 10:31:24', '2015-07-09 10:31:24', '2014-08-17 10:31:24', '2014-11-30 10:31:24', '2014-12-02 10:31:24', 1, 2, NULL, 3, 'Test ticket #244', 'Test ticket #244', NULL, NULL, NULL),
(245, NULL, 1, 2, NULL, NULL, NULL, NULL, 4, 1, NULL, 4, 2, 1, 1, NULL, NULL, '0.36127295937507686', '', '', '', '', '', '', 'resolved', 'spam', NULL, 1, 4, 3, 9, 3, '2015-08-16 10:31:24', '2014-07-16 10:31:24', '2015-07-10 10:31:24', '2015-06-28 10:31:24', NULL, '2014-08-28 10:31:24', '2014-12-09 10:31:24', '2014-12-14 10:31:24', '2015-07-19 10:31:24', '2015-09-27 10:31:24', '2014-11-30 10:31:24', 1, 2, NULL, 3, 'Test ticket #245', 'Test ticket #245', NULL, NULL, NULL),
(246, NULL, 1, 3, NULL, NULL, NULL, NULL, 2, 1, NULL, 1, NULL, 1, 4, NULL, NULL, '0.07630900486959984', '', '', '', '', '', '', 'hidden', 'deleted', NULL, 1, 3, 6, 8, 2, '2015-02-25 10:31:24', '2015-07-20 10:31:24', '2015-02-22 10:31:24', '2014-09-02 10:31:24', NULL, '2014-08-26 10:31:24', '2015-07-25 10:31:24', '2015-05-10 10:31:24', '2015-09-08 10:31:24', '2015-03-26 10:31:24', '2014-09-11 10:31:24', 3, 2, NULL, 2, 'Test ticket #246', 'Test ticket #246', NULL, NULL, NULL),
(247, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, 2, NULL, 2, 2, 1, 2, NULL, NULL, '0.5675476571242769', '', '', '', '', '', '', 'archived', 'spam', NULL, 0, 4, 7, 5, 2, '2015-08-30 10:31:24', '2014-07-08 10:31:24', '2015-04-23 10:31:24', '2014-06-26 10:31:24', NULL, '2014-12-04 10:31:24', '2015-09-29 10:31:24', '2015-06-05 10:31:24', '2015-07-01 10:31:24', '2015-06-10 10:31:24', '2014-12-10 10:31:24', 2, 3, NULL, 2, 'Test ticket #247', 'Test ticket #247', NULL, NULL, NULL),
(248, NULL, 1, 3, NULL, NULL, NULL, NULL, 4, 4, NULL, 4, NULL, 1, 1, NULL, NULL, '0.7295169473900618', '', '', '', '', '', '', 'resolved', 'deleted', NULL, 1, 4, 1, 6, 3, '2015-06-27 10:31:24', '2015-05-14 10:31:24', '2014-08-10 10:31:24', '2015-04-13 10:31:24', NULL, '2015-07-26 10:31:24', '2015-10-01 10:31:24', '2014-12-01 10:31:24', '2015-09-02 10:31:24', '2015-02-03 10:31:24', '2015-06-02 10:31:24', 4, 1, NULL, 3, 'Test ticket #248', 'Test ticket #248', NULL, NULL, NULL),
(249, NULL, 1, 1, NULL, NULL, NULL, NULL, 4, 4, NULL, 3, NULL, 1, 1, NULL, NULL, '0.36033370845050894', '', '', '', '', '', '', 'resolved', 'spam', NULL, 1, 3, 8, 6, 4, '2015-09-23 10:31:24', '2014-08-03 10:31:24', '2015-06-19 10:31:24', '2015-01-21 10:31:24', NULL, '2015-07-31 10:31:24', '2014-07-30 10:31:24', '2014-06-28 10:31:24', '2015-09-06 10:31:24', '2015-01-21 10:31:24', '2015-03-18 10:31:24', 2, 1, NULL, 2, 'Test ticket #249', 'Test ticket #249', NULL, NULL, NULL),
(250, 88, 1, 2, NULL, NULL, NULL, NULL, 4, 1, NULL, 1, NULL, 1, 1, NULL, 1, '0.8314968057270132', '', '', '', '', '', '', 'hidden', 'temp', NULL, 1, 4, 4, 9, 3, '2015-08-05 10:31:24', '2014-06-16 10:31:24', '2015-03-22 10:31:24', '2015-08-02 10:31:24', '2015-02-17 10:31:24', '2014-07-01 10:31:24', '2015-06-06 10:31:24', '2015-02-22 10:31:24', '2015-01-14 10:31:24', '2015-05-09 10:31:24', '2014-07-03 10:31:24', 3, 3, NULL, 2, 'Test ticket #250', 'Test ticket #250', NULL, NULL, NULL),
(251, 58, 1, 3, NULL, NULL, NULL, NULL, 3, 4, NULL, 1, 1, 1, 3, NULL, NULL, '0.380632205289446', '', '', '', '', '', '', 'awaiting_user', 'temp', NULL, 1, 4, 8, 10, 3, '2014-08-05 10:31:24', '2014-12-12 10:31:24', '2015-03-08 10:31:24', '2015-04-21 10:31:24', NULL, '2015-09-06 10:31:24', '2015-08-31 10:31:24', '2015-07-06 10:31:24', '2014-10-19 10:31:24', '2014-06-06 10:31:24', '2014-09-21 10:31:24', 4, 1, NULL, 1, 'Test ticket #251', 'Test ticket #251', NULL, NULL, NULL),
(252, NULL, 1, 1, NULL, NULL, NULL, NULL, 4, 1, NULL, NULL, 2, 1, 4, NULL, 4, '0.9844506885711576', '', '', '', '', '', '', 'awaiting_user', 'spam', NULL, 0, 3, 6, 8, 2, '2014-10-02 10:31:24', '2015-03-04 10:31:24', '2014-06-19 10:31:24', '2015-02-20 10:31:24', NULL, '2014-08-24 10:31:24', '2014-09-13 10:31:24', '2015-02-28 10:31:24', '2014-07-26 10:31:24', '2015-09-08 10:31:24', '2014-11-10 10:31:24', 1, 3, NULL, 1, 'Test ticket #252', 'Test ticket #252', NULL, NULL, NULL),
(253, NULL, 1, 3, NULL, NULL, NULL, NULL, 1, 3, NULL, 1, 2, 1, 2, NULL, NULL, '0.4774751807353228', '', '', '', '', '', '', 'awaiting_user', 'temp', NULL, 0, 2, 10, 3, 2, '2015-01-30 10:31:24', '2015-06-29 10:31:24', '2015-01-29 10:31:24', '2014-07-08 10:31:24', NULL, '2015-01-10 10:31:24', '2015-01-09 10:31:24', '2015-08-21 10:31:24', '2014-08-12 10:31:24', '2014-06-28 10:31:24', '2015-08-01 10:31:24', 4, 1, NULL, 2, 'Test ticket #253', 'Test ticket #253', NULL, NULL, NULL),
(254, NULL, 1, 1, NULL, NULL, NULL, NULL, 2, 4, NULL, 4, 2, 1, 2, NULL, 1, '0.9326505436866084', '', '', '', '', '', '', 'archived', 'spam', NULL, 1, 3, 8, 7, 2, '2015-03-17 10:31:24', '2015-07-20 10:31:24', '2014-12-23 10:31:24', '2015-03-20 10:31:24', NULL, '2015-06-02 10:31:24', '2015-03-24 10:31:24', '2015-06-21 10:31:24', '2014-07-16 10:31:24', '2014-08-13 10:31:24', '2015-01-23 10:31:24', 1, 3, NULL, 2, 'Test ticket #254', 'Test ticket #254', NULL, NULL, NULL),
(255, NULL, 1, 2, NULL, NULL, NULL, NULL, 2, 4, NULL, 4, 1, 1, 3, NULL, NULL, '0.35346372458475056', '', '', '', '', '', '', 'resolved', 'deleted', NULL, 1, 2, 3, 2, 1, '2015-09-28 10:31:24', '2014-08-17 10:31:24', '2015-08-10 10:31:24', '2015-08-24 10:31:24', NULL, '2015-06-29 10:31:24', '2014-10-10 10:31:24', '2015-09-23 10:31:24', '2014-06-09 10:31:24', '2014-09-17 10:31:24', '2014-06-21 10:31:24', 2, 2, NULL, 1, 'Test ticket #255', 'Test ticket #255', NULL, NULL, NULL),
(256, NULL, 1, 2, NULL, NULL, NULL, NULL, 1, 1, NULL, 1, 1, 1, 4, NULL, NULL, '0.08860759538468681', '', '', '', '', '', '', 'archived', NULL, NULL, 0, 2, 3, 1, 3, '2014-06-28 10:31:24', '2014-08-20 10:31:24', '2015-04-22 10:31:24', '2015-06-25 10:31:24', NULL, '2014-12-28 10:31:24', '2014-10-11 10:31:24', '2014-07-08 10:31:24', '2015-03-22 10:31:24', '2015-05-31 10:31:24', '2015-08-19 10:31:24', 3, 2, NULL, 1, 'Test ticket #256', 'Test ticket #256', NULL, NULL, NULL),
(257, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, 1, NULL, 3, NULL, 1, 1, NULL, NULL, '0.27338647495339297', '', '', '', '', '', '', 'hidden', 'spam', NULL, 1, 2, 8, 6, 2, '2014-10-27 10:31:24', '2015-09-07 10:31:24', '2015-06-09 10:31:24', '2015-09-29 10:31:24', NULL, '2014-09-07 10:31:24', '2014-08-09 10:31:24', '2014-07-25 10:31:24', '2014-08-08 10:31:24', '2014-11-30 10:31:24', '2015-01-02 10:31:24', 4, 4, NULL, 3, 'Test ticket #257', 'Test ticket #257', NULL, NULL, NULL),
(258, NULL, 1, 3, NULL, NULL, NULL, NULL, 1, 2, NULL, NULL, 1, 1, 3, NULL, 4, '0.7394444064604532', '', '', '', '', '', '', 'awaiting_agent', NULL, NULL, 1, 2, 5, 4, 1, '2014-10-22 10:31:24', '2015-08-13 10:31:24', '2015-02-21 10:31:24', '2014-06-18 10:31:24', NULL, '2015-08-12 10:31:24', '2015-03-29 10:31:24', '2014-12-18 10:31:24', '2014-09-10 10:31:24', '2015-07-16 10:31:24', '2015-02-08 10:31:24', 4, 1, NULL, 3, 'Test ticket #258', 'Test ticket #258', NULL, NULL, NULL),
(259, NULL, 1, 1, NULL, NULL, NULL, NULL, 1, 3, NULL, 4, 1, 1, 4, NULL, 2, '0.18690245895358032', '', '', '', '', '', '', 'resolved', 'temp', NULL, 1, 4, 9, 7, 3, '2015-08-24 10:31:24', '2014-10-16 10:31:24', '2015-05-09 10:31:24', '2015-04-03 10:31:24', NULL, '2014-10-07 10:31:24', '2014-09-28 10:31:24', '2015-01-07 10:31:24', '2015-02-03 10:31:24', '2014-08-21 10:31:24', '2014-11-15 10:31:24', 4, 4, NULL, 2, 'Test ticket #259', 'Test ticket #259', NULL, NULL, NULL),
(260, NULL, 1, 3, NULL, NULL, NULL, NULL, 2, 1, NULL, 3, 1, 1, 2, NULL, NULL, '0.5062509081384641', '', '', '', '', '', '', 'hidden', 'validating', NULL, 0, 4, 1, 8, 4, '2015-09-19 10:31:24', '2015-01-09 10:31:24', '2014-12-08 10:31:24', '2015-03-18 10:31:24', NULL, '2014-07-31 10:31:24', '2014-10-30 10:31:24', '2014-08-21 10:31:24', '2015-08-31 10:31:24', '2014-07-14 10:31:24', '2015-05-21 10:31:24', 3, 3, NULL, 1, 'Test ticket #260', 'Test ticket #260', NULL, NULL, NULL),
(261, NULL, 1, 1, NULL, NULL, NULL, NULL, 3, 3, NULL, 1, 1, 1, 3, NULL, NULL, '0.05606343136733717', '', '', '', '', '', '', 'awaiting_user', 'spam', NULL, 0, 3, 8, 10, 1, '2015-03-04 10:31:24', '2015-02-18 10:31:24', '2015-10-01 10:31:24', '2014-11-05 10:31:24', '2015-04-25 10:31:24', '2014-11-21 10:31:24', '2015-06-24 10:31:24', '2015-08-01 10:31:24', '2015-09-16 10:31:24', '2014-08-26 10:31:24', '2014-06-23 10:31:24', 2, 3, NULL, 3, 'Test ticket #261', 'Test ticket #261', NULL, NULL, NULL),
(262, NULL, 1, 3, NULL, NULL, NULL, NULL, 1, 4, NULL, 3, 1, 1, 2, NULL, 2, '0.3916694870141051', '', '', '', '', '', '', 'awaiting_agent', NULL, '1', 0, 2, 1, 8, 1, '2015-09-29 10:31:24', '2015-03-11 10:31:24', '2015-09-12 10:31:24', '2014-05-27 10:31:24', NULL, '2015-07-17 10:31:24', '2015-04-23 10:31:24', '2015-07-07 10:31:24', '2014-07-03 10:31:24', '2015-09-07 10:31:24', '2015-01-11 10:31:24', 3, 4, NULL, 1, 'Test ticket #262', 'Test ticket #262', NULL, NULL, NULL),
(263, NULL, 1, 1, NULL, NULL, NULL, NULL, 3, 2, NULL, 5, NULL, 1, 1, NULL, 2, '0.2027800131615065', '', '', '', '', '', '', 'resolved', 'spam', NULL, 1, 3, 1, 6, 3, '2015-03-30 10:31:24', '2015-08-15 10:31:24', '2015-03-28 10:31:24', '2014-12-04 10:31:24', NULL, '2014-09-28 10:31:24', '2014-05-30 10:31:24', '2014-10-16 10:31:24', '2014-12-14 10:31:24', '2014-08-17 10:31:24', '2015-03-28 10:31:24', 2, 1, NULL, 3, 'Test ticket #263', 'Test ticket #263', NULL, NULL, NULL),
(264, NULL, 1, 3, NULL, NULL, NULL, NULL, 4, 2, NULL, 3, 2, 1, 4, NULL, 2, '0.7047230095646558', '', '', '', '', '', '', 'awaiting_agent', 'validating', NULL, 0, 4, 9, 4, 2, '2014-11-17 10:31:24', '2015-06-15 10:31:24', '2015-06-27 10:31:24', '2015-04-22 10:31:24', '2015-09-02 10:31:24', '2015-04-18 10:31:24', '2015-01-22 10:31:24', '2015-01-04 10:31:24', '2015-06-23 10:31:24', '2015-03-15 10:31:24', '2015-03-10 10:31:24', 4, 1, NULL, 1, 'Test ticket #264', 'Test ticket #264', NULL, NULL, NULL),
(265, NULL, 1, 3, NULL, NULL, NULL, NULL, 1, 4, NULL, 3, 2, 1, 4, NULL, NULL, '0.22146803626927364', '', '', '', '', '', '', 'hidden', NULL, '1', 1, 1, 1, 7, 1, '2015-09-01 10:31:24', '2014-10-02 10:31:24', '2015-02-02 10:31:24', '2015-06-06 10:31:24', '2014-09-23 10:31:24', '2015-09-11 10:31:24', '2014-05-31 10:31:24', '2014-09-10 10:31:24', '2014-06-13 10:31:24', '2015-02-20 10:31:24', '2015-03-17 10:31:24', 3, 1, NULL, 3, 'Test ticket #265', 'Test ticket #265', NULL, NULL, NULL),
(266, NULL, 1, 3, NULL, NULL, NULL, NULL, 1, 2, NULL, 4, 2, 1, 3, NULL, NULL, '0.2617164182120156', '', '', '', '', '', '', 'awaiting_agent', 'spam', '0', 1, 1, 8, 5, 1, '2015-04-18 10:31:24', '2015-05-06 10:31:24', '2015-01-23 10:31:24', '2014-11-15 10:31:24', NULL, '2014-11-24 10:31:24', '2015-09-27 10:31:24', '2015-06-27 10:31:24', '2014-06-15 10:31:24', '2015-07-06 10:31:24', '2015-09-08 10:31:24', 3, 3, NULL, 2, 'Test ticket #266', 'Test ticket #266', NULL, NULL, NULL);

SET FOREIGN_KEY_CHECKS=1;

INSERT INTO `tickets_participants`
(`id`, `ticket_id`, `person_id`, `access_code_id`, `person_email_id`, `default_on`)

VALUES
(1, 36, 4, NULL, NULL, 1),
(2, 193, 2, NULL, NULL, 0),
(3, 168, 3, NULL, NULL, 0),
(4, 124, 5, NULL, NULL, 0),
(5, 49, 1, NULL, NULL, 1),
(6, 51, 1, NULL, NULL, 0),
(7, 95, 2, NULL, NULL, 1),
(8, 89, 4, NULL, NULL, 0),
(9, 101, 4, NULL, NULL, 1),
(10, 144, 4, NULL, NULL, 0),
(11, 138, 5, NULL, NULL, 0),
(12, 35, 4, NULL, NULL, 0),
(13, 190, 4, NULL, NULL, 1),
(14, 172, 3, NULL, NULL, 0),
(15, 90, 1, NULL, NULL, 1),
(16, 30, 2, NULL, NULL, 1),
(17, 68, 3, NULL, NULL, 1),
(18, 154, 3, NULL, NULL, 0),
(19, 214, 5, NULL, NULL, 0),
(20, 82, 2, NULL, NULL, 1),
(21, 51, 5, NULL, NULL, 1),
(22, 203, 4, NULL, NULL, 0),
(23, 126, 1, NULL, NULL, 1),
(24, 144, 4, NULL, NULL, 1),
(25, 33, 4, NULL, NULL, 0),
(26, 28, 3, NULL, NULL, 0),
(27, 84, 3, NULL, NULL, 0),
(28, 89, 4, NULL, NULL, 0),
(29, 152, 5, NULL, NULL, 1),
(30, 76, 3, NULL, NULL, 1),
(31, 51, 3, NULL, NULL, 0),
(32, 150, 1, NULL, NULL, 1),
(33, 91, 1, NULL, NULL, 1),
(34, 106, 2, NULL, NULL, 1),
(35, 146, 4, NULL, NULL, 1),
(36, 96, 1, NULL, NULL, 0),
(37, 70, 5, NULL, NULL, 0),
(38, 94, 4, NULL, NULL, 0),
(39, 133, 3, NULL, NULL, 1),
(40, 205, 3, NULL, NULL, 0),
(41, 106, 1, NULL, NULL, 1),
(42, 26, 4, NULL, NULL, 0),
(43, 137, 3, NULL, NULL, 0),
(44, 176, 3, NULL, NULL, 0),
(45, 109, 5, NULL, NULL, 0),
(46, 42, 5, NULL, NULL, 1),
(47, 116, 2, NULL, NULL, 1),
(48, 45, 2, NULL, NULL, 1),
(49, 129, 1, NULL, NULL, 1),
(50, 46, 5, NULL, NULL, 0),
(51, 89, 5, NULL, NULL, 1),
(52, 211, 4, NULL, NULL, 1),
(53, 149, 5, NULL, NULL, 0),
(54, 125, 2, NULL, NULL, 1),
(55, 39, 1, NULL, NULL, 0),
(56, 70, 5, NULL, NULL, 1),
(57, 215, 4, NULL, NULL, 0),
(58, 32, 4, NULL, NULL, 0),
(59, 90, 5, NULL, NULL, 0),
(60, 197, 1, NULL, NULL, 0),
(61, 29, 5, NULL, NULL, 0),
(62, 143, 2, NULL, NULL, 0),
(63, 153, 4, NULL, NULL, 1),
(64, 81, 4, NULL, NULL, 0),
(65, 28, 5, NULL, NULL, 0),
(66, 147, 4, NULL, NULL, 0),
(67, 78, 5, NULL, NULL, 0),
(68, 199, 5, NULL, NULL, 1),
(69, 143, 1, NULL, NULL, 1),
(70, 130, 1, NULL, NULL, 0),
(71, 67, 4, NULL, NULL, 1),
(72, 149, 2, NULL, NULL, 0),
(73, 32, 1, NULL, NULL, 1),
(74, 64, 3, NULL, NULL, 1),
(75, 132, 5, NULL, NULL, 0),
(76, 162, 3, NULL, NULL, 0),
(77, 93, 2, NULL, NULL, 1),
(78, 173, 1, NULL, NULL, 0),
(79, 51, 4, NULL, NULL, 1),
(80, 86, 5, NULL, NULL, 1),
(81, 224, 4, NULL, NULL, 0),
(82, 87, 1, NULL, NULL, 1),
(83, 45, 3, NULL, NULL, 1),
(84, 107, 5, NULL, NULL, 0),
(85, 217, 3, NULL, NULL, 0),
(86, 215, 2, NULL, NULL, 1),
(87, 41, 5, NULL, NULL, 0),
(88, 150, 4, NULL, NULL, 0),
(89, 170, 4, NULL, NULL, 1),
(90, 223, 1, NULL, NULL, 0),
(91, 182, 3, NULL, NULL, 1),
(92, 72, 1, NULL, NULL, 1),
(93, 108, 5, NULL, NULL, 0),
(94, 36, 5, NULL, NULL, 0),
(95, 91, 1, NULL, NULL, 0),
(96, 177, 4, NULL, NULL, 1),
(97, 37, 5, NULL, NULL, 1),
(98, 61, 1, NULL, NULL, 1),
(99, 196, 5, NULL, NULL, 1),
(100, 116, 3, NULL, NULL, 1),
(101, 103, 1, NULL, NULL, 0),
(102, 177, 2, NULL, NULL, 1),
(103, 117, 5, NULL, NULL, 1),
(104, 34, 1, NULL, NULL, 1),
(105, 71, 3, NULL, NULL, 0),
(106, 81, 4, NULL, NULL, 0),
(107, 88, 1, NULL, NULL, 1),
(108, 114, 5, NULL, NULL, 0),
(109, 82, 5, NULL, NULL, 0),
(110, 141, 2, NULL, NULL, 1),
(111, 151, 1, NULL, NULL, 0),
(112, 149, 1, NULL, NULL, 0),
(113, 147, 1, NULL, NULL, 0),
(114, 206, 5, NULL, NULL, 0),
(115, 43, 5, NULL, NULL, 1),
(116, 148, 4, NULL, NULL, 0),
(117, 152, 2, NULL, NULL, 0),
(118, 49, 3, NULL, NULL, 1),
(119, 132, 4, NULL, NULL, 1),
(120, 97, 1, NULL, NULL, 1),
(121, 109, 3, NULL, NULL, 1),
(122, 175, 2, NULL, NULL, 1),
(123, 161, 4, NULL, NULL, 1),
(124, 77, 2, NULL, NULL, 0),
(125, 139, 1, NULL, NULL, 1),
(126, 133, 4, NULL, NULL, 1),
(127, 184, 5, NULL, NULL, 1),
(128, 26, 2, NULL, NULL, 0),
(129, 26, 4, NULL, NULL, 0),
(130, 107, 5, NULL, NULL, 0),
(131, 157, 3, NULL, NULL, 0),
(132, 207, 1, NULL, NULL, 1),
(133, 207, 5, NULL, NULL, 0),
(134, 42, 5, NULL, NULL, 1),
(135, 105, 4, NULL, NULL, 0),
(136, 178, 4, NULL, NULL, 0),
(137, 117, 1, NULL, NULL, 1),
(138, 87, 3, NULL, NULL, 1),
(139, 79, 1, NULL, NULL, 0),
(140, 109, 1, NULL, NULL, 1),
(141, 42, 2, NULL, NULL, 1),
(142, 143, 2, NULL, NULL, 0),
(143, 204, 5, NULL, NULL, 0),
(144, 164, 1, NULL, NULL, 1),
(145, 94, 3, NULL, NULL, 1),
(146, 169, 4, NULL, NULL, 0),
(147, 120, 1, NULL, NULL, 0),
(148, 130, 1, NULL, NULL, 1),
(149, 93, 5, NULL, NULL, 1),
(150, 28, 3, NULL, NULL, 0),
(151, 33, 1, NULL, NULL, 1),
(152, 170, 4, NULL, NULL, 1),
(153, 35, 2, NULL, NULL, 0),
(154, 93, 4, NULL, NULL, 0),
(155, 160, 2, NULL, NULL, 1),
(156, 35, 4, NULL, NULL, 1),
(157, 172, 5, NULL, NULL, 0),
(158, 100, 2, NULL, NULL, 0),
(159, 222, 3, NULL, NULL, 0),
(160, 166, 2, NULL, NULL, 0),
(161, 187, 4, NULL, NULL, 0),
(162, 212, 1, NULL, NULL, 0),
(163, 39, 4, NULL, NULL, 0),
(164, 82, 3, NULL, NULL, 0),
(165, 182, 3, NULL, NULL, 1),
(166, 65, 2, NULL, NULL, 1),
(167, 208, 1, NULL, NULL, 0),
(168, 190, 5, NULL, NULL, 0),
(169, 80, 1, NULL, NULL, 0),
(170, 189, 5, NULL, NULL, 0),
(171, 42, 5, NULL, NULL, 0),
(172, 174, 5, NULL, NULL, 1),
(173, 106, 1, NULL, NULL, 0),
(174, 105, 4, NULL, NULL, 1),
(175, 29, 1, NULL, NULL, 1),
(176, 218, 4, NULL, NULL, 0),
(177, 166, 3, NULL, NULL, 0),
(178, 108, 5, NULL, NULL, 0),
(179, 87, 2, NULL, NULL, 0),
(180, 123, 1, NULL, NULL, 1),
(181, 191, 4, NULL, NULL, 0),
(182, 43, 4, NULL, NULL, 1),
(183, 201, 3, NULL, NULL, 1),
(184, 45, 4, NULL, NULL, 0),
(185, 91, 4, NULL, NULL, 1),
(186, 172, 2, NULL, NULL, 0),
(187, 221, 3, NULL, NULL, 0),
(188, 123, 5, NULL, NULL, 1),
(189, 145, 3, NULL, NULL, 0),
(190, 185, 4, NULL, NULL, 0),
(191, 172, 1, NULL, NULL, 1),
(192, 81, 4, NULL, NULL, 1),
(193, 124, 4, NULL, NULL, 1),
(194, 73, 2, NULL, NULL, 1),
(195, 153, 3, NULL, NULL, 0),
(196, 144, 3, NULL, NULL, 1),
(197, 66, 3, NULL, NULL, 1),
(198, 127, 1, NULL, NULL, 0);

INSERT INTO `agent_team_members`
(`team_id`, `person_id`)

VALUES
(1, 1),
(1, 2),
(1, 5),
(2, 2),
(2, 4),
(2, 5),
(3, 4);

");

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
$FEEDBACK_BUG     = $em->getRepository('DeskPRO:FeedbackCategory')->find(3);

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

$em->getConnection()->executeUpdate("
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
        ('person', 'Third person label', 'yellow', 3),

        ('ticket', 'Label #1', '#B0171F', 2),
        ('ticket', 'Label #2', '#DC143C', 35),
        ('ticket', 'Label #3', '#FFB6C1', 62),
        ('ticket', 'Label #4', '#FFAEB9', 53),
        ('ticket', 'Label #5', '#EEA2AD', 0),
        ('ticket', 'Label #6', '#CD8C95', 25),
        ('ticket', 'Label #7', '#8B5F65', 91),
        ('ticket', 'Label #8', '#C71585', 72),
        ('ticket', 'Label #9', '#D02090', 89),
        ('ticket', 'Label #10', '#DA70D6', 152),
        ('ticket', 'Label #11', '#FF83FA', 0),
        ('ticket', 'Label #12', '#EE7AE9', 194),
        ('ticket', 'Label #13', '#CD69C9', 91),
        ('ticket', 'Label #14', '#8B4789', 98),
        ('ticket', 'Label #15', '#B452CD', 64),
        ('ticket', 'Label #16', '#7A378B', 0),
        ('ticket', 'Label #17', '#9400D3', 131),
        ('ticket', 'Label #18', '#68228B', 147),
        ('ticket', 'Label #19', '#4B0082', 47),
        ('ticket', 'Label #20', '#8A2BE2', 36),
        ('ticket', 'Label #21', '#8968CD', 1),
        ('ticket', 'Label #22', '#5D478B', 104),
        ('ticket', 'Label #23', '#483D8B', 3),
        ('ticket', 'Label #24', '#8470FF', 86),
        ('ticket', 'Label #25', '#6959CD', 116),
        ('ticket', 'Label #26', '#473C8B', 112),
        ('ticket', 'Label #27', '#F8F8FF', 0),
        ('ticket', 'Label #28', '#E6E6FA', 144),
        ('ticket', 'Label #29', '#0000FF', 10),
        ('ticket', 'Label #30', '#0000EE', 182),
        ('ticket', 'Label #31', '#0000CD', 41),
        ('ticket', 'Label #32', '#00008B', 44),
        ('ticket', 'Label #33', '#6495ED', 16),
        ('ticket', 'Label #34', '#B0C4DE', 103),
        ('ticket', 'Label #35', '#CAE1FF', 97),
        ('ticket', 'Label #36', '#BCD2EE', 141),
        ('ticket', 'Label #37', '#A2B5CD', 128),
        ('ticket', 'Label #38', '#6E7B8B', 188),
        ('ticket', 'Label #39', '#778899', 12),
        ('ticket', 'Label #40', '#1C86EE', 17),
        ('ticket', 'Label #41', '#1874CD', 140),
        ('ticket', 'Label #42', '#104E8B', 124),
        ('ticket', 'Label #43', '#F0F8FF', 10),
        ('ticket', 'Label #44', '#4682B4', 31),
        ('ticket', 'Label #45', '#63B8FF', 21),
        ('ticket', 'Label #46', '#A4D3EE', 75),
        ('ticket', 'Label #47', '#8DB6CD', 84),
        ('ticket', 'Label #48', '#607B8B', 153),
        ('ticket', 'Label #49', '#87CEFF', 22),
        ('ticket', 'Label #50', '#7EC0EE', 132),
        ('ticket', 'Label #51', '#6CA6CD', 189),
        ('ticket', 'Label #52', '#00B2EE', 70),
        ('ticket', 'Label #53', '#009ACD', 36),
        ('ticket', 'Label #54', '#00688B', 58),
        ('ticket', 'Label #55', '#33A1C9', 156),
        ('ticket', 'Label #56', '#ADD8E6', 152),
        ('ticket', 'Label #57', '#BFEFFF', 170),
        ('ticket', 'Label #58', '#B2DFEE', 9),
        ('ticket', 'Label #59', '#9AC0CD', 96),
        ('ticket', 'Label #60', '#68838B', 181),
        ('ticket', 'Label #61', '#B0E0E6', 191),
        ('ticket', 'Label #62', '#98F5FF', 137),
        ('ticket', 'Label #63', '#8EE5EE', 25),
        ('ticket', 'Label #64', '#7AC5CD', 7),
        ('ticket', 'Label #65', '#53868B', 39),
        ('ticket', 'Label #66', '#00F5FF', 122),
        ('ticket', 'Label #67', '#2F4F4F', 148),
        ('ticket', 'Label #68', '#97FFFF', 168),
        ('ticket', 'Label #69', '#8DEEEE', 110),
        ('ticket', 'Label #70', '#00EEEE', 161),
        ('ticket', 'Label #71', '#00CDCD', 186),
        ('ticket', 'Label #72', '#008B8B', 50),
        ('ticket', 'Label #73', '#008080', 85),
        ('ticket', 'Label #74', '#48D1CC', 196),
        ('ticket', 'Label #75', '#20B2AA', 81),
        ('ticket', 'Label #76', '#03A89E', 107),
        ('ticket', 'Label #77', '#40E0D0', 71),
        ('ticket', 'Label #78', '#808A87', 166),
        ('ticket', 'Label #79', '#00C78C', 59),
        ('ticket', 'Label #80', '#7FFFD4', 93),
        ('ticket', 'Label #81', '#76EEC6', 97),
        ('ticket', 'Label #82', '#66CDAA', 48),
        ('ticket', 'Label #83', '#458B74', 0),
        ('ticket', 'Label #84', '#00FA9A', 133),
        ('ticket', 'Label #85', '#F5FFFA', 107),
        ('ticket', 'Label #86', '#00FF7F', 7),
        ('ticket', 'Label #87', '#00FF00', 85),
        ('ticket', 'Label #88', '#00EE00', 77),
        ('ticket', 'Label #89', '#00CD00', 129),
        ('ticket', 'Label #90', '#008B00', 182),
        ('ticket', 'Label #91', '#008000', 57),
        ('ticket', 'Label #92', '#76EE00', 120),
        ('ticket', 'Label #93', '#66CD00', 118),
        ('ticket', 'Label #94', '#458B00', 83),
        ('ticket', 'Label #95', '#ADFF2F', 127),
        ('ticket', 'Label #96', '#CAFF70', 157),
        ('ticket', 'Label #97', '#BCEE68', 5),
        ('ticket', 'Label #98', '#A2CD5A', 75),
        ('ticket', 'Label #99', '#FFFFE0', 125),
        ('ticket', 'Label #100', '#EEEED1', 115),
        ('ticket', 'Label #101', '#CDCDB4', 35),
        ('ticket', 'Label #102', '#8B8B7A', 110),
        ('ticket', 'Label #103', '#FAFAD2', 166),
        ('ticket', 'Label #104', '#FFFF00', 121),
        ('ticket', 'Label #105', '#EEEE00', 106),
        ('ticket', 'Label #106', '#CDCD00', 46),
        ('ticket', 'Label #107', '#8B8B00', 27),
        ('ticket', 'Label #108', '#DAA520', 177),
        ('ticket', 'Label #109', '#FFC125', 12),
        ('ticket', 'Label #110', '#EEB422', 86),
        ('ticket', 'Label #111', '#CD9B1D', 70),
        ('ticket', 'Label #112', '#8B6914', 110),
        ('ticket', 'Label #113', '#FFDEAD', 135),
        ('ticket', 'Label #114', '#EECFA1', 33),
        ('ticket', 'Label #115', '#CDB38B', 43),
        ('ticket', 'Label #116', '#8B795E', 41),
        ('ticket', 'Label #117', '#FCE6C9', 153),
        ('ticket', 'Label #118', '#D2B48C', 128),
        ('ticket', 'Label #119', '#FF9912', 118),
        ('ticket', 'Label #120', '#FAEBD7', 81),
        ('ticket', 'Label #121', '#FFEFDB', 109),
        ('ticket', 'Label #122', '#EEDFCC', 176),
        ('ticket', 'Label #123', '#8B4C39', 1),
        ('ticket', 'Label #124', '#FF7256', 27),
        ('ticket', 'Label #125', '#EE6A50', 58),
        ('ticket', 'Label #126', '#CD5B45', 128),
        ('ticket', 'Label #127', '#EE0000', 185),
        ('ticket', 'Label #128', '#CD0000', 63),
        ('ticket', 'Label #129', '#8B0000', 2),
        ('ticket', 'Label #130', '#800000', 109),
        ('ticket', 'Label #131', '#8E388E', 179),
        ('ticket', 'Label #132', '#7171C6', 38),
        ('ticket', 'Label #133', '#7D9EC0', 18),
        ('ticket', 'Label #134', '#388E8E', 145),
        ('ticket', 'Label #135', '#71C671', 159),
        ('ticket', 'Label #136', '#8E8E38', 125),
        ('ticket', 'Label #137', '#C5C1AA', 191),
        ('ticket', 'Label #138', '#C67171', 186),
        ('ticket', 'Label #139', '#555555', 102),
        ('ticket', 'Label #140', '#1E1E1E', 3),
        ('ticket', 'Label #141', '#282828', 72),
        ('ticket', 'Label #142', '#515151', 172),
        ('ticket', 'Label #143', '#5B5B5B', 113),
        ('ticket', 'Label #144', '#848484', 6),
        ('ticket', 'Label #145', '#8E8E8E', 5),
        ('ticket', 'Label #146', '#AAAAAA', 156),
        ('ticket', 'Label #147', '#B7B7B7', 47),
        ('ticket', 'Label #148', '#C1C1C1', 159),
        ('ticket', 'Label #149', '#EAEAEA', 84),
        ('ticket', 'Label #150', '#F4F4F4', 166)
    ;
");

################################################################################
# Add some brands to test different themes
################################################################################
//INSERT INTO brands (name, theme_id) VALUES ('Default Brand', 'standard')
$em->getConnection()->executeUpdate("
INSERT INTO `brands` (`id`, `logo_blob_id`, `name`, `theme_id`)
VALUES
	(1, NULL, 'Standard Theme', 'standard'),
	(2, NULL, 'Sidebar Theme', 'sidebar'),
	(3, NULL, 'Simple Theme', 'simple'),
	(4, NULL, 'Tab Bar Theme', 'tabbar');
");

################################################################################
# TEMPORARY TEST DATA: Tickets problems, stars, messages
################################################################################

$em->getConnection()->executeUpdate("
    SET FOREIGN_KEY_CHECKS=0;

    INSERT INTO
        `tickets_flagged` (`person_id`, `ticket_id`, `color`)
    VALUES
        (1, 260, 'pink'),
        (1, 261, 'blue'),
        (1, 262, 'blue'),
        (2, 272, 'blue')
    ;

    INSERT INTO
        `problems` (`id`, `person_id`, `title`, `created`, `is_open`)
    VALUES
        (1, 1, 'Problem #1', '2015-10-01 00:00:00', 1),
        (2, 1, 'Problem #2', '2015-10-25 00:00:00', 1),
        (3, 1, 'Problem #3', '2015-10-16 00:00:00', 0),
        (4, 2, 'Problem #4', '2015-10-23 00:00:00', 1)
    ;

    INSERT INTO
        `problem2tickets` (`ticket_id`, `problem_id`)
    VALUES
        (260, 1),
        (260, 1),
        (260, 3),
        (261, 4)
    ;

    INSERT INTO
        `tickets_messages` (`id`, `ticket_id`, `person_id`, `email_source_id`, `message_translated_id`, `date_created`, `is_agent_note`, `creation_system`, `ip_address`, `hostname`, `geo_country`, `email`, `message_hash`, `message`, `message_full`, `message_raw`, `lang_code`, `show_full_hint`, `visitor_id`)
    VALUES
        (1, 260, 1, NULL, NULL, '2015-10-01 00:00:00', 0, 'web', '1.1.1.1', 'dp.lo', NULL, 'w1@w.ww', 'hash1', 'Lorem ipsum', NULL, NULL, NULL, 1, NULL),
        (2, 260, 2, NULL, NULL, '2015-11-01 00:00:00', 1, 'web', '1.1.1.1', 'dp.lo', NULL, 'w1@w.ww', 'hash2', 'Sit amet', NULL, NULL, NULL, 1, NULL),
        (3, 260, 2, NULL, NULL, '0000-00-00 00:00:00', 0, 'web', '1.1.1.1', 'dp.lo', NULL, 'w2@w.ww', 'hash3', 'Hello', NULL, NULL, NULL, 1, NULL),
        (4, 261, 1, NULL, NULL, '2015-10-01 00:00:00', 0, 'web', '1.1.1.1', 'dp.lo', NULL, 'w3@w.ww', 'hash4', 'Ololo!', NULL, NULL, NULL, 1, NULL)
    ;

    SET FOREIGN_KEY_CHECKS=1;
");
