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

/**
 * DeskPRO.
 */
namespace DpTestSrc\TestBundle\DataSet;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\LabelDef;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketLayout;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\Entity\Task;
use DeskPRO\Bundle\AppBundle\Entity\TaskAssignment;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DpTestSrc\TestBundle\UserDetailsRepo;

/**
 * Class ApiDb.
 */
class ApiDb extends AbstractDbSet
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'api';
    }

    /**
     * {@inheritdoc}
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

        $deletedAgent = $this->addUser(
            UserDetailsRepo::DELETED_AGENT_FIRST_NAME,
            UserDetailsRepo::DELETED_AGENT_LAST_NAME,
            UserDetailsRepo::DELETED_AGENT_EMAIL,
            UserDetailsRepo::DELETED_AGENT_PASS,
            true,
            false,
            true
        );

        // this will be refactored into a better "entity creator" once the api data set needs more elaborate data
        // we need some deps, and some other entities
        $dep1              = new Department();
        $dep1->title       = 'sales';
        $dep2              = new Department();
        $dep2->title       = 'support';
        $team              = new AgentTeam();
        $team->name        = 'test team';
        $ticket_def        = new CustomDefTicket();
        $ticket_def->title = 'def';

        // Create ticket layouts
        // prepare custom defs for people, organizations and tickets
        foreach (['custom_def_people', 'custom_def_organizations', 'custom_def_ticket'] as $custom_def_table) {
            $this->getDb()->exec(
                "
                INSERT INTO `$custom_def_table` (`id`, `js_class`, `has_form_template`, `has_display_template`, `title`, `description`, `handler_class`, `options`, `is_user_enabled`, `is_enabled`, `display_order`, `is_agent_field`) VALUES ('1', '', '0', '0', 'Desired Sizes', 'A custom  field', 'Application\\\DeskPRO\\\CustomFields\\\Handler\\\Choice', '?', '1', '1', '12', '0');
                INSERT INTO `$custom_def_table` (`id`, `parent_id`, `js_class`, `has_form_template`, `has_display_template`, `title`, `description`, `options`, `is_user_enabled`, `is_enabled`, `display_order`, `is_agent_field`) VALUES ('2', '1', '', '0', '0', 'Small', '', '?', '1', '1', '13', '0');
                INSERT INTO `$custom_def_table` (`id`, `parent_id`, `js_class`, `has_form_template`, `has_display_template`, `title`, `description`, `options`, `is_user_enabled`, `is_enabled`, `display_order`, `is_agent_field`) VALUES ('3', '1', '', '0', '0', 'Medium', '', '?', '1', '1', '14', '0');
                INSERT INTO `$custom_def_table` (`id`, `parent_id`, `js_class`, `has_form_template`, `has_display_template`, `title`, `description`, `options`, `is_user_enabled`, `is_enabled`, `display_order`, `is_agent_field`) VALUES ('4', '1', '', '0', '0', 'Large', '', '?', '1', '1', '15', '0');
                INSERT INTO `$custom_def_table` (`id`, `js_class`, `has_form_template`, `has_display_template`, `title`, `description`, `handler_class`, `options`, `is_user_enabled`, `is_enabled`, `display_order`, `is_agent_field`) VALUES ('5', '', '0', '0', 'Delivery Time', 'A custom  field', 'Application\\\DeskPRO\\\CustomFields\\\Handler\\\DateTime', '?', '1', '1', '38', '0');
                INSERT INTO `$custom_def_table` (`id`, `js_class`, `has_form_template`, `has_display_template`, `title`, `description`, `handler_class`, `options`, `is_user_enabled`, `is_enabled`, `display_order`, `is_agent_field`) VALUES ('6', '', '0', '0', 'Widget Type', 'A custom  field', 'Application\\\DeskPRO\\\CustomFields\\\Handler\\\Text', '?', '1', '1', '10', '0');
                INSERT INTO `$custom_def_table` (`id`, `js_class`, `has_form_template`, `has_display_template`, `title`, `description`, `handler_class`, `options`, `is_user_enabled`, `is_enabled`, `display_order`, `is_agent_field`) VALUES ('7', '', '0', '0', 'Widget Description', 'A custom  field', 'Application\\\DeskPRO\\\CustomFields\\\Handler\\\Textarea', '?', '1', '1', '11', '0');
                INSERT INTO `$custom_def_table` (`id`, `js_class`, `has_form_template`, `has_display_template`, `title`, `description`, `handler_class`, `options`, `is_user_enabled`, `is_enabled`, `display_order`, `is_agent_field`) VALUES ('8', '', '0', '0', 'Multiple choice', 'A custom  field', 'Application\\\DeskPRO\\\CustomFields\\\Handler\\\Choice', 'a:2:{s:8:\"multiple\";b:1;s:8:\"expanded\";b:1;}', '1', '1', '12', '0');
                INSERT INTO `$custom_def_table` (`id`, `parent_id`, `js_class`, `has_form_template`, `has_display_template`, `title`, `description`, `options`, `is_user_enabled`, `is_enabled`, `display_order`, `is_agent_field`) VALUES ('9', '8', '', '0', '0', 'Choice 1', '', '?', '1', '1', '13', '0');
                INSERT INTO `$custom_def_table` (`id`, `parent_id`, `js_class`, `has_form_template`, `has_display_template`, `title`, `description`, `options`, `is_user_enabled`, `is_enabled`, `display_order`, `is_agent_field`) VALUES ('10', '8', '', '0', '0', 'Choice 2', '', '?', '1', '1', '14', '0');
                INSERT INTO `$custom_def_table` (`id`, `parent_id`, `js_class`, `has_form_template`, `has_display_template`, `title`, `description`, `options`, `is_user_enabled`, `is_enabled`, `display_order`, `is_agent_field`) VALUES ('11', '8', '', '0', '0', 'Choice 3', '', '?', '1', '1', '15', '0');
            "
            );
        }

        $layout = new Layout();
        $layout
            ->add(new LayoutField(FormFields::DEPARTMENT))
            ->add(new LayoutField(FormFields::MESSAGE))
        ;

        $ticket_layout1               = new TicketLayout();
        $ticket_layout1->is_enabled   = true;
        $ticket_layout1->agent_layout = $layout;
        $ticket_layout1->user_layout  = new Layout();

        $layout = new Layout();
        $layout
            ->add(new LayoutField(FormFields::PERSON))
            ->add(new LayoutField(FormFields::DEPARTMENT))
            ->add(new LayoutField(FormFields::MESSAGE))
            ->add(new LayoutField(FormFields::ATTACHMENTS))
            ->add(new LayoutField(FormFields::CAPTCHA))
            ->add(new LayoutField(FormFields::PRODUCT))
            ->add(new LayoutField(FormFields::CC))
            ->add(new LayoutField(FormFields::PRIORITY))
            ->add(new LayoutField(FormFields::CATEGORY))
            ->add(new LayoutField(FormFields::WORKFLOW))
            ->add(new LayoutField(FormFields::LABELS))

            ->add(new LayoutField('ticket_field', 1)) // Select box
            ->add(new LayoutField('ticket_field', 5)) // Datetime
            ->add(new LayoutField('ticket_field', 6)) // Text
            ->add(new LayoutField('ticket_field', 7)) // Textarea
            ->add(new LayoutField('ticket_field', 8)) // Checkbox group

            ->add(new LayoutField('user_field', 1)) // Select box
            ->add(new LayoutField('user_field', 5)) // Datetime
            ->add(new LayoutField('user_field', 6)) // Text
            ->add(new LayoutField('user_field', 7)) // Textarea
            ->add(new LayoutField('user_field', 8)) // Checkbox group

            ->add(new LayoutField('org_field', 1)) // Select box
            ->add(new LayoutField('org_field', 5)) // Datetime
            ->add(new LayoutField('org_field', 6)) // Text
            ->add(new LayoutField('org_field', 7)) // Textarea
            ->add(new LayoutField('org_field', 8)) // Checkbox group
        ;

        $ticket_layout2               = new TicketLayout($dep2);
        $ticket_layout2->is_enabled   = true;
        $ticket_layout2->agent_layout = $layout;
        $ticket_layout2->user_layout  = new Layout();

        // Create a basic task
        $task = new Task($admin);
        $task->setTitle('A demo task');
        $taskAssignment = new TaskAssignment();
        $taskAssignment->setTask($task);
        $taskAssignment->setPerson($admin);

        $unassignedTask = new Task($admin);
        $unassignedTask->setTitle('An unassigned task');

        // Create a new knowledge base article
        $article               = new Article();
        $article->slug         = 'test';
        $article->title        = 'A test article';
        $article->content      = 'This is a test article';
        $article->view_count   = 0;
        $article->total_rating = 0;
        $article->num_comments = 0;
        $article->num_ratings  = 0;
        $article->status       = 'published';
        $article->date_created = new \DateTime();

        // Persist them in the entity manager
        $em->persist($ticket_def);
        $em->persist($team);
        $em->persist($dep1);
        $em->persist($dep2);
        $em->persist($ticket_layout1);
        $em->persist($ticket_layout2);
        $em->persist($task);
        $em->persist($taskAssignment);
        $em->persist($unassignedTask);
        $em->persist($article);
        $em->flush();

        $this->getDb()->insert('permissions', ['person_id' => $admin->id, 'name' => 'admin.use', 'value' => 1]);

        $types = ['user', 'agent'];
        foreach ($types as $type) {
            $deskProUsers                = new Usersource();
            $deskProUsers->type          = $type;
            $deskProUsers->source_type   = 'Application\\DeskPRO\\Usersource\\Adapter\\DeskPRO';
            $deskProUsers->is_enabled    = true;
            $deskProUsers->display_order = -10; // ensure #1 order (initially!)
            $deskProUsers->title         = 'DeskPRO';
            $deskProUsers->options       = [];
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
                '".date('Y-m-d H:i:s')."',
                '".date('Y-m-d H:i:s')."',
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
                ('core.deskpro_build', '".time()."'),
                ('core.deskpro_build_num', '0'),
                ('core.deskpro_url', 'http://localhost:8888/'),
                ('core.deskpro_version', '20131002122551'),
                ('core.done_data_initializer', '1'),
                ('core.install_build', '".time()."'),
                ('core.install_key', '6S7X77ZAR2CYSDT4GJCJ'),
                ('core.install_timestamp', '".time()."'),
                ('core.install_token', 'PUGYIA9E82Z8JCPKO0NKGC957HITHNZRFHY4CQ3V1380214398'),
                ('core.last_cron_run', '".time()."'),
                ('core.last_cron_start', '".time()."'),
                ('core.license', '".$this->getLicenseKey()."'),
                ('core.rewrite_urls', '1'),
                ('core.setup_initial', '1'),
                ('core.task_completed_add_ticketfield', '".time()."'),
                ('core.twitter_last_cleanup', '".time()."'),
                ('core.use_agent_team', '1'),
                ('core_tickets.enable_like_search_auto', '1'),
                ('user.kb_subscriptions_last', '".time()."');
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
        $ticket1->setSubject('Ticket #1');
        $ticket1->setRef('DIDXGBLWRL-201622485');
        $em->persist($ticket1);
        $ticket2 = new Ticket();
        $ticket2->disableAutoTicketProcess();
        $ticket2->setPersonId(3);
        $ticket2->agent = $agent2;
        $ticket2->setDepartmentId(1);
        $ticket2->setSubject('Ticket #2');
        $em->persist($ticket2);
        $ticket3 = new Ticket();
        $ticket3->disableAutoTicketProcess();
        $ticket3->setPersonId(3);
        $ticket3->agent = $agent1;
        $ticket3->setDepartmentId(2);
        $ticket3->setSubject('Ticket #3');
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

        $this->getDb()->exec(
            "
            INSERT INTO `feedback_categories` (`id`, `parent_id`, `title`, `slug`, `display_order`, `depth`, `root`)
              VALUES
              (1, NULL, 'Suggestion', 'suggestion', 0, 0, NULL),
              (2, NULL, 'Feature Request', 'feature-request', 0, 0, NULL),
              (3, NULL, 'Bug Report', 'bug-report', 0, 0, NULL);
            "
        );

        $this->getDb()->exec(
            "
            INSERT INTO `feedback_status_categories` (`status_type`, `title`, `display_order`)
            VALUES
              ('active', 'Gathering Feedback', 0),
              ('active', 'Planning', 0),
              ('active', 'Started', 0),
              ('active', 'Under Review', 0),
              ('closed', 'Completed', 0),
              ('closed', 'Duplicate', 0),
              ('closed', 'Declined', 0);
        "
        );

        $this->getDb()->exec(
            "
            INSERT INTO `feedback` (`id`, `status_category_id`, `category_id`, `person_id`, `language_id`, `hidden_status`, `is_reviewed`, `popularity`, `slug`, `title`, `content`, `view_count`, `total_rating`, `num_comments`, `num_ratings`, `status`, `date_created`, `date_published`, `date_updated`, `date_last_comment`)
              VALUES
                (1, 5, 1, 1, NULL, NULL, 1, 0, '_slug-to-feedback-1', 'Test feedback 1', 'Content of test feedback 1. This is an example suggestion. Feel free to edit or delete it from the agent interface.', 0, 1, 0, 2, 'closed', '2015-09-13 11:33:33', '2015-04-13 11:33:33', '0000-00-00 00:00:00', NULL),
                (2, 5, 1, 1, NULL, NULL, 1, 0, '_slug-to-feedback-2', 'Test feedback 2', 'Content of test feedback 2', 0, 3, 0, 4, 'closed', '2015-09-14 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (3, 5, 2, 1, NULL, NULL, 1, 0, '_slug-to-feedback-3', 'Test feedback 3', 'Content of test feedback 3', 0, 5, 0, 6, 'closed', '2015-09-15 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (4, 5, 3, 1, NULL, NULL, 1, 0, '_slug-to-feedback-4', 'Test feedback 4', 'Content of test feedback 4', 0, 0, 0, 0, 'closed', '2015-09-16 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (5, 5, 1, 1, NULL, NULL, 1, 0, '_slug-to-feedback-5', 'Test feedback 5', 'Content of test feedback 5', 0, 1, 0, 1, 'closed', '2015-09-17 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (6, 5, 1, 1, NULL, NULL, 1, 0, '_slug-to-feedback-6', 'Test feedback 6', 'Content of test feedback 6', 0, 2, 0, 1, 'closed', '2015-09-18 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (7, 5, 2, 1, NULL, NULL, 1, 15, '_slug-to-feedback-7', 'Test feedback 7', 'I am trying to implement Infinite Scrolling on a gridview to speed up my web application, since the gridview is being bound to a sql query that returns thousands of records at start (its the clients wish, and I cant change that.)', 0, 3, 0, 1, 'closed', '2015-09-19 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (8, 5, 1, 1, NULL, NULL, 1, 0, '_slug-to-feedback-8', 'Test feedback 8', 'This is an example suggestion. Feel free to edit or delete it from the agent interface.', 0, 0, 0, 2, 'closed', '2015-09-19 11:33:33', '2015-04-13 11:33:33', '0000-00-00 00:00:00', NULL),
                (9, 5, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-9', 'Test feedback 9', 'Content of test feedback 9. This is an example suggestion. Feel free to edit or delete it from the agent interface.', 0, 1, 0, 2, 'closed', '2015-09-20 11:33:33', '2015-04-13 11:33:33', '0000-00-00 00:00:00', NULL),
                (10, 5, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-10', 'Test feedback 10', 'Content of test feedback 10', 0, 3, 0, 4, 'closed', '2015-09-21 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (11, 6, 2, 1, NULL, NULL, 1, 0, 'slug-to-feedback-11', 'Test feedback 11', 'Content of test feedback 11', 0, 5, 0, 6, 'closed', '2015-09-22 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (12, 6, 3, 1, NULL, NULL, 1, 0, 'slug-to-feedback-12', 'Test feedback 12', 'Content of test feedback 12', 0, 0, 0, 0, 'closed', '2015-09-23 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (13, 6, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-13', 'Test feedback 13', 'Content of test feedback 13', 0, 1, 0, 1, 'closed', '2015-09-24 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (14, 6, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-14', 'Test feedback 14', 'Content of test feedback 14', 0, 2, 0, 1, 'closed', '2015-09-25 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (15, 6, 2, 1, NULL, NULL, 1, 15, 'slug-to-feedback-15', 'Test feedback 15', 'I am trying to implement Infinite Scrolling on a gridview to speed up my web application, since the gridview is being bound to a sql query that returns thousands of records at start (its the clients wish, and I cant change that.)', 0, 3, 0, 1, 'closed', '2015-09-26 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (16, 6, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-16', 'Test feedback 16', 'This is an example suggestion. Feel free to edit or delete it from the agent interface.', 0, 0, 0, 2, 'closed', '2015-09-27 11:33:33', '2015-04-13 11:33:33', '0000-00-00 00:00:00', NULL),
                (17, 6, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-17', 'Test feedback 17', 'Content of test feedback 17', 0, 3, 0, 4, 'closed', '2015-09-27 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (18, 6, 2, 1, NULL, NULL, 1, 0, 'slug-to-feedback-18', 'Test feedback 18', 'Content of test feedback 18', 0, 5, 0, 6, 'closed', '2015-09-28 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (19, 6, 3, 1, NULL, NULL, 1, 0, 'slug-to-feedback-19', 'Test feedback 19', 'Content of test feedback 3', 0, 0, 0, 0, 'closed', '2015-09-29 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (20, 6, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-20', 'Test feedback 20', 'Content of test feedback 20', 0, 1, 0, 1, 'closed', '2015-09-30 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (21, 1, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-21', 'Test feedback 21', 'Content of test feedback 21', 0, 2, 0, 1, 'active', '2015-10-01 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (22, 1, 2, 1, NULL, NULL, 1, 15, 'slug-to-feedback-22', 'Test feedback 22', 'I am trying to implement Infinite Scrolling on a gridview to speed up my web application, since the gridview is being bound to a sql query that returns thousands of records at start (its the clients wish, and I cant change that.)', 0, 3, 0, 1, 'active', '2015-10-02 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (23, 1, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-23', 'Test feedback 23', 'Content of test feedback 23. This is an example suggestion. Feel free to edit or delete it from the agent interface.', 0, 1, 0, 2, 'active', '2015-10-02 11:33:33', '2015-04-13 11:33:33', '0000-00-00 00:00:00', NULL),
                (24, NULL, 1, 1, NULL, 'deleted', 0, 0, 'slug-to-feedback-24', 'Test feedback 24', 'Content of test feedback 24', 0, 3, 0, 4, 'hidden', '2015-10-03 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (25, 1, 2, 1, NULL, NULL, 0, 0, 'slug-to-feedback-25', 'Test feedback 25', 'Content of test feedback 25', 0, 5, 0, 6, 'active', '2015-10-04 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (26, 1, 3, 1, NULL, NULL, 1, 0, 'slug-to-feedback-26', 'Test feedback 26', 'Content of test feedback 26', 0, 0, 0, 0, 'active', '2015-10-05 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (27, NULL, 1, 1, NULL, 'spam', 0, 0, 'slug-to-feedback-27', 'Test feedback 27', 'Content of test feedback 27', 0, 1, 0, 1, 'hidden', '2015-10-06 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (28, 1, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-28', 'Test feedback 28', 'Content of test feedback 28', 0, 2, 0, 1, 'active', '2015-10-07 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (29, 1, 2, 1, NULL, NULL, 1, 15, 'slug-to-feedback-29', 'Test feedback 29', 'I am trying to implement Infinite Scrolling on a gridview to speed up my web application, since the gridview is being bound to a sql query that returns thousands of records at start (its the clients wish, and I cant change that.)', 0, 3, 0, 1, 'active', '2015-10-08 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (30, 1, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-30', 'Test feedback 30', 'This is an example suggestion. Feel free to edit or delete it from the agent interface.', 0, 0, 0, 2, 'active', '2015-04-13 11:33:33', '2015-04-13 11:33:33', '0000-00-00 00:00:00', NULL),
                (31, NULL, 1, 1, NULL, 'deleted', 0, 0, 'slug-to-feedback-31', 'Test feedback 31', 'Content of test feedback 31', 0, 3, 0, 4, 'hidden', '2015-10-09 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (32, 2, 2, 1, NULL, NULL, 0, 0, 'slug-to-feedback-32', 'Test feedback 32', 'Content of test feedback 32', 0, 5, 0, 6, 'active', '2015-10-10 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (33, 2, 3, 1, NULL, NULL, 1, 0, 'slug-to-feedback-33', 'Test feedback 33', 'Content of test feedback 33', 0, 0, 0, 0, 'active', '2015-10-11 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (34, NULL, 1, 1, NULL, 'spam', 0, 0, 'slug-to-feedback-34', 'Test feedback 34', 'Content of test feedback 34', 0, 1, 0, 1, 'hidden', '2015-10-12 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (35, 2, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-35', 'Test feedback 35', 'Content of test feedback 35', 0, 2, 0, 1, 'active', '2015-10-13 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (36, 2, 2, 1, NULL, NULL, 1, 15, 'slug-to-feedback-36', 'Test feedback 36', 'I am trying to implement Infinite Scrolling on a gridview to speed up my web application, since the gridview is being bound to a sql query that returns thousands of records at start (its the clients wish, and I cant change that.)', 0, 3, 0, 1, 'active', '2015-10-14 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (37, 2, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-37', 'Test feedback 37', 'Content of test feedback 37. This is an example suggestion. Feel free to edit or delete it from the agent interface.', 0, 1, 0, 2, 'active', '2015-10-14 11:33:33', '2015-04-13 11:33:33', '0000-00-00 00:00:00', NULL),
                (38, NULL, 1, 1, NULL, 'deleted', 0, 0, 'slug-to-feedback-38', 'Test feedback 38', 'Content of test feedback 38', 0, 3, 0, 4, 'hidden', '2015-10-15 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (39, 2, 2, 1, NULL, NULL, 0, 0, 'slug-to-feedback-39', 'Test feedback 39', 'Content of test feedback 39', 0, 5, 0, 6, 'active', '2015-06-02 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (40, 2, 3, 1, NULL, NULL, 1, 0, 'slug-to-feedback-40', 'Test feedback 40', 'Content of test feedback 40', 0, 0, 0, 0, 'active', '2015-10-16 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (41, NULL, 1, 1, NULL, 'spam', 0, 0, 'slug-to-feedback-41', 'Test feedback 41', 'Content of test feedback 41', 0, 1, 0, 1, 'hidden', '2015-10-17 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (42, 2, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-42', 'Test feedback 42', 'Content of test feedback 42', 0, 2, 0, 1, 'active', '2015-10-18 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (43, 2, 2, 1, NULL, NULL, 1, 15, 'slug-to-feedback-43', 'Test feedback 43', 'I am trying to implement Infinite Scrolling on a gridview to speed up my web application, since the gridview is being bound to a sql query that returns thousands of records at start (its the clients wish, and I cant change that.)', 0, 3, 0, 1, 'active', '2015-10-20 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (44, 2, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-44', 'Test feedback 44', 'This is an example suggestion. Feel free to edit or delete it from the agent interface.', 0, 0, 0, 2, 'active', '2015-10-21 11:33:33', '2015-04-13 11:33:33', '0000-00-00 00:00:00', NULL),
                (45, NULL, 1, 1, NULL, 'deleted', 0, 0, 'slug-to-feedback-45', 'Test feedback 45', 'Content of test feedback 45', 0, 3, 0, 4, 'hidden', '2015-10-22 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (46, 2, 2, 1, NULL, NULL, 0, 0, 'slug-to-feedback-46', 'Test feedback 46', 'Content of test feedback 46', 0, 5, 0, 6, 'active', '2015-10-23 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (47, 2, 3, 1, NULL, NULL, 1, 0, 'slug-to-feedback-47', 'Test feedback 47', 'Content of test feedback 47', 0, 0, 0, 0, 'active', '2015-10-24 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (48, NULL, 1, 1, NULL, 'spam', 0, 0, 'slug-to-feedback-48', 'Test feedback 48', 'Content of test feedback 48', 0, 1, 0, 1, 'hidden', '2015-10-25 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (49, 2, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-49', 'Test feedback 49', 'Content of test feedback 49', 0, 2, 0, 1, 'active', '2015-10-26 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (50, 2, 2, 1, NULL, NULL, 1, 15, 'slug-to-feedback-50', 'Test feedback 50', 'I am trying to implement Infinite Scrolling on a gridview to speed up my web application, since the gridview is being bound to a sql query that returns thousands of records at start (its the clients wish, and I cant change that.)', 0, 3, 0, 1, 'active', '2015-10-26 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (51, 2, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-51', 'Test feedback 51', 'Content of test feedback 51. This is an example suggestion. Feel free to edit or delete it from the agent interface.', 0, 1, 0, 2, 'active', '2015-10-28 11:33:33', '2015-04-13 11:33:33', '0000-00-00 00:00:00', NULL),
                (52, NULL, 1, 1, NULL, 'deleted', 0, 0, 'slug-to-feedback-52', 'Test feedback 52', 'Content of test feedback 52', 0, 3, 0, 4, 'hidden', '2015-10-29 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (53, 2, 2, 1, NULL, NULL, 0, 0, 'slug-to-feedback-53', 'Test feedback 53', 'Content of test feedback 53', 0, 5, 0, 6, 'active', '2015-10-30 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (54, 2, 3, 1, NULL, NULL, 1, 0, 'slug-to-feedback-54', 'Test feedback 54', 'Content of test feedback 54', 0, 0, 0, 0, 'active', '2015-10-31 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (55, NULL, 1, 1, NULL, 'spam', 0, 0, 'slug-to-feedback-55', 'Test feedback 55', 'Content of test feedback 55', 0, 1, 0, 1, 'hidden', '2015-11-01 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (56, 2, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-56', 'Test feedback 56', 'Content of test feedback 56', 0, 2, 0, 1, 'active', '2015-11-02 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (57, 2, 2, 1, NULL, NULL, 1, 15, 'slug-to-feedback-57', 'Test feedback 57', 'I am trying to implement Infinite Scrolling on a gridview to speed up my web application, since the gridview is being bound to a sql query that returns thousands of records at start (its the clients wish, and I cant change that.)', 0, 3, 0, 1, 'active', '2015-11-02 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (58, 2, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-58', 'Test feedback 58', 'This is an example suggestion. Feel free to edit or delete it from the agent interface.', 0, 0, 0, 2, 'active', '2015-04-13 11:33:33', '2015-04-13 11:33:33', '0000-00-00 00:00:00', NULL),
                (59, NULL, 1, 1, NULL, 'deleted', 0, 0, 'slug-to-feedback-59', 'Test feedback 59', 'Content of test feedback 59', 0, 3, 0, 4, 'hidden', '2015-11-04 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (60, 2, 2, 1, NULL, NULL, 0, 0, 'slug-to-feedback-60', 'Test feedback 60', 'Content of test feedback 60', 0, 5, 0, 6, 'active', '2015-11-05 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (61, 2, 3, 1, NULL, NULL, 1, 0, 'slug-to-feedback-61', 'Test feedback 61', 'Content of test feedback 61', 0, 0, 0, 0, 'active', '2015-11-06 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (62, NULL, 1, 1, NULL, 'spam', 0, 0, 'slug-to-feedback-62', 'Test feedback 62', 'Content of test feedback 62', 0, 1, 0, 1, 'hidden', '2015-11-07 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (63, 2, 1, 1, NULL, NULL, 1, 0, 'slug-to-feedback-63', 'Test feedback 63', 'Content of test feedback 63', 0, 2, 0, 1, 'active', '2015-11-08 00:00:00', NULL, '0000-00-00 00:00:00', NULL),
                (64, 2, 2, 1, NULL, NULL, 1, 15, 'slug-to-feedback-64', 'Test feedback 64', 'I am trying to implement Infinite Scrolling on a gridview to speed up my web application, since the gridview is being bound to a sql query that returns thousands of records at start (its the clients wish, and I cant change that.)', 0, 3, 0, 1, 'active', '2015-11-08 00:00:00', NULL, '0000-00-00 00:00:00', NULL);
           "
        );

        $this->getDb()->exec(
            "
            INSERT INTO `labels_feedback` (`feedback_id`, `label`)
            VALUES
              (1, 'label1'),
              (1, 'label2'),
              (2, 'label1'),
              (3, 'another');
            "
        );

        $this->getDb()->exec(
            "
              INSERT INTO `feedback_comments` (`id`, `feedback_id`, `person_id`, `ip_address`, `visitor_id`, `email`, `name`, `website`, `content`, `status`, `is_reviewed`, `date_created`)
              VALUES
                (1, 1, 1, '', '', NULL, NULL, NULL, 'Some comment for the first feedback. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce ultrices sem ac risus efficitur, vitae.', 'validating', 0, '2015-08-17 00:00:00'),
                (2, 1, 1, '', '', NULL, NULL, NULL, 'One more comment for the first feedback', 'validating', 0, '2015-09-08 00:00:00'),
                (3, 2, 1, '', '', NULL, NULL, NULL, 'Some comment for the second feedback. Quisque id malesuada urna. Aliquam erat volutpat. Duis risus odio, faucibus ac lacus nec, dapibus.', 'validating', 0, '2015-09-23 00:00:00'),
                (4, 3, 1, '', '', NULL, NULL, NULL, 'Some comment for the third feedback. Proin enim mauris, faucibus sit amet pretium non, sagittis ut eros. Praesent non sem ut.', 'user_validating', 0, '2015-10-01 00:00:00');
            ;
        "
        );

        $this->getDb()->exec(
            "
            INSERT INTO `custom_def_feedback`
            (`id`, `parent_id`, `app_id`, `sys_name`, `js_class`, `has_form_template`, `has_display_template`, `title`, `description`, `handler_class`, `options`, `is_user_enabled`, `is_enabled`, `display_order`, `default_value`, `is_agent_field`)
            VALUES
              (1, NULL, NULL, 'cat', '', 0, 0, 'Category', 'e.g., maybe Windows, Mac, Linux.', 'Application\\\DeskPRO\\\CustomFields\\\Handler\\\Text', '', 1, 1, 0, NULL, 1)
            ;
        "
        );

        $this->getDb()->exec(
            "
            INSERT INTO `custom_data_feedback`
            (`id`, `feedback_id`, `field_id`, `root_field_id`, `value`, `input`)
            VALUES
              (1, 1, 1, NULL, 0, 'Windows'),
              (2, 2, 1, NULL, 0, 'Linux'),
              (3, 3, 1, NULL, 0, 'Linux'),
              (4, 4, 1, NULL, 0, 'Mac')
            ;
        "
        );

        // "/user_chats" endpoint test data ----------------------------------------------------------------------------
        $this->getDb()->exec(
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
        // end of "/user_chats" endpoint test data

        // Labels endpoints test data ----------------------------------------------------------------------------------
        $feedbackType     = LabelDef::TYPE_FEEDBACK;
        $organizationType = LabelDef::TYPE_ORGS;
        $peopleType       = LabelDef::TYPE_PEOPLE;
        $ticketType       = LabelDef::TYPE_TICKETS;
        $this->getDb()->exec(
            "
            INSERT INTO `label_defs`
                (`label_type`, `label`, `color`, `total`)
            VALUES
                ('$feedbackType', 'AAA-feedback', 'red', 0),
                ('$feedbackType', 'BBB-feedback', 'white', 0),
                ('$feedbackType', 'CCC-feedback', 'red', 0),
                ('$organizationType', 'AAA-org', 'red', 1),
                ('$organizationType', 'BBB-org', 'blue', 2),
                ('$organizationType', 'CCC-org', 'green', 42),
                ('$peopleType', 'AAA-person', 'white', 1),
                ('$peopleType', 'BBB-person', 'red', 3),
                ('$peopleType', 'CCC-person', 'yellow', 3),
                ('$ticketType', 'AAA-ticket', 'white', 1),
                ('$ticketType', 'BBB-ticket', 'red', 3),
                ('$ticketType', 'CCC-ticket', 'green', 13)
            ;
        "
        );
        // end of labels endpoints

        // "/user_groups" endpoint and its' children test data ---------------------------------------------------------
        $this->getDb()->exec(
            "
            INSERT INTO `usergroups`
                (`id`, `title`, `note`, `is_agent_group`, `sys_name`, `is_enabled`)
            VALUES
                (1, 'Everyone', 'test', 0, 'everyone', 1),
                (2, 'Registered', 'test', 0, 'registered', 1),
                (3, 'Group 1', 'test', 0, 'g1', 1),
                (4, 'Group 2 (disabled)', 'test', 0, 'g2', 0),
                (5, 'Group 3', 'test', 0, 'g3', 1),
                (6, 'Group 4', 'test', 0, 'g4', 1),

                (7, 'usergroup_agent_all_perms', 'usergroup_agent_all_perms', 1, 'agent_all_perms', 1),
                (8, 'agent_all_non_destructive', 'agent_all_non_destructive', 1, 'agent_all_safe_perms', 1)
            ;

            INSERT INTO `person2usergroups`
                (`person_id`, `usergroup_id`)
            VALUES
                (1, 1),
                (2, 1),
                (3, 1),
                (4, 1),
                (1, 2),
                (2, 2),
                (3, 2),
                (4, 2),
                (1, 3),
                (1, 4),
                (2, 4),
                (1, 5),
                (2, 5),
                (3, 5),
                (4, 6),

                (1, 7),
                (1, 8)
            ;
        "
        );
        // end of "/user_groups"

        // Department permissions test data ----------------------------------------------------------------------------------
        $this->getDb()->exec(
            "
            INSERT INTO `department_permissions`
                (`id`, `department_id`, `usergroup_id`, `app`, `name`, `value`)
            VALUES
                ('1', '1', '1', 'tickets', 'full', '1'),
                ('2', '2', '1', 'tickets', 'full', '1');
        "
        );
        // end of department permissions

        // Products test data ----------------------------------------------------------------------------------
        $this->getDb()->exec(
            "
            INSERT INTO `products`
                (`id`, `title`, `display_order`, `depth`)
            VALUES
                ('1', 'Product 1', '10', '0'),
                ('2', 'Product 2', '20', '0'),
                ('3', 'Product 3', '30', '0');
        "
        );
        // end of products

        // Ticket priorities test data ----------------------------------------------------------------------------------
        $this->getDb()->exec(
            "
            INSERT INTO `ticket_priorities`
                (`id`, `title`, `priority`)
            VALUES
                ('1', 'Priority 1', '10'),
                ('2', 'Priority 2', '20'),
                ('3', 'Priority 3', '30');
        "
        );
        // end of ticket priorities

        // Ticket categories test data ----------------------------------------------------------------------------------
        $this->getDb()->exec(
            "
            INSERT INTO `ticket_categories`
                (`id`, `title`, `display_order`)
            VALUES
              ('1', 'Category 1', '10'),
              ('2', 'Category 2', '20'),
              ('3', 'Category 3', '30');
        "
        );
        // end of ticket categories

        // Ticket workflows test data ----------------------------------------------------------------------------------
        $this->getDb()->exec(
            "
            INSERT INTO `ticket_workflows`
                (`id`, `title`, `display_order`)
            VALUES
              ('1', 'Workflow 1', '10'),
              ('2', 'Workflow 2', '20'),
              ('3', 'Workflow 3', '30');
        "
        );
        // end of ticket workflows

        // "/organizations" endpoint and its' children test data -------------------------------------------------------
        $this->getDb()->exec(
            "
            INSERT INTO `organizations`
                (`picture_blob_id`, `name`, `summary`, `importance`, `date_created`)
            VALUES
                (NULL, 'Organization 1', 'test organization', 1, '2015-08-03 00:00:00'),
                (NULL, 'Organization 2', 'test organization', 2, '2015-08-07 00:00:00');

            UPDATE `people` SET organization_id = 1 WHERE id IN (1, 3);
            UPDATE `people` SET organization_id = 2 WHERE id IN (2, 4);
        "
        );
        // end of "/organizations"

        // "/agent_teams" endpoint and its' children test data ---------------------------------------------------------
        $this->getDb()->exec(
            "
            INSERT INTO `agent_teams`
                (`avatar_blob_id`, `name`)
            VALUES
                (NULL, 'Support Managers'),
                (NULL, '1st Level Support')
            ;

            INSERT INTO `agent_team_members`
                (`team_id`, `person_id`)
            VALUES
                (1, 1),
                (1, 2),
                (2, 3),
                (2, 4)
            ;
        "
        );
        // end of "/agent_teams"

        // Default language --------------------------------------------------------------------------------------------
        $this->getDb()->exec(
            "
            INSERT INTO `languages`
                (`id`, `sys_name`, `lang_code`, `title`, `base_filepath`, `locale`, `flag_image`, `is_rtl`, `has_user`,
                 `has_agent`, `has_admin`)
            VALUES
                (1, 'default', 'eng', 'English', NULL, 'en_US', 'us.png', 0, 1, 1, 1);
        "
        );

        // Content (articles, news, downloads) test data ---------------------------------------------------------------
        $this->getDb()->exec(
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
        // end of content test data

        // Comments test data ------------------------------------------------------------------------------------------
        $this->getDb()->exec(
            "
            INSERT INTO `article_comments`
                (`id`, `article_id`, `person_id`, `ip_address`, `email`, `name`, `website`, `content`, `status`, `is_reviewed`, `date_created`)
            VALUES
                (1, 1, 1, '', NULL, NULL, NULL, 'Article comment #1', 'visible', 1, '2011-08-01 00:00:00'),
                (2, 2, 2, '', NULL, NULL, NULL, 'Article comment #2', 'visible', 0, '2011-08-01 00:00:00'),
                (3, 2, 3, '', NULL, NULL, NULL, 'Article comment #3', 'deleted', 0, '2011-08-01 00:00:00')
            ;

            INSERT INTO `news_comments`
                (`id`, `news_id`, `person_id`, `ip_address`, `email`, `name`, `website`, `content`, `status`, `is_reviewed`, `date_created`)
            VALUES
                (1, 1, 1, '', NULL, NULL, NULL, 'News comment #1', 'visible', 1, '2011-08-01 00:00:00'),
                (2, 2, 2, '', NULL, NULL, NULL, 'News comment #2', 'visible', 0, '2011-08-01 00:00:00'),
                (3, 2, 3, '', NULL, NULL, NULL, 'News comment #3', 'deleted', 0, '2011-08-01 00:00:00')
            ;

            INSERT INTO `download_comments`
                (`id`, `download_id`, `person_id`, `ip_address`, `email`, `name`, `website`, `content`, `status`, `is_reviewed`, `date_created`)
            VALUES
                (1, 1, 1, '', NULL, NULL, NULL, 'Download comment #1', 'visible', 1, '2011-08-01 00:00:00'),
                (2, 2, 2, '', NULL, NULL, NULL, 'Download comment #2', 'visible', 0, '2011-08-01 00:00:00'),
                (3, 2, 3, '', NULL, NULL, NULL, 'Download comment #3', 'deleted', 0, '2011-08-01 00:00:00')
            ;
        "
        );
        // end of comments test data

        // Glossary test data ------------------------------------------------------------------------------------------
        $this->getDb()->exec(
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
        // end of glossary test data

        // ArticlePendingCreate test data ------------------------------------------------------------------------------
        $this->getDb()->exec(
            "
            INSERT INTO `article_pending_create`
                (`person_id`, `ticket_id`, `ticket_message_id`, `comment`, `date_created`, `assigned_person_id`)
            VALUES
                (1, NULL, NULL, 'ArticlePendingCreate #1', '2015-09-01 10:05:30', 2),
                (2, NULL, NULL, 'ArticlePendingCreate #2', '2015-09-02 04:12:25', 3)
            ;
        "
        );
        // end of ArticlePendingCreate

        // PersonSetting test data ------------------------------------------------------------------------------
        $this->getDb()->exec(
            "
            INSERT INTO `person_settings` (`person_id`, `name`, `value`)
            VALUES
              (1, 'feedback_display_fields', '{\"isStored\":null,\"isChanged\":\"1\",\"card\":{\"id\":{\"isShown\":\"1\"},\"hidden_status\":{\"isShown\":\"1\"},\"status_category\":{\"isShown\":\"1\"},\"custom_category\":{\"isShown\":\"1\"},\"type\":{\"isShown\":\"1\"},\"date_created\":{\"isShown\":null},\"total_rating\":{\"isShown\":\"1\"},\"num_ratings\":{\"isShown\":\"1\"},\"num_comments\":{\"isShown\":\"1\"}},\"table\":{\"id\":{\"isShown\":\"1\"},\"num_ratings\":{\"isShown\":\"1\"},\"title\":{\"isShown\":\"1\"},\"content\":{\"isShown\":\"1\"},\"hidden_status\":{\"isShown\":\"1\"},\"status_category\":{\"isShown\":\"1\"},\"type\":{\"isShown\":\"1\"},\"custom_category\":{\"isShown\":\"1\"},\"labels\":{\"isShown\":\"1\"},\"author_name\":{\"isShown\":\"1\"},\"num_comments\":{\"isShown\":\"1\"},\"date_created\":{\"isShown\":\"1\"},\"total_rating\":{\"isShown\":\"1\"},\"validating\":{\"isShown\":\"1\"}}}');
        "
        );
        // end of ArticlePendingCreate

        // AgentAlerts test data ------------------------------------------------------------------------------
        $this->getDb()->exec(
            "
            INSERT INTO `agent_alerts` (`id`, `person_id`, `typename`, `data`, `date_created`, `is_dismissed`)
            VALUES
                (1, 1, 'tickets', 0x613a31303a7b733a31323a224066657463685f7479706573223b613a333a7b733a363a227469636b6574223b733a31343a224465736b50524f3a5469636b6574223b733a393a22706572666f726d6572223b733a31343a224465736b50524f3a506572736f6e223b733a393a226c6f675f6974656d73223b733a31373a224465736b50524f3a5469636b65744c6f67223b7d733a363a227469636b6574223b693a3536373b733a393a22706572666f726d6572223b693a3532383b733a31333a2269735f6e65775f7469636b6574223b623a313b733a31383a2269735f6e65775f6167656e745f7265706c79223b623a303b733a31373a2269735f6e65775f6167656e745f6e6f7465223b623a303b733a31373a2269735f6e65775f757365725f7265706c79223b623a313b733a393a226c6f675f6974656d73223b613a383a7b693a303b693a313b693a313b693a323b693a323b693a333b693a333b693a343b693a343b693a353b693a353b693a363b693a363b693a373b693a373b693a383b7d733a31363a2262726f777365725f72656e6465726564223b733a3438393a223c6c690a09636c6173733d22696e73696465207469636b6574206e65772d7469636b6574207469636b65742d726f772d353637207469636b65742d353637220a09646174612d636c6173732d69643d227469636b65742d726f772d353637220a09646174612d747970653d227469636b657473220a09646174612d726f7574653d227469636b65743a2f696e6465782e7068702f6f6c642d6167656e742f7469636b6574732f353637220a09646174612d726f7574652d6e6f74616272656c6f61643d2231220a3e0a093c64697620636c6173733d226469736d697373223e3c6920636c6173733d2269636f6e2d62616e2d636972636c65223e3c2f693e3c2f6469763e0a093c74696d65206461746574696d653d22323031362d30312d32305430333a30363a32382b30303a3030223e3c2f74696d653e0a093c6269673e0a09093c7370616e20636c6173733d22726f772d6964223e233536373c2f7370616e3e0a090954657374204d657373616765202330333036202d2d20323031362d30312d3230202d2d2032380a093c2f6269673e0a093c736d616c6c3e0a0909090909202020202020202020202020094e6577207469636b6574206279205573657220287573657240666f6f6261722e636f6d290a0909090909093c2f736d616c6c3e0a3c2f6c693e0a223b733a31323a22407461726765745f6d617073223b613a313a7b733a373a2262726f77736572223b613a313a7b693a303b733a31363a2262726f777365725f72656e6465726564223b7d7d7d, '2016-01-20 03:06:28', 0),
                (2, 1, 'tickets', 0x613a31303a7b733a31323a224066657463685f7479706573223b613a333a7b733a363a227469636b6574223b733a31343a224465736b50524f3a5469636b6574223b733a393a22706572666f726d6572223b733a31343a224465736b50524f3a506572736f6e223b733a393a226c6f675f6974656d73223b733a31373a224465736b50524f3a5469636b65744c6f67223b7d733a363a227469636b6574223b693a3536383b733a393a22706572666f726d6572223b693a3532393b733a31333a2269735f6e65775f7469636b6574223b623a313b733a31383a2269735f6e65775f6167656e745f7265706c79223b623a303b733a31373a2269735f6e65775f6167656e745f6e6f7465223b623a303b733a31373a2269735f6e65775f757365725f7265706c79223b623a313b733a393a226c6f675f6974656d73223b613a383a7b693a303b693a393b693a313b693a31303b693a323b693a31313b693a333b693a31323b693a343b693a31333b693a353b693a31343b693a363b693a31353b693a373b693a31363b7d733a31363a2262726f777365725f72656e6465726564223b733a3439313a223c6c690a09636c6173733d22696e73696465207469636b6574206e65772d7469636b6574207469636b65742d726f772d353638207469636b65742d353638220a09646174612d636c6173732d69643d227469636b65742d726f772d353638220a09646174612d747970653d227469636b657473220a09646174612d726f7574653d227469636b65743a2f696e6465782e7068702f6f6c642d6167656e742f7469636b6574732f353638220a09646174612d726f7574652d6e6f74616272656c6f61643d2231220a3e0a093c64697620636c6173733d226469736d697373223e3c6920636c6173733d2269636f6e2d62616e2d636972636c65223e3c2f693e3c2f6469763e0a093c74696d65206461746574696d653d22323031362d30312d32305430353a30393a35302b30303a3030223e3c2f74696d653e0a093c6269673e0a09093c7370616e20636c6173733d22726f772d6964223e233536383c2f7370616e3e0a090954657374204d657373616765202330353039202d2d20323031362d30312d3230202d2d2035300a093c2f6269673e0a093c736d616c6c3e0a0909090909202020202020202020202020094e6577207469636b65742062792055736572312028757365723140666f6f6261722e636f6d290a0909090909093c2f736d616c6c3e0a3c2f6c693e0a223b733a31323a22407461726765745f6d617073223b613a313a7b733a373a2262726f77736572223b613a313a7b693a303b733a31363a2262726f777365725f72656e6465726564223b7d7d7d, '2016-01-20 05:09:50', 0),
                (3, 1, 'tickets', 0x613a31303a7b733a31323a224066657463685f7479706573223b613a333a7b733a363a227469636b6574223b733a31343a224465736b50524f3a5469636b6574223b733a393a22706572666f726d6572223b733a31343a224465736b50524f3a506572736f6e223b733a393a226c6f675f6974656d73223b733a31373a224465736b50524f3a5469636b65744c6f67223b7d733a363a227469636b6574223b693a3536393b733a393a22706572666f726d6572223b693a3533303b733a31333a2269735f6e65775f7469636b6574223b623a313b733a31383a2269735f6e65775f6167656e745f7265706c79223b623a303b733a31373a2269735f6e65775f6167656e745f6e6f7465223b623a303b733a31373a2269735f6e65775f757365725f7265706c79223b623a313b733a393a226c6f675f6974656d73223b613a383a7b693a303b693a31373b693a313b693a31383b693a323b693a31393b693a333b693a32303b693a343b693a32313b693a353b693a32323b693a363b693a32333b693a373b693a32343b7d733a31363a2262726f777365725f72656e6465726564223b733a3439313a223c6c690a09636c6173733d22696e73696465207469636b6574206e65772d7469636b6574207469636b65742d726f772d353639207469636b65742d353639220a09646174612d636c6173732d69643d227469636b65742d726f772d353639220a09646174612d747970653d227469636b657473220a09646174612d726f7574653d227469636b65743a2f696e6465782e7068702f6f6c642d6167656e742f7469636b6574732f353639220a09646174612d726f7574652d6e6f74616272656c6f61643d2231220a3e0a093c64697620636c6173733d226469736d697373223e3c6920636c6173733d2269636f6e2d62616e2d636972636c65223e3c2f693e3c2f6469763e0a093c74696d65206461746574696d653d22323031362d30312d32305430353a31303a30302b30303a3030223e3c2f74696d653e0a093c6269673e0a09093c7370616e20636c6173733d22726f772d6964223e233536393c2f7370616e3e0a090954657374204d657373616765202330353130202d2d20323031362d30312d3230202d2d2030300a093c2f6269673e0a093c736d616c6c3e0a0909090909202020202020202020202020094e6577207469636b65742062792055736572322028757365723240666f6f6261722e636f6d290a0909090909093c2f736d616c6c3e0a3c2f6c693e0a223b733a31323a22407461726765745f6d617073223b613a313a7b733a373a2262726f77736572223b613a313a7b693a303b733a31363a2262726f777365725f72656e6465726564223b7d7d7d, '2016-01-20 05:10:00', 0),
                (4, 1, 'tickets', 0x613a31303a7b733a31323a224066657463685f7479706573223b613a333a7b733a363a227469636b6574223b733a31343a224465736b50524f3a5469636b6574223b733a393a22706572666f726d6572223b733a31343a224465736b50524f3a506572736f6e223b733a393a226c6f675f6974656d73223b733a31373a224465736b50524f3a5469636b65744c6f67223b7d733a363a227469636b6574223b693a3536393b733a393a22706572666f726d6572223b693a3533303b733a31333a2269735f6e65775f7469636b6574223b623a303b733a31383a2269735f6e65775f6167656e745f7265706c79223b623a303b733a31373a2269735f6e65775f6167656e745f6e6f7465223b623a303b733a31373a2269735f6e65775f757365725f7265706c79223b623a313b733a393a226c6f675f6974656d73223b613a333a7b693a303b693a32353b693a313b693a32363b693a323b693a32373b7d733a31363a2262726f777365725f72656e6465726564223b733a3438373a223c6c690a09636c6173733d22696e73696465207469636b6574206e65772d7265706c79207469636b65742d726f772d353639207469636b65742d353639220a09646174612d636c6173732d69643d227469636b65742d726f772d353639220a09646174612d747970653d227469636b657473220a09646174612d726f7574653d227469636b65743a2f696e6465782e7068702f6f6c642d6167656e742f7469636b6574732f353639220a09646174612d726f7574652d6e6f74616272656c6f61643d2231220a3e0a093c64697620636c6173733d226469736d697373223e3c6920636c6173733d2269636f6e2d62616e2d636972636c65223e3c2f693e3c2f6469763e0a093c74696d65206461746574696d653d22323031362d30312d32305430353a31303a35362b30303a3030223e3c2f74696d653e0a093c6269673e0a09093c7370616e20636c6173733d22726f772d6964223e233536393c2f7370616e3e0a090954657374204d657373616765202330353130202d2d20323031362d30312d3230202d2d2030300a093c2f6269673e0a093c736d616c6c3e0a09092020202020202020202020204e65772075736572207265706c792062792055736572322028757365723240666f6f6261722e636f6d290a0909093c2f736d616c6c3e0a3c2f6c693e0a223b733a31323a22407461726765745f6d617073223b613a313a7b733a373a2262726f77736572223b613a313a7b693a303b733a31363a2262726f777365725f72656e6465726564223b7d7d7d, '2016-01-20 05:10:56', 0);
            "
        );
        // end of AgentAlerts

        ++$count;

        return $count;
    }
}
