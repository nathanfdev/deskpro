<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO.
 */

namespace DpTestSrc\TestBundle\DataSet;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\FeedbackComment;
use Application\DeskPRO\Entity\LabelFeedback;
use DeskPRO\Bundle\AppBundle\Entity\Task;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Usersource;
use Application\InstallBundle\Data\DefaultDataProcessor;
use DeskPRO\Bundle\AppBundle\Entity\TaskAssignment;
use DpTestSrc\TestBundle\UserDetailsRepo;

class ApiDb extends AbstractDbSet
{
    public function getId()
    {
        return 'api';
    }

    /**
     * Install default/empty data.
     *
     * @return int
     */
    protected function installSet()
    {
        static $count = 0;

        $em = $this->getEm();

        #------------------------------
        # Init data
        #------------------------------

        $admin = $this->addUser(
            UserDetailsRepo::ADMIN_FIRST_NAME,
            UserDetailsRepo::ADMIN_LAST_NAME,
            UserDetailsRepo::ADMIN_EMAIL,
            UserDetailsRepo::ADMIN_PASS,
            true,
            true
        );

        $agent = $this->addUser(
            UserDetailsRepo::AGENT_FIRST_NAME,
            UserDetailsRepo::AGENT_LAST_NAME,
            UserDetailsRepo::AGENT_EMAIL,
            UserDetailsRepo::AGENT_PASS,
            true,
            false
        );

        $user = $this->addUser(
            UserDetailsRepo::USER_FIRST_NAME,
            UserDetailsRepo::USER_LAST_NAME,
            UserDetailsRepo::USER_EMAIL,
            UserDetailsRepo::USER_PASS,
            false,
            false
        );


        // this will be refactored into a better "entity creator" once the api data set needs more elaborate data
        // we need a brand and some deps, and some other entities
        $dep1 = new Department;
        $dep1->title = "sales";
        $dep2 = new Department;
        $dep2->title = "support";
        $brand = new Brand();
        $team = new AgentTeam;
        $team->name = "test team";
        $ticket_def = new CustomDefTicket();
        $ticket_def->title = "def";

        // Create a basic task
        $task = new Task($admin);
        $task->setTitle("A demo task");
        $taskAssignment = new TaskAssignment();
        $taskAssignment->setTask($task);
        $taskAssignment->setPerson($admin);

        $unassignedTask = new Task($admin);
        $unassignedTask->setTitle("An unassigned task");

        // Create a new knowledge base article
        $article = new Article();
        $article->slug = 'test';
        $article->title = 'A test article';
        $article->content = 'This is a test article';
        $article->view_count = 0;
        $article->total_rating = 0;
        $article->num_comments = 0;
        $article->num_ratings = 0;
        $article->status = 'published';
        $article->date_created = new \DateTime();

        // Persist them in the entity manager
        $em->persist($ticket_def);
        $em->persist($team);
        $em->persist($dep1);
        $em->persist($dep2);
        $em->persist($brand);
        $em->persist($task);
        $em->persist($taskAssignment);
        $em->persist($unassignedTask);
        $em->persist($article);
        $em->flush();

        $this->getDb()->insert('permissions', array('person_id' => $admin->id, 'name' => 'admin.use', 'value' => 1));

        $types = array('user', 'agent');
        foreach ($types as $type) {
            $deskProUsers = new Usersource();
            $deskProUsers->type = $type;
            $deskProUsers->source_type = 'Application\\DeskPRO\\Usersource\\Adapter\\DeskPRO';
            $deskProUsers->is_enabled = true;
            $deskProUsers->display_order = -10; // ensure #1 order (initially!)
            $deskProUsers->title = 'DeskPRO';
            $deskProUsers->options = array();
            $this->getEm()->persist($deskProUsers);
        }

        $this->getEm()->flush();

        // Create a ticket in the DB manually
        $this->getDb()->exec(
            "
            INSERT INTO `tickets`
            (
            `ref`,
            `auth`,
            `sent_to_address`,
            `email_account_address`,
            `creation_system`,
            `creation_system_option`,
            `ticket_hash`,
            `status`,
            `is_hold`,
            `urgency`,
            `count_agent_replies`,
            `count_user_replies`,
            `date_created`,
            `date_status`,
            `total_user_waiting`,
            `total_to_first_reply`,
            `has_attachments`,
            `subject`,
            `original_subject`
            )
            VALUES
                ('QMOI-7218-PQGI',
                'SPMGCS2PRX32YNG',
                '',
                '',
                'web.agent.portal',
                '',
                'none',
                'awaiting_user',
                0,
                1,
                1,
                0,
                '" . date('Y-m-d H:i:s') . "',
                '" . date('Y-m-d H:i:s') . "',
                0,
                0,
                0,
                'Test',
                'Test'
                );
            "
        );

        $this->getDb()->exec(
            "
            REPLACE INTO `settings` (`name`, `value`)
            VALUES
                ('portal.default_brand', '1'),
                ('core.app_secret', 'YXI5Z2HSQ9IF8KROQQ63GL4FB4CV57ZIIZ7CZO68FUDYBZIP2M'),
                ('core.cron_logreport.cli-phperr.log', '1380716762'),
                ('core.default_from_email', 'noreply@example.com'),
                ('core.default_timezone', 'UTC'),
                ('core.deskpro_build', '" . time() . "'),
                ('core.deskpro_build_num', '0'),
                ('core.deskpro_url', 'http://localhost:8888/'),
                ('core.deskpro_version', '20131002122551'),
                ('core.done_data_initializer', '1'),
                ('core.done_rewrite_urls_check', '" . time() . "'),
                ('core.install_build', '" . time() . "'),
                ('core.install_key', '6S7X77ZAR2CYSDT4GJCJ'),
                ('core.install_timestamp', '" . time() . "'),
                ('core.install_token', 'PUGYIA9E82Z8JCPKO0NKGC957HITHNZRFHY4CQ3V1380214398'),
                ('core.last_cron_run', '" . time() . "'),
                ('core.last_cron_start', '" . time() . "'),
                ('core.license', 'TlZNVi0wMTEyLUZVVVNFVEJHVFJNRU9KQlNHVlJNUVNTUgERC3\r\nlkZGRncEQKPwB2IyU+LiJjOgZ9FhE8ARdRIQ4OCR8seUR0ZRUZ\r\nJi9+cQB4eTF5ZjQ3P2J5TXYxdREHWzB/a1xiVQ0KeQdqMS5Qf1\r\nYtWXwZagd5DX9OCxASXzAzNGJmGTE7HhAKEBBnODZiGyYGAXVt\r\nLh8TKxcMQyFbKiAhP08aEFoECSM4TQkmMS8mEXJ1UQQINRcsAG\r\noHPBBxZxcFP1l7Uw8TJwseDn1IXAI5WwxLfVQoASkUClloBy93\r\nUEF2XFMQCwYFSC9aewFYHwJVeV0RAAonCEkhIzkjHn8WWSkRPn\r\ncpVyxrMQw6fARnIk8TDQcQCGcZRSombUhedVMENwhxUmpTLUIV\r\nZHRUflZ5UAhnAVs0CyhTZgspTkUIfQVdNWA'),
                ('core.rewrite_urls', '1'),
                ('core.setup_initial', '1'),
                ('core.task_completed_add_ticketfield', '" . time() . "'),
                ('core.twitter_last_cleanup', '" . time() . "'),
                ('core.use_agent_team', '1'),
                ('core_tickets.enable_like_search_auto', '1'),
                ('user.kb_subscriptions_last', '" . time() . "');
        "
        );

        // disable http cache
        $this->getDb()->exec(
            "
            REPLACE INTO `settings` (`name`, `value`)
            VALUES
                ('portal.http_cache_last_modified', '0'),
                ('portal.http_cache_etags', '0'),
                ('portal.smaxage_guest_tag', '0'),
                ('portal.smaxage_guest_page', '0'),
                ('portal.smaxage_user_page', '0'),
                ('portal.smaxage_user_tag', '0');
        "
        );

        $agent1 = $em->find('DeskPRO:Person', 1);
        $agent2 = $em->find('DeskPRO:Person', 2);

// Tickets
        $ticket1 = new Ticket();
        $ticket1->disableAutoTicketProcess();
        $ticket1->setPersonId(3);
        $ticket1->agent = $agent1;
        $ticket1->setDepartmentId(1);
        $em->persist($ticket1);
        $ticket2 = new Ticket();
        $ticket2->disableAutoTicketProcess();
        $ticket2->setPersonId(3);
        $ticket2->agent = $agent2;
        $ticket2->setDepartmentId(1);
        $em->persist($ticket2);
        $ticket3 = new Ticket();
        $ticket3->disableAutoTicketProcess();
        $ticket3->setPersonId(3);
        $ticket3->agent = $agent1;
        $ticket3->setDepartmentId(2);
        $em->persist($ticket3);
        $em->flush();

        // Add a blue flag on the first ticket.
        $ticket1->setFlagForPerson($agent, 'blue');
        $ticket1->setFlagForPerson($admin, 'green');
        $ticket2->setFlagForPerson($admin, 'green');

        // Adding a couple of labels.
        $ticket1->addLabelByString('foo');
        $ticket2->addLabelByString('bar');
        $ticket3->addLabelByString('bar'); // This adds a duplicate 'bar' record.
        $em->persist($ticket1);
        $em->persist($ticket2);
        $em->persist($ticket3);
        $em->flush();

// Feedback

        $category1 = new FeedbackCategory();
        $category1->setTitle('First feedback category');
        $em->persist($category1);

        $category2 = new FeedbackCategory();
        $category2->setTitle('Second feedback category');
        $em->persist($category2);

        $category3 = new FeedbackCategory();
        $category3->setTitle('Third feedback category');
        $em->persist($category3);

        $em->flush();

        $feedback1 = new Feedback();
        $feedback1->setTitle('First feedback');
        $feedback1->setContent('First feedback content');
        $feedback1->setHiddenStatus(Feedback::HIDDEN_STATUS_VALIDATING);
        $feedback1->setCategory($category1);
        $em->persist($feedback1);

        $feedback2 = new Feedback();
        $feedback2->setTitle('Second feedback');
        $feedback2->setContent('Second feedback content');
        $feedback2->setCategory($category2);
        $em->persist($feedback2);

        $feedback3 = new Feedback();
        $feedback3->setTitle('Third feedback');
        $feedback3->setContent('Third feedback content');
        $feedback3->setStatus(Feedback::STATUS_ACTIVE);
        $feedback3->setCategory($category1);
        $em->persist($feedback3);
        $em->flush();

        $feedback4 = new Feedback();
        $feedback4->setTitle('Fourth feedback');
        $feedback4->setContent('Fourth feedback content');
        $feedback4->setStatus(Feedback::STATUS_ACTIVE);
        $feedback4->setCategory($category2);
        $em->persist($feedback4);
        $em->flush();

        $feedback1->addLabelByString('foo');
        $feedback1->addLabelByString('bar');
        $feedback2->addLabelByString('bar');

        $comment1 = new FeedbackComment();
        $comment1->setContentReal('First test comment');
        $comment1->setStatus(FeedbackComment::STATUS_VALIDATING);
        $comment1->setObject($feedback1);
        $em->persist($comment1);

        $comment2 = new FeedbackComment();
        $comment2->setContentReal('Second test comment');
        $comment2->setStatus(FeedbackComment::STATUS_VISIBLE);
        $comment2->setObject($feedback2);
        $em->persist($comment2);

        $count++;

        return $count;
    }
}
