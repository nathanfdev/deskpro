<?php if (!defined('DP_ROOT')) {
    exit('No access');
}

################################################################################
# Language
################################################################################

##BEGIN:locale.language##
$l = new \Application\DeskPRO\Entity\Language();
$l['title'] = $translate->phrase('user.defaults.language_english');
$l['locale'] = 'en_US';
$l['sys_name'] = 'default';
$l['flag_image'] = 'us.png';
$l['lang_code'] = 'eng';
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
    $q['is_chat_enabled'] = false;
    $em->persist($q);
    $em->flush();
}

##BEGIN:create_department.department1##
if (!$IMPORT_INSTALL) {
    $q = new \Application\DeskPRO\Entity\Department();
    $q['title'] = $translate->phrase('user.defaults.department_sales');
    $q['is_tickets_enabled'] = true;
    $q['is_chat_enabled'] = false;
    $em->persist($q);
    $em->flush();
}

##BEGIN:create_department.department3##
if (!$IMPORT_INSTALL) {
    $q = new \Application\DeskPRO\Entity\Department();
    $q['title'] = $translate->phrase('user.defaults.department_support');
    $q['is_tickets_enabled'] = false;
    $q['is_chat_enabled'] = true;
    $em->persist($q);
    $em->flush();
}

##BEGIN:create_department.department4##
if (!$IMPORT_INSTALL) {
    $q = new \Application\DeskPRO\Entity\Department();
    $q['title'] = $translate->phrase('user.defaults.department_sales');
    $q['is_tickets_enabled'] = false;
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

$em->getConnection()->executeUpdate("
INSERT INTO `feedback_categories` (`id`, `parent_id`, `title`, `slug`, `display_order`, `depth`, `root`) VALUES
(1, NULL, 'Suggestion', 'suggestion', 0, 0, NULL),
(2, NULL, 'Feature Request', 'feature-request', 0, 0, NULL),
(3, NULL, 'Bug Report', 'bug-report', 0, 0, NULL);
");

$em->getConnection()->executeUpdate("
INSERT INTO `custom_def_feedback` (`id`, `parent_id`, `app_id`, `sys_name`, `js_class`, `has_form_template`, `has_display_template`, `title`, `description`, `handler_class`, `options`, `is_user_enabled`, `is_enabled`, `display_order`, `default_value`, `is_agent_field`) VALUES
(1, NULL, NULL, 'cat', '', 0, 0, 'Category', 'e.g., maybe Windows, Mac, Linux.', NULL, '', 1, 1, 0, NULL, 1);
");

$em->getConnection()->executeUpdate("
INSERT INTO `feedback_status_categories` (`id`, `status_type`, `title`, `display_order`) VALUES
(1, 'active', 'Gathering Feedback', 0),
(2, 'active', 'Planning', 0),
(3, 'active', 'Started', 0),
(4, 'active', 'Under Review', 0),
(5, 'closed', 'Completed', 0),
(6, 'closed', 'Duplicate', 0),
(7, 'closed', 'Declined', 0);
");

$em->getConnection()->executeUpdate("
INSERT INTO `feedback` (`id`, `status_category_id`, `category_id`, `person_id`, `language_id`, `hidden_status`, `validating`, `popularity`, `slug`, `title`, `content`, `view_count`, `total_rating`, `num_comments`, `num_ratings`, `status`, `date_created`, `date_published`) VALUES
(1, 5, 1, 1, NULL, 'validating', NULL, 0, 'example-suggestion', 'Example Suggestion', 'This is an example suggestion. Feel free to edit or delete it from the agent interface.', 0, 1, 0, 2, 'new', '2015-08-13 11:33:33', '2015-08-13 11:33:33'),
(2, 1, 1, 1, NULL, 'deleted', NULL, 0, 'Test feedback 1', 'Slug to feedback 1', 'Content of test feedback 1', 0, 3, 0, 4, 'hidden', '2015-08-01 00:00:00', NULL),
(3, 1, 2, 1, NULL, NULL, NULL, 0, 'Test feedback 2', 'Slug to feedback 2', 'Content of test feedback 2', 0, 5, 0, 6, 'active', '2015-08-02 00:00:00', NULL),
(4, 2, 3, 1, NULL, 'validating', NULL, 0, 'Test feedback 3', 'Slug to feedback 3', 'Content of test feedback 3', 0, 0, 0, 0, 'active', '2015-08-03 00:00:00', NULL),
(5, 1, 1, 1, NULL, 'spam', NULL, 0, 'Test feedback 4', 'Slug to feedback 4', 'Content of test feedback 4', 0, 1, 0, 1, 'hidden', '2015-08-04 00:00:00', NULL),
(6, 5, 1, 1, NULL, 'validating', NULL, 0, 'Test feedback 5', 'Slug to feedback 5', 'Content of test feedback 5', 0, 2, 0, 1, 'closed', '2015-08-05 00:00:00', NULL),
(7, 1, 2, 1, NULL, 'validating', NULL, 15, 'Test feedback 6', 'Slug to feedback 6', 'I''m trying to implement Infinite Scrolling on a gridview to speed up my web application, since the gridview is being bound to a sql query that returns thousands of records at start (it''s the client''s wish, and I can''t change that.)', 0, 3, 0, 1, 'new', '2015-08-10 00:00:00', NULL);
");

$em->getConnection()->executeUpdate("
INSERT INTO `custom_data_feedback` (`id`, `feedback_id`, `field_id`, `root_field_id`, `value`, `input`) VALUES
(1, 1, 1, NULL, 0, 'Windows'),
(2, 2, 1, NULL, 0, 'Linux'),
(3, 3, 1, NULL, 0, 'Linux'),
(4, 4, 1, NULL, 0, 'Mac');
");

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
$g['sys_name'] = 'everyone';
$em->persist($g);
$em->flush();
$USERGROUP_EVERYONE = $g;

##BEGIN:usergroups.register##
$g = new \Application\DeskPRO\Entity\Usergroup();
$g['title'] = $translate->phrase('agent.defaults.usergroup_registered');
$g['note'] = $translate->phrase('agent.defaults.usergroup_registered_note');
$g['sys_name'] = 'registered';
$em->persist($g);
$em->flush();
$USERGROUP_REG = $g;

##BEGIN:usergroups.agent_all##
$AGENTGROUP_ALL = new \Application\DeskPRO\Entity\Usergroup();
$AGENTGROUP_ALL['title'] = $translate->phrase('agent.defaults.usergroup_agent_all_perms');
$AGENTGROUP_ALL['note'] = $translate->phrase('agent.defaults.usergroup_agent_all_perms_note');
$AGENTGROUP_ALL['is_agent_group'] = true;
$AGENTGROUP_ALL['sys_name'] = 'agent_all_perms';
$em->persist($AGENTGROUP_ALL);
$em->flush();

##BEGIN:usergroups.agent_all_nondestructive##
$AGENTGROUP_ALL_ND = new \Application\DeskPRO\Entity\Usergroup();
$AGENTGROUP_ALL_ND['title'] = $translate->phrase('agent.defaults.usergroup_agent_all_non_destructive');
$AGENTGROUP_ALL_ND['note'] = $translate->phrase('agent.defaults.usergroup_agent_all_non_destructive_note');
$AGENTGROUP_ALL_ND['is_agent_group'] = true;
$AGENTGROUP_ALL_ND['sys_name'] = 'agent_all_safe_perms';
$em->persist($AGENTGROUP_ALL_ND);
$em->flush();

// Permissions for ND group
$ugid = $AGENTGROUP_ALL_ND->getId();
$em->getConnection()->executeUpdate("
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
");
