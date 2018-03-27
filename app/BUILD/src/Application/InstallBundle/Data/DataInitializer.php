<?php

/**
 * DeskPRO.
 */

namespace Application\InstallBundle\Data;

use Application\DeskPRO\App;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;

class DataInitializer
{
    /**
     * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    protected $container;

    /**
     * @var bool
     */
    protected $is_import = false;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    public $admin_user;

    public function __construct(DeskproContainer $container)
    {
        $this->container = $container;
    }

    public function setImportMode()
    {
        $this->is_import = true;
    }

    public function getAdminUser()
    {
        if ($this->admin_user) {
            return $this->admin_user;
        }

        $this->admin_user = $this->container->getEm()
                ->createQuery('SELECT p FROM DeskPRO:Person p WHERE p.is_agent = true AND p.can_admin = true ORDER BY p.id DESC')
                ->setMaxResults(1)
                ->getOneOrNullResult();

        return $this->admin_user;
    }

    public function run()
    {
        $this->container->getDb()->replace('settings', ['name' => 'core.admin_upgrade_notice', 'value' => DP_BUILD_TIME]);

        $this->runSearchIndex();
        $this->runInitPerms();
        $this->runInitAdminNotifications();
        $this->runInitDefaultSla();
        $this->runInitInitialData();
    }

    public function runInitPerms()
    {
        if ($this->is_import) {
            return;
        }

        // Initial agent has access to all deps
        $this->container->getDb()->insert('department_permissions', ['department_id' => 1, 'is_active' => 1, 'person_id' => 1, 'app' => 'tickets', 'name' => 'full', 'value' => 1]);
        $this->container->getDb()->insert('department_permissions', ['department_id' => 2, 'is_active' => 1, 'person_id' => 1, 'app' => 'tickets', 'name' => 'full', 'value' => 1]);
        $this->container->getDb()->insert('department_permissions', ['department_id' => 3, 'is_active' => 1, 'person_id' => 1, 'app' => 'chat', 'name' => 'full', 'value' => 1]);
        $this->container->getDb()->insert('department_permissions', ['department_id' => 4, 'is_active' => 1, 'person_id' => 1, 'app' => 'chat', 'name' => 'full', 'value' => 1]);

        // The everyone group has access to all deps too
        $this->container->getDb()->insert('department_permissions', ['department_id' => 1, 'is_active' => 1, 'usergroup_id' => 1, 'app' => 'tickets', 'name' => 'full', 'value' => 1]);
        $this->container->getDb()->insert('department_permissions', ['department_id' => 2, 'is_active' => 1, 'usergroup_id' => 1, 'app' => 'tickets', 'name' => 'full', 'value' => 1]);
        $this->container->getDb()->insert('department_permissions', ['department_id' => 3, 'is_active' => 1, 'usergroup_id' => 1, 'app' => 'chat', 'name' => 'full', 'value' => 1]);
        $this->container->getDb()->insert('department_permissions', ['department_id' => 4, 'is_active' => 1, 'usergroup_id' => 1, 'app' => 'chat', 'name' => 'full', 'value' => 1]);

        // The registered group has access to all deps too
        $this->container->getDb()->insert('department_permissions', ['department_id' => 1, 'is_active' => 1, 'usergroup_id' => 2, 'app' => 'tickets', 'name' => 'full', 'value' => 1]);
        $this->container->getDb()->insert('department_permissions', ['department_id' => 2, 'is_active' => 1, 'usergroup_id' => 2, 'app' => 'tickets', 'name' => 'full', 'value' => 1]);
        $this->container->getDb()->insert('department_permissions', ['department_id' => 3, 'is_active' => 1, 'usergroup_id' => 2, 'app' => 'chat', 'name' => 'full', 'value' => 1]);
        $this->container->getDb()->insert('department_permissions', ['department_id' => 4, 'is_active' => 1, 'usergroup_id' => 2, 'app' => 'chat', 'name' => 'full', 'value' => 1]);
    }

    public function runSearchIndex()
    {
        if (defined('DP_TESTS_RUNNING')) {
            return;
        }
        if ($this->is_import) {
            return;
        }
        $types = [
            ['article',   'articles',  'DeskPRO:Article'],
            ['download',  'downloads', 'DeskPRO:Download'],
            ['feedback',  'feedback',  'DeskPRO:Feedback'],
            ['news',      'news',      'DeskPRO:News'],
        ];

        foreach ($types as $t) {
            list($content_type, $table, $entity) = $t;
            $all_ids                             = $this->container->getDb()->fetchAllCol("SELECT id FROM $table ORDER BY id ASC");
            $batch                               = $this->container->getEm()->getRepository($entity)->getByIds($all_ids);
            if ($batch) {
                $this->container->getSearchAdapter()->updateObjectsInIndex($batch);
            }
        }
    }

    public function runInitAdminNotifications()
    {
        if ($this->is_import) {
            return;
        }

        $agent = \Application\DeskPRO\App::getOrm()->createQuery('SELECT p FROM DeskPRO:Person p WHERE p.can_admin = 1 ORDER BY p.id ASC')
            ->setMaxResults(1)
            ->getOneOrNullResult();

        // Possible to be in import mode and no admin
        if (!$agent) {
            return;
        }

//        for ($i = 1; $i <= 5; $i++) {
//            $this->container->getDb()->insert('ticket_filter_subscriptions', array(
//                'filter_id'             => $i,
//                'person_id'             => $agent->id,
//                'email_created'         => 1,
//                'email_new'             => 1,
//                'email_user_activity'   => 1,
//                'email_agent_activity'  => 1,
//                'email_agent_note'      => 1,
//                'email_property_change' => 1,
//                'alert_created'         => 1,
//                'alert_new'             => 1,
//                'alert_user_activity'   => 1,
//                'alert_agent_activity'  => 1,
//                'alert_property_change' => 1,
//            ));
//        }

        $prefs                                = [];
        $prefs['chat_message.email']          = 1;
        $prefs['login_attempt_fail.email']    = 1;
        $prefs['task_assign_self.email']      = 1;
        $prefs['task_assign_self.alert']      = 1;
        $prefs['task_assign_team.email']      = 1;
        $prefs['task_assign_team.alert']      = 1;
        $prefs['task_complete.email']         = 1;
        $prefs['task_complete.alert']         = 1;
        $prefs['task_due.email']              = 1;
        $prefs['task_due.alert']              = 1;
        $prefs['tweet_assign_self.email']     = 1;
        $prefs['tweet_assign_self.alert']     = 1;
        $prefs['tweet_assign_team.email']     = 1;
        $prefs['tweet_assign_team.alert']     = 1;
        $prefs['tweet_reply.email']           = 1;
        $prefs['tweet_reply.alert']           = 1;
        $prefs['tweet_new_dm.email']          = 1;
        $prefs['tweet_new_dm.alert']          = 1;
        $prefs['tweet_new_reply.email']       = 1;
        $prefs['tweet_new_reply.alert']       = 1;
        $prefs['tweet_new_mention.email']     = 1;
        $prefs['tweet_new_mention.alert']     = 1;
        $prefs['tweet_new_retweet.email']     = 1;
        $prefs['tweet_new_retweet.alert']     = 1;
        $prefs['new_feedback.email']          = 1;
        $prefs['new_feedback.alert']          = 1;
        $prefs['new_feedback_validate.email'] = 1;
        $prefs['new_feedback_validate.alert'] = 1;
        $prefs['new_comment.email']           = 1;
        $prefs['new_comment.alert']           = 1;
        $prefs['new_comment_validate.email']  = 1;
        $prefs['new_comment_validate.alert']  = 1;

        foreach ($prefs as $p => $v) {
            $this->container->getDb()->insert('people_prefs', [
                'person_id'   => $agent->id,
                'name'        => 'agent_notif.'.$p,
                'value_str'   => $v,
                'value_array' => 'N;',
            ]);
        }
    }

    public function runInitDefaultSla()
    {
    }

    public function runInitInitialData()
    {
        if ($this->is_import) {
            return;
        }
    }

    public static function newDefaultTicket($for_agent)
    {
        $department = App::getDataService('Department')->getDefaultTicketDepartment();

        $user = App::getOrm()->getRepository('DeskPRO:Person')->findOneByEmail('support@deskpro.com');
        if (!$user) {
            $user = Person::newContactPerson([
                'name'         => 'Christopher Padfield',
                'email'        => 'support@deskpro.com',
                'is_confirmed' => true,
            ]);
            $user->getPrimaryEmail()->is_validated = true;
            App::getOrm()->persist($user);
        }

        $ticket = new Ticket();
        $ticket->getTicketLogger()->recordExtra('is_install', true);
        $ticket->creation_system = Ticket::CREATED_WEB_PERSON;
        $ticket->person          = $user;
        $ticket->agent           = $for_agent;
        $ticket->department      = $department;
        $ticket->subject         = 'Welcome to DeskPRO';
        $ticket->status          = Ticket::STATUS_AWAITING_AGENT;
        $ticket->setProperty('send_reply_service', 'https://support.deskpro.com/api/open/tickets/new-ticket-message');
        $ticket->setProperty('allow_send_reply_service', true);

        App::getOrm()->persist($ticket);

        $agent_name = htmlspecialchars($for_agent->getDisplayName(), ENT_QUOTES, 'UTF-8');

        $message          = new TicketMessage();
        $message->person  = $user;
        $message->ticket  = $ticket;
        $message->message = <<<STR
Hello $agent_name,<br /><br />

Welcome to DeskPRO. This is a sample ticket that demonstrates how the system will look when a user submits a new ticket. Feel free to close or delete this whenever you want.<br /><br />

If you have any questions or run into any problems, you can simply reply to this ticket or you can always visit our helpdesk at <a href="http://support.deskpro.com/">support.deskpro.com</a>.<br /><br />

Best Regards,<br /><br />

Christopher Padfield<br />
<a href="http://www.deskpro.com/">www.deskpro.com</a>
STR;
        $ticket->addMessage($message);

        App::getOrm()->persist($message);
        App::getOrm()->flush();
    }
}
