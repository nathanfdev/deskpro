<?php

namespace DpTestSrc\TestBundle\DataSet;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\PersonUsersourceAssoc;
use Application\DeskPRO\Entity\Usersource;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DpTestSrc\TestBundle\Mock\Usersource\CallbackAdapterMock;
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

        //------------------------------
        // Init data
        //------------------------------

        $admin = $this->addUser(
            UserDetailsRepo::ADMIN_FIRST_NAME,
            UserDetailsRepo::ADMIN_LAST_NAME,
            UserDetailsRepo::ADMIN_EMAIL,
            UserDetailsRepo::ADMIN_PASS,
            true,
            true
        );

        $this->addUser(
            UserDetailsRepo::AGENT_FIRST_NAME,
            UserDetailsRepo::AGENT_LAST_NAME,
            UserDetailsRepo::AGENT_EMAIL,
            UserDetailsRepo::AGENT_PASS,
            true,
            false
        );

        $this->addUser(
            UserDetailsRepo::USER_FIRST_NAME,
            UserDetailsRepo::USER_LAST_NAME,
            UserDetailsRepo::USER_EMAIL,
            UserDetailsRepo::USER_PASS,
            false,
            false
        );

        $this->addUser(
            UserDetailsRepo::DELETED_AGENT_FIRST_NAME,
            UserDetailsRepo::DELETED_AGENT_LAST_NAME,
            UserDetailsRepo::DELETED_AGENT_EMAIL,
            UserDetailsRepo::DELETED_AGENT_PASS,
            true,
            false,
            true
        );

        // we need a brand here, but the other db's use data.php which has all the brands
        $brand = new Brand();
        $brand->setName('default');

        $themeSet = new ThemeSet();
        $themeSet->setThemeId('standard');
        $this->getEm()->persist($themeSet);

        $editThemeSet = new ThemeSet();
        $editThemeSet->setThemeId('standard');
        $this->getEm()->persist($editThemeSet);

        $brand->setThemeSet($themeSet);
        $brand->setEditThemeSet($editThemeSet);

        $this->getEm()->persist($brand);
        $this->getEm()->flush();

        // Default language --------------------------------------------------------------------------------------------
        $this->getDb()->exec(
            <<<'SQL'
            INSERT INTO `languages`
                (`id`, `sys_name`, `title`, `base_filepath`, `locale`, `flag_image`, `is_rtl`, `has_user`,
                 `has_agent`, `has_admin`)
            VALUES
                (1, 'default', 'English', NULL, 'en-US', '', 0, 1, 1, 1),
                (2, 'french', 'Français', '%DP_ROOT%/locales/fr', 'fr', '', '0', '1', '1', '1'),
                (3, 'russian', 'Pусский', '%DP_ROOT%/locales/ru', 'ru', '', '0', '1', '1', '1')

            ;
SQL
        );

        // this will be refactored into a better "entity creator" once the api data set needs more elaborate data
        // we need some deps, and some other entities
        $dep1        = Department::createTicketDepartment();
        $dep1->title = 'sales';
        $dep1->addBrand($brand);
        $dep2        = Department::createTicketDepartment();
        $dep2->title = 'support';
        $dep2->addBrand($brand);
        $dep3        = Department::createChatDepartment();
        $dep3->title = 'support';
        $dep3->addBrand($brand);

        $team       = new AgentTeam();
        $team->name = 'test team';

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
        $em->persist($team);
        $em->persist($dep1);
        $em->persist($dep2);
        $em->persist($dep3);
        $em->persist($article);
        $em->persist($brand);
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
            $deskProUsers->options       = [
                'reg_enabled' => true,
            ];
            $this->getEm()->persist($deskProUsers);

            $googlePlusUs                = new Usersource();
            $googlePlusUs->type          = $type;
            $googlePlusUs->source_type   = CallbackAdapterMock::class;
            $googlePlusUs->is_enabled    = true;
            $googlePlusUs->display_order = 0; // ensure #1 order (initially!)
            $googlePlusUs->title         = 'GooglePlus';
            $googlePlusUs->options       = [];
            $this->getEm()->persist($googlePlusUs);

            $assoc = new PersonUsersourceAssoc();
            $assoc->setPerson($admin);
            $assoc->setUsersource($googlePlusUs);
            $assoc->setIdentity(1);
            $assoc->setIdentityFriendly('');
            $this->getEm()->persist($assoc);
        }

        $fbUserUs                = new Usersource();
        $fbUserUs->type          = 'user';
        $fbUserUs->source_type   = CallbackAdapterMock::class;
        $fbUserUs->is_enabled    = false;
        $fbUserUs->display_order = 10; // ensure #1 order (initially!)
        $fbUserUs->title         = 'Facebook';
        $fbUserUs->options       = [];
        $this->getEm()->persist($fbUserUs);

        $this->getEm()->flush();

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
                (2, 4)
            ;
        "
        );
        // end of "/agent_teams"

        $this->getDb()->exec(
            "
            REPLACE INTO `settings` (`name`, `value`)
            VALUES
                ('portal.default_brand', '".$brand->getId()."'),
                ('core.app_secret', 'YXI5Z2HSQ9IF8KROQQ63GL4FB4CV57ZIIZ7CZO68FUDYBZIP2M'),
                ('core.cron_logreport.cli-phperr.log', '1380716762'),
                ('core.default_from_email', 'noreply@example.com'),
                ('core.default_timezone', 'UTC'),
                ('core.deskpro_build', '".time()."'),
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

        // "/user_chats" endpoint test data ----------------------------------------------------------------------------
        $this->getDb()->exec(
            "
            INSERT INTO `chat_conversations`
                (`department_id`, `agent_id`, `subject`, `status`, `person_name`, `person_email`, `rating_comment`,
                 `is_agent`, `is_window`, `date_created`, `should_send_transcript`, `total_to_ended`, `ended_by`)

            VALUES

                (1, 1, 'Test chat 1', 'test', 'test', 'test', '', 0, 1, '2010-08-01 10:19:00', 1, 1, 'test'),
                (1, 1, 'Test chat 2', 'test', 'test', 'test', '', 0, 1, '2011-08-02 10:19:00', 1, 1, 'test'),
                (1, 2, 'Test chat 3', 'test', 'test', 'test', '', 0, 1, '2015-08-03 10:19:00', 1, 1, 'test'),
                (2, 2, 'Test chat 4', 'test', 'test', 'test', '', 0, 1, '2015-08-04 10:19:00', 1, 1, 'test'),
                (2, 2, 'Test chat 5', 'test', 'test', 'test', '', 0, 1, '2015-08-05 10:19:00', 1, 1, 'test')
            ;
        "
        );
        // end of "/user_chats" endpoint test data

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

            INSERT INTO `feedback_category2usergroup`
                (`category_id`, `usergroup_id`)
            VALUES
                (1, 1),
                (2, 1),
                (3, 1),
                (1, 2),
                (2, 2),
                (3, 2),
                (1, 3),
                (1, 4),
                (2, 4),
                (1, 5),
                (2, 5),
                (3, 5),

                (1, 7),
                (1, 8),
                (2, 7),
                (2, 8),
                (3, 7),
                (3, 8)
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

        // permissions for agent usergroups
        $this->getDb()->exec(
            "
            INSERT INTO `permissions`
              (`usergroup_id`, `person_id`, `name`, `value`, `is_active`)
            VALUES
              ('8', NULL, 'articles.use', 1, 1),
              ('8', NULL, 'feedback.use', 1, 1),
              ('8', NULL, 'downloads.use', 1, 1),
              ('8', NULL, 'news.use', 1, 1),
              ('8', NULL, 'chat.use', 1, 1),
              ('8', NULL, 'guides.use', 1, 1),

              ('7', NULL, 'articles.use', 1, 1),
              ('7', NULL, 'feedback.use', 1, 1),
              ('7', NULL, 'downloads.use', 1, 1),
              ('7', NULL, 'news.use', 1, 1),
              ('7', NULL, 'chat.use', 1, 1);
            "
        );

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

        // Ticket filter sets test data ----------------------------------------------------------------------------------
        $this->getDb()->exec("
            INSERT INTO `ticket_filters2_sets` (`id`, `title`, `display_order`, `is_global`)
            VALUES
                (1,'Inbox',0,1)
        ");

        $this->getDb()->exec("
            INSERT INTO `ticket_filters2` (`id`, `title`, `query`, `is_enabled`)
            VALUES
                (1,'Assigned To Me','ticket.status = \'awaiting_agent\' AND ticket.agent = \$me',1),
                (2,'Tickets I Follow','ticket.status = \'awaiting_agent\' AND ticket.followers HAS \$me',1),
                (3,'Assigned To Team','ticket.status = \'awaiting_agent\' AND ticket.agent_team IN \$my_teams',1),
                (4,'Unassigned','ticket.status = \'awaiting_agent\' AND ticket.agent IS EMPTY',1),
                (5,'All Awaiting Agent','ticket.status = \'awaiting_agent\'',1)
        ");

        $this->getDb()->exec('
            INSERT INTO `ticket_filters2_assoc` (`filter_set_id`, `filter_id`, `display_order`)
            VALUES
                (1,1,10),
                (1,2,20),
                (1,3,30),
                (1,4,40),
                (1,5,50)
        ');

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
                (`id`, `brand_id`, `parent_id`, `is_agent`, `is_book`, `template_suffix`, `title`, `slug`, `display_order`, `depth`)
            VALUES
                (1, 1, NULL, 1, 1, NULL, 'Test Category #1', '1', 1, 1),
                (2, 1, NULL, 0, 0, NULL, 'Test Category #2', '2', 2, 1),
                (3, 1, 1, 1, 1, NULL, 'Test Category #3', '3', 1, 1),
                (4, 1, 1, 1, 1, NULL, 'Test Category #4', '4', 1, 1),
                (5, 1, 2, 1, 1, NULL, 'Test Category #5', '5', 1, 1),
                (6, 1, 2, 1, 1, NULL, 'Test Category #6', '6', 1, 1),
                (7, 1, 3, 1, 1, NULL, 'Test Category #7', '7', 1, 1),
                (8, 1, 3, 1, 1, NULL, 'Test Category #8', '8', 1, 1),
                (9, 1, 7, 1, 1, NULL, 'Test Category #9', '9', 1, 1),
                (10, 1, 7, 1, 1, NULL, 'Test Category #10', '10', 1, 1),
                (11, 1, 7, 1, 1, NULL, 'Test Category #11', '11', 1, 1)
            ;

            INSERT INTO `article_category2usergroup`
                (`category_id`, `usergroup_id`)
            VALUES
                (1, 7),
                (2, 7),
                (3, 7),
                (4, 7),
                (5, 7),
                (6, 7),
                (7, 7),
                (8, 7),
                (9, 7),
                (10, 7),
                (11, 7)
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
                (`id`, `brand_id`, `parent_id`, `title`, `slug`, `display_order`, `depth`)
            VALUES
                (1, 1, NULL, 'Test Category #1', '1', 1, 1),
                (2, 1, NULL, 'Test Category #2', '2', 2, 1),
                (3, 1, 1, 'Test Category #3', '3', 2, 1),
                (4, 1, 1, 'Test Category #4', '4', 2, 1),
                (5, 1, 2, 'Test Category #5', '5', 2, 1),
                (6, 1, 2, 'Test Category #6', '6', 2, 1),
                (7, 1, 3, 'Test Category #7', '7', 2, 1),
                (8, 1, 3, 'Test Category #8', '8', 2, 1),
                (9, 1, 7, 'Test Category #9', '9', 2, 1),
                (10, 1, 7, 'Test Category #10', '10', 2, 1),
                (11, 1, 7, 'Test Category #11', '11', 2, 1)
            ;


            INSERT INTO `news_category2usergroup`
                (`category_id`, `usergroup_id`)
            VALUES
                (1, 7),
                (2, 7),
                (3, 7),
                (4, 7),
                (5, 7),
                (6, 7),
                (7, 7),
                (8, 7),
                (9, 7),
                (10, 7),
                (11, 7)
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
                (`id`, `brand_id`, `parent_id`, `title`, `slug`, `display_order`, `depth`)
            VALUES
                (1, 1, NULL, 'Test Category #1', '1', 1, 1),
                (2, 1, NULL, 'Test Category #2', '2', 2, 1),
                (3, 1, 1, 'Test Category #3', '3', 2, 1),
                (4, 1, 1, 'Test Category #4', '4', 2, 1),
                (5, 1, 2, 'Test Category #5', '5', 2, 1),
                (6, 1, 2, 'Test Category #6', '6', 2, 1),
                (7, 1, 3, 'Test Category #7', '7', 2, 1),
                (8, 1, 3, 'Test Category #8', '8', 2, 1),
                (9, 1, 7, 'Test Category #9', '9', 2, 1),
                (10, 1, 7, 'Test Category #10', '10', 2, 1),
                (11, 1, 7, 'Test Category #11', '11', 2, 1)
            ;

            INSERT INTO `download_category2usergroup`
                (`category_id`, `usergroup_id`)
            VALUES
                (1, 7),
                (2, 7),
                (3, 7),
                (4, 7),
                (5, 7),
                (6, 7),
                (7, 7),
                (8, 7),
                (9, 7),
                (10, 7),
                (11, 7)
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
                (`id`, `definition_id`, `word`, `brand_id`)
            VALUES
                (1, 1, 'Word 1', 1),
                (2, 1, 'Word 2', 1)
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

        // PersonPref test data ------------------------------------------------------------------------------
        $this->getDb()->exec(
            "
            INSERT INTO `people_prefs` (`person_id`, `name`, `value_str`)
            VALUES
              (1, 'agent.ui.flag.green', 'First custom'),
              (1, 'agent.ui.flag.pink', 'Second custom'),
              (1, 'agent.ui.flag.red', 'Third custom');
        "
        );

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

        // SLAs
        $this->getDb()->exec(
            <<<'SQL'
            INSERT INTO `slas` (`id`, `title`, `sla_type`,`active_time`, `work_start`, `work_end`, `work_days`, `apply_type`, `warn_time`, `warn_time_unit`, `fail_time`, `fail_time_unit`)
            VALUES
                (1, 'First', 'first_response', 'default', 60 * 60 * 10, 60 * 60 * 18, '1,2,3,4,5,6', 'all', 1, 'hours', 1, 'hours'),
                (2, 'Second', 'resolution', 'default', 60 * 60 * 10, 60 * 60 * 18, '1,2,3,4,5,6', 'auto', 1, 'days', 1, 'days'),
                (3, 'Third', 'waiting_time', 'default', 60 * 60 * 10, 60 * 60 * 18, '1,2,3,4,5,6', 'manual', 1, 'hours', 1, 'hours')
            ;
SQL
        );

        ++$count;

        return $count;
    }
}
