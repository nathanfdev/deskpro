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

// Statuses are done as part of FeedbackCatsStep so we can map id's
if (!$IMPORT_INSTALL) {
    // ensure gathering-feedback is always first, such that it's ID = 1
    foreach (array('gathering-feedback', 'planning', 'started', 'under-review') as $t) {
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


################################################################################
# Feedback
################################################################################

##BEGIN:create_feedback.default##
$DEFAULT_IDEA_CAT = new \Application\DeskPRO\Entity\FeedbackCategory();
$DEFAULT_IDEA_CAT['title'] = $translate->phrase('user.defaults.feedback_type_suggestion');
$em->persist($DEFAULT_IDEA_CAT);
$em->flush();

if (!$IMPORT_INSTALL) {
    $DEFAULT_IDEA = new \Application\DeskPRO\Entity\Feedback();
    $DEFAULT_IDEA->person = $AGENT;
    $DEFAULT_IDEA->title = $translate->phrase('user.defaults.feedback_example_title');
    $DEFAULT_IDEA->content = $translate->phrase('user.defaults.feedback_example_content');
    $DEFAULT_IDEA->status = 'active';
    $DEFAULT_IDEA->category = $DEFAULT_IDEA_CAT;
    $em->persist($DEFAULT_IDEA);
    $em->flush();
}

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

################################################################################
# More Feedback
################################################################################

$FEEDBACK_FEATURE = new \Application\DeskPRO\Entity\FeedbackCategory();
$FEEDBACK_FEATURE['title'] = $translate->phrase('user.defaults.feedback_type_feature-request');
$FEEDBACK_FEATURE->addUsergroup($USERGROUP_EVERYONE);
$em->persist($FEEDBACK_FEATURE);
$em->flush();

$FEEDBACK_BUG = new \Application\DeskPRO\Entity\FeedbackCategory();
$FEEDBACK_BUG['title'] = $translate->phrase('user.defaults.feedback_type_bug-report');
$FEEDBACK_BUG->addUsergroup($USERGROUP_EVERYONE);
$em->persist($FEEDBACK_BUG);
$em->flush();


########################################################
# TEMP DATA
########################################################

$faker = \Faker\Factory::create();

// the content publisher agent guy
$publisher = new \Application\DeskPRO\Entity\Person();
$publisher->name = 'Corporate Content';
$publisher->can_agent = true;
$publisher->is_agent = true;
$publisher->addEmailAddressString('content@publisher.com');

$em->persist($publisher);
$em->flush($publisher);


// a regular dude
$person = new \Application\DeskPRO\Entity\Person();
$person->name = 'Joe Kool';
$person->addEmailAddressString('joe@kool.com');
$person->setPassword('deskpro');

$em->persist($person);
$em->flush($person);

//////////////////////////////////////////////////////////////
// articles
//////////////////////////////////////////////////////////////

$ac = new \Application\DeskPRO\Entity\ArticleCategory();
$ac->title = 'Germany Info';
$ac->addUsergroup($USERGROUP_EVERYONE);
$em->persist($ac);

for ($i = 0; $i < 15; $i++) {
    $a = new \Application\DeskPRO\Entity\Article;
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000) . "<br><br>" . $faker->text(3000));
    $a->setCategories(array($ac));
    $a->setStatus(\Application\DeskPRO\Entity\ContentAbstract::STATUS_PUBLISHED);
    $a->setPerson($publisher);
    $em->persist($a);
}

$ac = new \Application\DeskPRO\Entity\ArticleCategory();
$ac->title = 'Finland Info';
$ac->addUsergroup($USERGROUP_EVERYONE);
$em->persist($ac);

for ($i = 0; $i < 15; $i++) {
    $a = new \Application\DeskPRO\Entity\Article;
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000) . "<br><br>" . $faker->text(3000));
    $a->setCategories(array($ac, $em->getRepository('DeskPRO:ArticleCategory')->find(1)));
    $a->setStatus(\Application\DeskPRO\Entity\ContentAbstract::STATUS_PUBLISHED);
    $a->setPerson($publisher);
    $em->persist($a);
}

$ac = new \Application\DeskPRO\Entity\ArticleCategory();
$ac->title = 'Japan Info';
$ac->addUsergroup($USERGROUP_EVERYONE);
$em->persist($ac);

for ($i = 0; $i < 15; $i++) {
    $a = new \Application\DeskPRO\Entity\Article;
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000) . "<br><br>" . $faker->text(3000));
    $a->setCategories(array($ac));
    $a->setStatus(\Application\DeskPRO\Entity\ContentAbstract::STATUS_PUBLISHED);
    $a->setPerson($publisher);
    $em->persist($a);
}

//////////////////////////////////////////////////////////////
// news
//////////////////////////////////////////////////////////////

$ac = new \Application\DeskPRO\Entity\NewsCategory();
$ac->title = 'Canada Info';
$ac->addUsergroup($USERGROUP_EVERYONE);
$em->persist($ac);

for ($i = 0; $i < 15; $i++) {
    $a = new \Application\DeskPRO\Entity\News;
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000) . "<br><br>" . $faker->text(3000));
    $a->setCategory($ac);
    $a->setStatus(\Application\DeskPRO\Entity\ContentAbstract::STATUS_PUBLISHED);
    $a->setPerson($publisher);
    $em->persist($a);
}

$ac = new \Application\DeskPRO\Entity\NewsCategory();
$ac->title = 'U.S. Info';
$ac->addUsergroup($USERGROUP_EVERYONE);
$em->persist($ac);

for ($i = 0; $i < 15; $i++) {
    $a = new \Application\DeskPRO\Entity\News;
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000) . "<br><br>" . $faker->text(3000));
    $a->setCategory($ac);
    $a->setStatus(\Application\DeskPRO\Entity\ContentAbstract::STATUS_PUBLISHED);
    $a->setPerson($publisher);
    $em->persist($a);
}

$ac = $em->getRepository('DeskPRO:NewsCategory')->find(1);

for ($i = 0; $i < 15; $i++) {
    $a = new \Application\DeskPRO\Entity\News;
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000) . "<br><br>" . $faker->text(3000));
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
        realpath(__DIR__ . '/../../../../../web/images/dp-logo-130.png'),
        'dp-logo-130.png',
        'image/png'
    )
        ;

    $blob->authcode = rand(0, 18) . rand(0, 18) . rand(0, 18) . rand(0, 18) . rand(0, 18) . rand(0, 18);

    return $blob;
}

$ac = new \Application\DeskPRO\Entity\DownloadCategory();
$ac->title = 'Canada Info';
$ac->addUsergroup($USERGROUP_EVERYONE);
$em->persist($ac);

for ($i = 0; $i < 15; $i++) {
    $blob = make_blob($em);
    $a = new \Application\DeskPRO\Entity\Download;
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000) . "<br><br>" . $faker->text(3000));
    $a->setCategory($ac);
    $a->setStatus(\Application\DeskPRO\Entity\ContentAbstract::STATUS_PUBLISHED);
    $a->setPerson($publisher);
    $a->setBlob($blob);
    $em->persist($a);
}

$ac = new \Application\DeskPRO\Entity\DownloadCategory();
$ac->title = 'U.S. Info';
$ac->addUsergroup($USERGROUP_EVERYONE);
$em->persist($ac);

for ($i = 0; $i < 15; $i++) {
    $blob = make_blob($em);
    $a = new \Application\DeskPRO\Entity\Download();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000) . "<br><br>" . $faker->text(3000));
    $a->setCategory($ac);
    $a->setStatus(\Application\DeskPRO\Entity\ContentAbstract::STATUS_PUBLISHED);
    $a->setPerson($publisher);
    $a->setBlob($blob);
    $em->persist($a);
}

$ac = $em->getRepository('DeskPRO:DownloadCategory')->find(1);

for ($i = 0; $i < 15; $i++) {
    $blob = make_blob($em);
    $a = new \Application\DeskPRO\Entity\Download;
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(2000) . "<br><br>" . $faker->text(3000));
    $a->setCategory($ac);
    $a->setStatus(\Application\DeskPRO\Entity\ContentAbstract::STATUS_PUBLISHED);
    $a->setPerson($publisher);
    $a->setBlob($blob);
    $em->persist($a);
}

//////////////////////////////////////////////////////////////
// feedback
//////////////////////////////////////////////////////////////

function rand_fb_status_pair(\Doctrine\ORM\EntityManager $em) {
    $array = array();

    $opts = array(
        \Application\DeskPRO\Entity\Feedback::STATUS_ACTIVE,
        \Application\DeskPRO\Entity\Feedback::STATUS_CLOSED
    );
    $array['status'] = $opts[rand(0, (count($opts) - 1))];

    $scs = $em->getRepository('DeskPRO:FeedbackStatusCategory')->findBy(array(
        'status_type' => $array['status'],
    ));

    $array['status_category'] = $scs[rand(0, (count($scs) - 1))];

    return $array;
}

for ($i = 0; $i < 30; $i++) {
    $a = new \Application\DeskPRO\Entity\Feedback();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(750) . "<br><br>" . $faker->text(1000));
    $a->setCategory($DEFAULT_IDEA_CAT);
    $fbinfo = rand_fb_status_pair($em);
    $a->setStatus($fbinfo['status']);
    $a->setStatusCategory($fbinfo['status_category']);
    $a->setPerson($publisher);
    $em->persist($a);
}

for ($i = 0; $i < 30; $i++) {
    $a = new \Application\DeskPRO\Entity\Feedback();
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(750) . "<br><br>" . $faker->text(1000));
    $a->setCategory($FEEDBACK_BUG);
    $fbinfo = rand_fb_status_pair($em);
    $a->setStatus($fbinfo['status']);
    $a->setStatusCategory($fbinfo['status_category']);
    $a->setPerson($publisher);
    $em->persist($a);
}

$ac = $em->getRepository('DeskPRO:DownloadCategory')->find(1);

for ($i = 0; $i < 30; $i++) {
    $a = new \Application\DeskPRO\Entity\Feedback;
    $a->setTitle(sprintf('%s %s %s', $faker->company, $faker->word, $faker->word));
    $a->setContent($faker->text(750) . "<br><br>" . $faker->text(1000));
    $a->setCategory($FEEDBACK_FEATURE);
    $fbinfo = rand_fb_status_pair($em);
    $a->setStatus($fbinfo['status']);
    $a->setStatusCategory($fbinfo['status_category']);
    $a->setPerson($publisher);
    $em->persist($a);
}


$em->flush();
