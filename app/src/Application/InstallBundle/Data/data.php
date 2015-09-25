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

if (!defined('DP_ROOT')) {
    exit('No access');
}

################################################################################
# Language
################################################################################

##BEGIN:locale.language##
$l               = new \Application\DeskPRO\Entity\Language();
$l['title']      = $translate->phrase('user.defaults.language_english');
$l['locale']     = 'en_US';
$l['sys_name']   = 'default';
$l['flag_image'] = 'us.png';
$l['lang_code']  = 'eng';
$em->persist($l);
$em->flush();

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

// Statuses are done as part of FeedbackCatsStep so we can map id's
if (!$IMPORT_INSTALL) {
    // ensure gathering-feedback is always first, such that it's ID = 1
    foreach (array('gathering-feedback', 'planning', 'started', 'under-review') as $t) {
        $s              = new \Application\DeskPRO\Entity\FeedbackStatusCategory();
        $s->status_type = 'active';
        $s->title       = $translate->phrase('user.defaults.feedback_status_'.$t);
        $em->persist($s);
    }

    foreach (array('completed', 'duplicate', 'declined') as $t) {
        $s              = new \Application\DeskPRO\Entity\FeedbackStatusCategory();
        $s->status_type = 'closed';
        $s->title       = $translate->phrase('user.defaults.feedback_status_'.$t);
        $em->persist($s);
    }
    $em->flush();
}

################################################################################
# Feedback
################################################################################

##BEGIN:create_feedback.default##
$DEFAULT_IDEA_CAT          = new \Application\DeskPRO\Entity\FeedbackCategory();
$DEFAULT_IDEA_CAT['title'] = $translate->phrase('user.defaults.feedback_type_suggestion');
$em->persist($DEFAULT_IDEA_CAT);
$em->flush();

if (!$IMPORT_INSTALL) {
    $DEFAULT_IDEA           = new \Application\DeskPRO\Entity\Feedback();
    $DEFAULT_IDEA->person   = $AGENT;
    $DEFAULT_IDEA->title    = $translate->phrase('user.defaults.feedback_example_title');
    $DEFAULT_IDEA->content  = $translate->phrase('user.defaults.feedback_example_content');
    $DEFAULT_IDEA->status   = 'active';
    $DEFAULT_IDEA->category = $DEFAULT_IDEA_CAT;
    $em->persist($DEFAULT_IDEA);
    $em->flush();
}

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

################################################################################
# More Feedback
################################################################################

$FEEDBACK_FEATURE          = new \Application\DeskPRO\Entity\FeedbackCategory();
$FEEDBACK_FEATURE['title'] = $translate->phrase('user.defaults.feedback_type_feature-request');
$FEEDBACK_FEATURE->addUsergroup($USERGROUP_EVERYONE);
$em->persist($FEEDBACK_FEATURE);
$em->flush();

$FEEDBACK_BUG          = new \Application\DeskPRO\Entity\FeedbackCategory();
$FEEDBACK_BUG['title'] = $translate->phrase('user.defaults.feedback_type_bug-report');
$FEEDBACK_BUG->addUsergroup($USERGROUP_EVERYONE);
$em->persist($FEEDBACK_BUG);
$em->flush();

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

function make_blob(\Doctrine\ORM\EntityManager $em)
{
    $storage = new \Application\DeskPRO\BlobStorage\DeskproBlobStorage($em);

    $blob = $storage->createBlobRecordFromFile(
        realpath(__DIR__.'/../../../../../web/images/dp-logo-130.png'),
        'dp-logo-130.png',
        'image/png'
    )
        ;

    $blob->authcode = rand(0, 18).rand(0, 18).rand(0, 18).rand(0, 18).rand(0, 18).rand(0, 18);

    return $blob;
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

function rand_fb_status_pair(\Doctrine\ORM\EntityManager $em)
{
    $array = array();

    $opts = array(
        \Application\DeskPRO\Entity\Feedback::STATUS_ACTIVE,
        \Application\DeskPRO\Entity\Feedback::STATUS_CLOSED,
    );
    $array['status'] = $opts[rand(0, (count($opts) - 1))];

    $scs = $em->getRepository('DeskPRO:FeedbackStatusCategory')->findBy(array(
        'status_type' => $array['status'],
    ));

    $array['status_category'] = $scs[rand(0, (count($scs) - 1))];

    return $array;
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

//////////////////////////////////////////////////////////////
// tickets
//////////////////////////////////////////////////////////////


$em->getConnection()->executeUpdate("
INSERT INTO `tickets` (`id`, `parent_ticket_id`, `language_id`, `department_id`, `category_id`, `priority_id`, `workflow_id`, `product_id`, `person_id`, `person_email_id`, `person_email_validating_id`, `agent_id`, `agent_team_id`, `organization_id`, `linked_chat_id`, `email_account_id`, `locked_by_agent`, `ref`, `auth`, `sent_to_address`, `email_account_address`, `creation_system`, `creation_system_option`, `ticket_hash`, `status`, `hidden_status`, `validating`, `is_hold`, `urgency`, `count_agent_replies`, `count_user_replies`, `feedback_rating`, `date_feedback_rating`, `date_created`, `date_resolved`, `date_archived`, `date_first_agent_assign`, `date_first_agent_reply`, `date_last_agent_reply`, `date_last_user_reply`, `date_agent_waiting`, `date_user_waiting`, `date_status`, `total_user_waiting`, `total_to_first_reply`, `date_locked`, `has_attachments`, `subject`, `original_subject`, `properties`, `worst_sla_status`, `waiting_times`)
VALUES
	(1, NULL, NULL, 2, NULL, NULL, NULL, NULL, 3, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 'UBDD-6704-JMBR', '9KG35Z4RW4N9Z9Z', '', '', 'unknown', '', 'none', 'awaiting_user', NULL, NULL, 0, 1, 1, 1, NULL, NULL, '2015-09-06 15:23:35', NULL, NULL, '2015-09-06 15:28:39', '2015-09-06 15:28:54', '2015-09-06 15:28:55', '2015-09-06 15:23:35', '2015-09-06 15:28:54', NULL, '2015-09-06 15:28:55', 0, 319, NULL, 0, 'Help me, please!', 'Help me, please!', X'4E3B', NULL, X'613A303A7B7D'),
	(2, NULL, NULL, 1, NULL, NULL, NULL, NULL, 3, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'RERK-2006-MBNY', 'TCY84CRA7M3K65J', '', '', 'unknown', '', 'none', 'resolved', NULL, NULL, 0, 1, 1, 1, NULL, NULL, '2015-09-06 15:24:36', '2015-09-06 15:29:22', NULL, '2015-09-06 15:29:22', '2015-09-06 15:29:22', '2015-09-06 15:29:23', '2015-09-06 15:24:36', NULL, NULL, '2015-09-06 15:29:23', 0, 286, NULL, 0, 'You\'ll resolve this quickly', 'You\'ll resolve this quickly', X'4E3B', NULL, X'613A303A7B7D'),
	(3, NULL, NULL, 1, NULL, NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'OOZU-5727-YXGL', 'D94AGKH6HBG9RXJ', '', '', 'unknown', '', 'none', 'awaiting_agent', NULL, NULL, 0, 1, 0, 1, NULL, NULL, '2015-09-06 15:25:02', NULL, NULL, NULL, NULL, NULL, '2015-09-06 15:25:02', NULL, NULL, '2015-09-06 15:25:02', 0, 0, NULL, 0, 'How do I upload an avatar?', 'How do I upload an avatar?', X'4E3B', NULL, X'613A303A7B7D');
");
//$em->getConnection()->executeUpdate("
//INSERT INTO `ticket_proc_log` (`id`, `ticket_id`, `blob_id`, `date_created`)
//VALUES
//	(1, 1, 513, '2015-09-06 15:23:35'),
//	(2, 2, 514, '2015-09-06 15:24:37'),
//	(3, 3, 515, '2015-09-06 15:25:02'),
//	(4, 1, 518, '2015-09-06 15:28:39'),
//	(5, 1, 521, '2015-09-06 15:28:55'),
//	(6, 2, 524, '2015-09-06 15:29:23');
//
//");
$em->getConnection()->executeUpdate("
INSERT INTO `ticket_access_codes` (`id`, `ticket_id`, `person_id`, `auth`)
VALUES
	(1, 1, 3, 'XMSHNPNDR44DAS8'),
	(2, 2, 3, 'Q58A3TBACYTBCC4'),
	(3, 3, 3, '2J9NK5A5667YACR'),
	(4, 1, 1, '386J2JYGZTDHG94'),
	(5, 2, 1, 'AMN6M3APA7CBA96');

");
$x = <<<EOF
INSERT INTO `tickets_logs` (`id`, `parent_id`, `ticket_id`, `person_id`, `sla_id`, `action_type`, `id_object`, `id_before`, `id_after`, `trigger_id`, `escalation_id`, `sla_status`, `details`, `date_created`)
VALUES
	(1, NULL, 1, 3, NULL, 'action_starter', NULL, NULL, NULL, NULL, NULL, NULL, X'613A363A7B733A353A226576656E74223B733A393A226E65777469636B6574223B733A31323A226576656E745F6D6574686F64223B733A363A22706F7274616C223B733A31353A226576656E745F706572666F726D6572223B733A343A2275736572223B733A393A22706572736F6E5F6964223B693A333B733A31313A22706572736F6E5F6E616D65223B733A383A224A6F65204B6F6F6C223B733A31323A22706572736F6E5F656D61696C223B733A31313A226A6F65406A6F652E636F6D223B7D', '2015-09-06 15:23:35'),
	(2, 1, 1, 3, NULL, 'ticket_created', NULL, NULL, 1, NULL, NULL, NULL, X'613A333A7B733A393A227469636B65745F6964223B693A313B733A31353A226576656E745F706572666F726D6572223B733A343A2275736572223B733A31323A226576656E745F6D6574686F64223B733A363A22706F7274616C223B7D', '2015-09-06 15:23:35'),
	(3, 1, 1, 3, NULL, 'changed_person', NULL, NULL, 3, NULL, NULL, NULL, X'613A373A7B733A393A2269645F6265666F7265223B4E3B733A31333A226F6C645F706572736F6E5F6964223B4E3B733A31353A226F6C645F706572736F6E5F6E616D65223B4E3B733A31363A226F6C645F706572736F6E5F656D61696C223B4E3B733A31333A226E65775F706572736F6E5F6964223B693A333B733A31353A226E65775F706572736F6E5F6E616D65223B733A383A224A6F65204B6F6F6C223B733A31363A226E65775F706572736F6E5F656D61696C223B733A31313A226A6F65406A6F652E636F6D223B7D', '2015-09-06 15:23:35'),
	(4, 1, 1, 3, NULL, 'message_created', NULL, NULL, 1, NULL, NULL, NULL, X'613A363A7B733A31303A226D6573736167655F6964223B693A313B733A31353A226372656174696F6E5F73797374656D223B733A333A22776562223B733A31333A2269735F6167656E745F6E6F7465223B623A303B733A31363A2269735F6167656E745F6D657373616765223B623A303B733A31303A2269705F61646472657373223B4E3B733A353A22656D61696C223B4E3B7D', '2015-09-06 15:23:35'),
	(5, 1, 1, 3, NULL, 'changed_department', NULL, NULL, 2, NULL, NULL, NULL, X'613A353A7B733A393A2269645F6265666F7265223B4E3B733A31373A226F6C645F6465706172746D656E745F6964223B4E3B733A32303A226F6C645F6465706172746D656E745F7469746C65223B4E3B733A31373A226E65775F6465706172746D656E745F6964223B693A323B733A32303A226E65775F6465706172746D656E745F7469746C65223B733A353A2253616C6573223B7D', '2015-09-06 15:23:35'),
	(6, 1, 1, 3, NULL, 'changed_subject', NULL, NULL, NULL, NULL, NULL, NULL, X'613A323A7B733A31313A226F6C645F7375626A656374223B4E3B733A31313A226E65775F7375626A656374223B733A31363A2248656C70206D652C20706C6561736521223B7D', '2015-09-06 15:23:35'),
	(7, NULL, 2, 3, NULL, 'action_starter', NULL, NULL, NULL, NULL, NULL, NULL, X'613A363A7B733A353A226576656E74223B733A393A226E65777469636B6574223B733A31323A226576656E745F6D6574686F64223B733A363A22706F7274616C223B733A31353A226576656E745F706572666F726D6572223B733A343A2275736572223B733A393A22706572736F6E5F6964223B693A333B733A31313A22706572736F6E5F6E616D65223B733A383A224A6F65204B6F6F6C223B733A31323A22706572736F6E5F656D61696C223B733A31313A226A6F65406A6F652E636F6D223B7D', '2015-09-06 15:24:36'),
	(8, 7, 2, 3, NULL, 'ticket_created', NULL, NULL, 2, NULL, NULL, NULL, X'613A333A7B733A393A227469636B65745F6964223B693A323B733A31353A226576656E745F706572666F726D6572223B733A343A2275736572223B733A31323A226576656E745F6D6574686F64223B733A363A22706F7274616C223B7D', '2015-09-06 15:24:36'),
	(9, 7, 2, 3, NULL, 'changed_person', NULL, NULL, 3, NULL, NULL, NULL, X'613A373A7B733A393A2269645F6265666F7265223B4E3B733A31333A226F6C645F706572736F6E5F6964223B4E3B733A31353A226F6C645F706572736F6E5F6E616D65223B4E3B733A31363A226F6C645F706572736F6E5F656D61696C223B4E3B733A31333A226E65775F706572736F6E5F6964223B693A333B733A31353A226E65775F706572736F6E5F6E616D65223B733A383A224A6F65204B6F6F6C223B733A31363A226E65775F706572736F6E5F656D61696C223B733A31313A226A6F65406A6F652E636F6D223B7D', '2015-09-06 15:24:36'),
	(10, 7, 2, 3, NULL, 'message_created', NULL, NULL, 2, NULL, NULL, NULL, X'613A363A7B733A31303A226D6573736167655F6964223B693A323B733A31353A226372656174696F6E5F73797374656D223B733A333A22776562223B733A31333A2269735F6167656E745F6E6F7465223B623A303B733A31363A2269735F6167656E745F6D657373616765223B623A303B733A31303A2269705F61646472657373223B4E3B733A353A22656D61696C223B4E3B7D', '2015-09-06 15:24:36'),
	(11, 7, 2, 3, NULL, 'changed_department', NULL, NULL, 2, NULL, NULL, NULL, X'613A353A7B733A393A2269645F6265666F7265223B4E3B733A31373A226F6C645F6465706172746D656E745F6964223B4E3B733A32303A226F6C645F6465706172746D656E745F7469746C65223B4E3B733A31373A226E65775F6465706172746D656E745F6964223B693A323B733A32303A226E65775F6465706172746D656E745F7469746C65223B733A353A2253616C6573223B7D', '2015-09-06 15:24:36'),
	(12, 7, 2, 3, NULL, 'changed_department', NULL, 2, 1, NULL, NULL, NULL, X'613A343A7B733A31373A226F6C645F6465706172746D656E745F6964223B693A323B733A32303A226F6C645F6465706172746D656E745F7469746C65223B733A353A2253616C6573223B733A31373A226E65775F6465706172746D656E745F6964223B693A313B733A32303A226E65775F6465706172746D656E745F7469746C65223B733A373A22537570706F7274223B7D', '2015-09-06 15:24:36'),
	(13, 7, 2, 3, NULL, 'changed_subject', NULL, NULL, NULL, NULL, NULL, NULL, X'613A323A7B733A31313A226F6C645F7375626A656374223B4E3B733A31313A226E65775F7375626A656374223B733A32373A22596F75276C6C207265736F6C7665207468697320717569636B6C79223B7D', '2015-09-06 15:24:36'),
	(14, NULL, 3, 3, NULL, 'action_starter', NULL, NULL, NULL, NULL, NULL, NULL, X'613A363A7B733A353A226576656E74223B733A393A226E65777469636B6574223B733A31323A226576656E745F6D6574686F64223B733A363A22706F7274616C223B733A31353A226576656E745F706572666F726D6572223B733A343A2275736572223B733A393A22706572736F6E5F6964223B693A333B733A31313A22706572736F6E5F6E616D65223B733A383A224A6F65204B6F6F6C223B733A31323A22706572736F6E5F656D61696C223B733A31313A226A6F65406A6F652E636F6D223B7D', '2015-09-06 15:25:02'),
	(15, 14, 3, 3, NULL, 'ticket_created', NULL, NULL, 3, NULL, NULL, NULL, X'613A333A7B733A393A227469636B65745F6964223B693A333B733A31353A226576656E745F706572666F726D6572223B733A343A2275736572223B733A31323A226576656E745F6D6574686F64223B733A363A22706F7274616C223B7D', '2015-09-06 15:25:02'),
	(16, 14, 3, 3, NULL, 'changed_person', NULL, NULL, 3, NULL, NULL, NULL, X'613A373A7B733A393A2269645F6265666F7265223B4E3B733A31333A226F6C645F706572736F6E5F6964223B4E3B733A31353A226F6C645F706572736F6E5F6E616D65223B4E3B733A31363A226F6C645F706572736F6E5F656D61696C223B4E3B733A31333A226E65775F706572736F6E5F6964223B693A333B733A31353A226E65775F706572736F6E5F6E616D65223B733A383A224A6F65204B6F6F6C223B733A31363A226E65775F706572736F6E5F656D61696C223B733A31313A226A6F65406A6F652E636F6D223B7D', '2015-09-06 15:25:02'),
	(17, 14, 3, 3, NULL, 'message_created', NULL, NULL, 3, NULL, NULL, NULL, X'613A363A7B733A31303A226D6573736167655F6964223B693A333B733A31353A226372656174696F6E5F73797374656D223B733A333A22776562223B733A31333A2269735F6167656E745F6E6F7465223B623A303B733A31363A2269735F6167656E745F6D657373616765223B623A303B733A31303A2269705F61646472657373223B4E3B733A353A22656D61696C223B4E3B7D', '2015-09-06 15:25:02'),
	(18, 14, 3, 3, NULL, 'changed_department', NULL, NULL, 2, NULL, NULL, NULL, X'613A353A7B733A393A2269645F6265666F7265223B4E3B733A31373A226F6C645F6465706172746D656E745F6964223B4E3B733A32303A226F6C645F6465706172746D656E745F7469746C65223B4E3B733A31373A226E65775F6465706172746D656E745F6964223B693A323B733A32303A226E65775F6465706172746D656E745F7469746C65223B733A353A2253616C6573223B7D', '2015-09-06 15:25:02'),
	(19, 14, 3, 3, NULL, 'changed_department', NULL, 2, 1, NULL, NULL, NULL, X'613A343A7B733A31373A226F6C645F6465706172746D656E745F6964223B693A323B733A32303A226F6C645F6465706172746D656E745F7469746C65223B733A353A2253616C6573223B733A31373A226E65775F6465706172746D656E745F6964223B693A313B733A32303A226E65775F6465706172746D656E745F7469746C65223B733A373A22537570706F7274223B7D', '2015-09-06 15:25:02'),
	(20, 14, 3, 3, NULL, 'changed_subject', NULL, NULL, NULL, NULL, NULL, NULL, X'613A323A7B733A31313A226F6C645F7375626A656374223B4E3B733A31313A226E65775F7375626A656374223B733A32363A22486F7720646F20492075706C6F616420616E206176617461723F223B7D', '2015-09-06 15:25:02'),
	(21, NULL, 1, 1, NULL, 'action_starter', NULL, NULL, NULL, NULL, NULL, NULL, X'613A363A7B733A353A226576656E74223B733A363A22757064617465223B733A31323A226576656E745F6D6574686F64223B733A333A22776562223B733A31353A226576656E745F706572666F726D6572223B733A353A226167656E74223B733A393A22706572736F6E5F6964223B693A313B733A31313A22706572736F6E5F6E616D65223B733A31313A2241646D696E2041646D696E223B733A31323A22706572736F6E5F656D61696C223B733A31353A2261646D696E40656D61696C2E636F6D223B7D', '2015-09-06 15:28:39'),
	(22, 21, 1, 1, NULL, 'changed_agent', NULL, NULL, 2, NULL, NULL, NULL, X'613A373A7B733A393A2269645F6265666F7265223B4E3B733A31323A226F6C645F6167656E745F6964223B4E3B733A31343A226F6C645F6167656E745F6E616D65223B4E3B733A31353A226F6C645F6167656E745F656D61696C223B4E3B733A31323A226E65775F6167656E745F6964223B693A323B733A31343A226E65775F6167656E745F6E616D65223B733A31373A22436F72706F7261746520436F6E74656E74223B733A31353A226E65775F6167656E745F656D61696C223B733A32313A22636F6E74656E74407075626C69736865722E636F6D223B7D', '2015-09-06 15:28:39'),
	(23, NULL, 1, 1, NULL, 'action_starter', NULL, NULL, NULL, NULL, NULL, NULL, X'613A363A7B733A353A226576656E74223B733A383A226E65777265706C79223B733A31323A226576656E745F6D6574686F64223B733A333A22776562223B733A31353A226576656E745F706572666F726D6572223B733A353A226167656E74223B733A393A22706572736F6E5F6964223B693A313B733A31313A22706572736F6E5F6E616D65223B733A31313A2241646D696E2041646D696E223B733A31323A22706572736F6E5F656D61696C223B733A31353A2261646D696E40656D61696C2E636F6D223B7D', '2015-09-06 15:28:55'),
	(24, 23, 1, 1, NULL, 'message_created', NULL, NULL, 4, NULL, NULL, NULL, X'613A363A7B733A31303A226D6573736167655F6964223B693A343B733A31353A226372656174696F6E5F73797374656D223B733A31363A227765622E6167656E742E706F7274616C223B733A31333A2269735F6167656E745F6E6F7465223B623A303B733A31363A2269735F6167656E745F6D657373616765223B623A313B733A31303A2269705F61646472657373223B733A393A223132372E302E302E31223B733A353A22656D61696C223B4E3B7D', '2015-09-06 15:28:55'),
	(25, 23, 1, 1, NULL, 'changed_status', NULL, 100, 110, NULL, NULL, NULL, X'613A323A7B733A31303A226F6C645F737461747573223B733A31343A226177616974696E675F6167656E74223B733A31303A226E65775F737461747573223B733A31333A226177616974696E675F75736572223B7D', '2015-09-06 15:28:55'),
	(26, 23, 1, 1, NULL, 'ticket_email', NULL, NULL, 1, 7, NULL, NULL, X'613A393A7B733A393A22757365725F6D6F6465223B733A343A2275736572223B733A373A22746F5F6E616D65223B733A383A224A6F65204B6F6F6C223B733A383A22746F5F656D61696C223B733A31313A226A6F65406A6F652E636F6D223B733A393A2263635F656D61696C73223B613A303A7B7D733A393A2266726F6D5F6E616D65223B733A31313A2241646D696E2041646D696E223B733A31303A2266726F6D5F656D61696C223B733A31353A2261646D696E40656D61696C2E636F6D223B733A383A2274656D706C617465223B733A35303A224465736B50524F3A656D61696C735F757365723A7469636B65742D7265706C792D62796167656E742E68746D6C2E74776967223B733A31383A2273656E646D61696C5F736F757263655F6964223B733A313A2231223B733A31333A22747269676765725F7469746C65223B733A33303A2253656E642075736572206E6577207265706C792066726F6D206167656E74223B7D', '2015-09-06 15:28:55'),
	(27, NULL, 2, 1, NULL, 'action_starter', NULL, NULL, NULL, NULL, NULL, NULL, X'613A363A7B733A353A226576656E74223B733A383A226E65777265706C79223B733A31323A226576656E745F6D6574686F64223B733A333A22776562223B733A31353A226576656E745F706572666F726D6572223B733A353A226167656E74223B733A393A22706572736F6E5F6964223B693A313B733A31313A22706572736F6E5F6E616D65223B733A31313A2241646D696E2041646D696E223B733A31323A22706572736F6E5F656D61696C223B733A31353A2261646D696E40656D61696C2E636F6D223B7D', '2015-09-06 15:29:23'),
	(28, 27, 2, 1, NULL, 'message_created', NULL, NULL, 5, NULL, NULL, NULL, X'613A363A7B733A31303A226D6573736167655F6964223B693A353B733A31353A226372656174696F6E5F73797374656D223B733A31363A227765622E6167656E742E706F7274616C223B733A31333A2269735F6167656E745F6E6F7465223B623A303B733A31363A2269735F6167656E745F6D657373616765223B623A313B733A31303A2269705F61646472657373223B733A393A223132372E302E302E31223B733A353A22656D61696C223B4E3B7D', '2015-09-06 15:29:23'),
	(29, 27, 2, 1, NULL, 'changed_agent', NULL, NULL, 1, NULL, NULL, NULL, X'613A373A7B733A393A2269645F6265666F7265223B4E3B733A31323A226F6C645F6167656E745F6964223B4E3B733A31343A226F6C645F6167656E745F6E616D65223B4E3B733A31353A226F6C645F6167656E745F656D61696C223B4E3B733A31323A226E65775F6167656E745F6964223B693A313B733A31343A226E65775F6167656E745F6E616D65223B733A31313A2241646D696E2041646D696E223B733A31353A226E65775F6167656E745F656D61696C223B733A31353A2261646D696E40656D61696C2E636F6D223B7D', '2015-09-06 15:29:23'),
	(30, 27, 2, 1, NULL, 'changed_status', NULL, 100, 200, NULL, NULL, NULL, X'613A323A7B733A31303A226F6C645F737461747573223B733A31343A226177616974696E675F6167656E74223B733A31303A226E65775F737461747573223B733A383A227265736F6C766564223B7D', '2015-09-06 15:29:23'),
	(31, 27, 2, 1, NULL, 'ticket_email', NULL, NULL, 2, 7, NULL, NULL, X'613A393A7B733A393A22757365725F6D6F6465223B733A343A2275736572223B733A373A22746F5F6E616D65223B733A383A224A6F65204B6F6F6C223B733A383A22746F5F656D61696C223B733A31313A226A6F65406A6F652E636F6D223B733A393A2263635F656D61696C73223B613A303A7B7D733A393A2266726F6D5F6E616D65223B733A31313A2241646D696E2041646D696E223B733A31303A2266726F6D5F656D61696C223B733A31353A2261646D696E40656D61696C2E636F6D223B733A383A2274656D706C617465223B733A35303A224465736B50524F3A656D61696C735F757365723A7469636B65742D7265706C792D62796167656E742E68746D6C2E74776967223B733A31383A2273656E646D61696C5F736F757263655F6964223B733A313A2232223B733A31333A22747269676765725F7469746C65223B733A33303A2253656E642075736572206E6577207265706C792066726F6D206167656E74223B7D', '2015-09-06 15:29:23');
EOF;
$em->getConnection()->executeUpdate(
    $x
);
$em->getConnection()->executeUpdate("
INSERT INTO `tickets_messages` (`id`, `ticket_id`, `person_id`, `email_source_id`, `message_translated_id`, `date_created`, `is_agent_note`, `creation_system`, `ip_address`, `hostname`, `geo_country`, `email`, `message_hash`, `message`, `message_full`, `message_raw`, `lang_code`, `show_full_hint`)
VALUES
	(1, 1, 3, NULL, NULL, '2015-09-06 15:23:35', 0, 'web', '', '', NULL, '', '10e28bdd3294c6352c72418f4defe6da5d749e83', 'I have a question about the pricing. Please tell me...<br />\n<br />\nI have a question about the pricing. Please tell me...I have a question about the pricing. Please tell me...I have a question about the pricing. Please tell me...I have a question about the pricing. Please tell me...I have a question about the pricing. Please tell me...I have a question about the pricing. Please tell me...I have a question about the pricing. Please tell me...I have a question about the pricing. Please tell me...I have a question about the pricing. Please tell me...I have a question about the pricing. Please tell me...', NULL, NULL, NULL, 0),
	(2, 2, 3, NULL, NULL, '2015-09-06 15:24:36', 0, 'web', '', '', NULL, '', 'd2064b6e22cfb660239da156727ece53ce82a40e', 'This should be resolved rather quickly. I have a problem where I can&#039;t close my browser window.', NULL, NULL, NULL, 0),
	(3, 3, 3, NULL, NULL, '2015-09-06 15:25:02', 0, 'web', '', '', NULL, '', '14111802db031010c57ffd2bea0fc2146f92d619', 'I&#039;d like to see my face on here. How can I do that?', NULL, NULL, NULL, 0),
	(4, 1, 1, NULL, NULL, '2015-09-06 15:28:54', 0, 'web.agent.portal', '127.0.0.1', '', NULL, '', 'ee7fb19e19afc02bb208e6e980e33f8980c9e958', 'Consider yourself helped', NULL, NULL, NULL, 0),
	(5, 2, 1, NULL, NULL, '2015-09-06 15:29:22', 0, 'web.agent.portal', '127.0.0.1', '', NULL, '', 'ef825ffa985cbbe44835093b53739f0029e63eee', 'Click X. <br />\n<br>Have a good day.', NULL, NULL, NULL, 0);

");
$em->getConnection()->executeUpdate("
INSERT INTO `tickets_search_active` (`id`, `ref`, `language_id`, `department_id`, `category_id`, `workflow_id`, `priority_id`, `product_id`, `person_id`, `person_email_id`, `agent_id`, `agent_team_id`, `organization_id`, `sent_to_address`, `creation_system`, `creation_system_option`, `status`, `is_hold`, `urgency`, `feedback_rating`, `date_feedback_rating`, `date_created`, `date_resolved`, `date_first_agent_assign`, `date_first_agent_reply`, `date_last_agent_reply`, `date_last_user_reply`, `date_agent_waiting`, `date_user_waiting`, `date_status`, `total_user_waiting`, `total_to_first_reply`, `subject`, `original_subject`)
VALUES
	(1, 'UBDD-6704-JMBR', NULL, 2, NULL, NULL, NULL, NULL, 3, NULL, 2, NULL, NULL, '', 'unknown', '', 'awaiting_user', 0, 1, NULL, NULL, '2015-09-06 15:23:35', NULL, '2015-09-06 15:28:39', '2015-09-06 15:28:54', '2015-09-06 15:28:55', '2015-09-06 15:23:35', '2015-09-06 15:28:54', NULL, '2015-09-06 15:28:55', 0, 319, 'Help me, please!', 'Help me, please!'),
	(2, 'RERK-2006-MBNY', NULL, 1, NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, NULL, '', 'unknown', '', 'resolved', 0, 1, NULL, NULL, '2015-09-06 15:24:36', '2015-09-06 15:29:22', '2015-09-06 15:29:22', '2015-09-06 15:29:22', '2015-09-06 15:29:23', '2015-09-06 15:24:36', NULL, NULL, '2015-09-06 15:29:23', 0, 286, 'You\'ll resolve this quickly', 'You\'ll resolve this quickly'),
	(3, 'OOZU-5727-YXGL', NULL, 1, NULL, NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, '', 'unknown', '', 'awaiting_agent', 0, 1, NULL, NULL, '2015-09-06 15:25:02', NULL, NULL, NULL, NULL, '2015-09-06 15:25:02', NULL, NULL, '2015-09-06 15:25:02', 0, 0, 'How do I upload an avatar?', 'How do I upload an avatar?');

");

$em->flush();
