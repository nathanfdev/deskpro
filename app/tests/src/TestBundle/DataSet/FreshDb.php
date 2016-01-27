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

use Application\InstallBundle\Data\DefaultDataProcessor;
use DpTestSrc\TestBundle\UserDetailsRepo;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Class FreshDb.
 */
class FreshDb extends AbstractDbSet
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'fresh';
    }

    /**
     * {@inheritdoc}
     */
    protected function installSet()
    {
        $this->loadFixtures();

        $count = 0;

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

        $this->getDb()->insert('permissions', array('person_id' => $admin->id, 'name' => 'admin.use', 'value' => 1));

        // Install data stuff
        $AGENTGROUP_ALL     = null; // should be defined by the time we finish processing data.php
        $USERGROUP_EVERYONE = null; // should be defined by the time we finish processing data.php
        $AGENT              = $admin; // can be used in data.php
        $WEB_INSTALL        = true;
        $IMPORT_INSTALL     = false;

        $install_data = new \Application\InstallBundle\Install\InstallDataReader(DP_ROOT.'/src/Application/InstallBundle/Data/data.php');
        $translate    = $this->getContainer()->get('deskpro.core.translate');

        foreach ($install_data as $php) {
            eval($php);
        }

        $em->flush();

        \Application\DeskPRO\DataSync\AbstractDataSync::syncAllBaseToLive();

        $data_proc = new DefaultDataProcessor($this->getContainer());
        $data_proc->runInstall();

        // For the all agent group, fetch permissions from the template
        if ($AGENTGROUP_ALL) {
            $ch = new \Application\DeskPRO\ORM\CollectionHelper($admin, 'usergroups');
            $ch->setCollection(array($AGENTGROUP_ALL));
            $em->persist($admin);
            $em->flush();
        }

        if ($USERGROUP_EVERYONE) {
            $scanner = new \Application\InstallBundle\Data\UserGroupPermScanner();
            foreach ($scanner->getNames() as $p_name) {
                $p            = new \Application\DeskPRO\Entity\Permission();
                $p->usergroup = $USERGROUP_EVERYONE;
                $p->name      = $p_name;
                $p->value     = 1;
                $em->persist($p);
            }
            $em->flush();
        }

        $data_init             = new \Application\InstallBundle\Data\DataInitializer($this->getContainer());
        $data_init->admin_user = $admin;
        $data_init->run();
// initial settings so we are "installed"
        $this->getDb()->exec("
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
                ('core.license', 'TlZNVi0wMTEyLUZVVVNFVEJHVFJNRU9KQlNHVlJNUVNTUgERC3\r\nlkZGRncEQKPwB2IyU+LiJjOgZ9FhE8ARdRIQ4OCR8seUR0ZRUZ\r\nJi9+cQB4eTF5ZjQ3P2J5TXYxdREHWzB/a1xiVQ0KeQdqMS5Qf1\r\nYtWXwZagd5DX9OCxASXzAzNGJmGTE7HhAKEBBnODZiGyYGAXVt\r\nLh8TKxcMQyFbKiAhP08aEFoECSM4TQkmMS8mEXJ1UQQINRcsAG\r\noHPBBxZxcFP1l7Uw8TJwseDn1IXAI5WwxLfVQoASkUClloBy93\r\nUEF2XFMQCwYFSC9aewFYHwJVeV0RAAonCEkhIzkjHn8WWSkRPn\r\ncpVyxrMQw6fARnIk8TDQcQCGcZRSombUhedVMENwhxUmpTLUIV\r\nZHRUflZ5UAhnAVs0CyhTZgspTkUIfQVdNWA'),
                ('core.setup_initial', '1'),
                ('core.task_completed_add_ticketfield', '".time()."'),
                ('core.twitter_last_cleanup', '".time()."'),
                ('core.use_agent_team', '1'),
                ('core_tickets.enable_like_search_auto', '1'),
                ('user.kb_subscriptions_last', '".time()."');
        ");

        // disable http cache
        $this->getDb()->exec("
            REPLACE INTO `settings` (`name`, `value`)
            VALUES
                ('portal.http_cache_last_modified', '0'),
                ('portal.http_cache_etags', '0'),
                ('portal.smaxage_guest_tag', '0'),
                ('portal.smaxage_guest_page', '0'),
                ('portal.smaxage_user_page', '0'),
                ('portal.smaxage_user_tag', '0');
        ");

        // We already have themes 1(standard) and 2(sidebar) from BrandFixtures of the normal install
        // We already have brand1 (standard theme) from BrandFixtures too
        // So just an extra theme for Sidebar
        $this->getDb()->exec("
            INSERT INTO `brands` (`id`, `name`, `theme_set_id`)
            VALUES
                (2, 'Brand With Sidebar Theme', 2)
        ");

        $perms = [
            [
                'department_id' => 1,
                'usergroup_id'  => 1, // everyone
                'app'           => 'tickets',
                'name'          => 'full',
                'value'         => 1,
            ],
            [
                'department_id' => 2,
                'usergroup_id'  => 1, // everyone
                'app'           => 'tickets',
                'name'          => 'full',
                'value'         => 1,
            ],
        ];

        $this->getDb()->batchInsert('department_permissions', $perms, true);

        $USERGROUP_EVERYONE = $em->getRepository('DeskPRO:Usergroup')->findOneBy(
            array('sys_name' => 'everyone')
        );
        $em->flush();

        if (!empty($USERGROUP_EVERYONE)) {
            $scanner = new \Application\InstallBundle\Data\UserGroupPermScanner();
            foreach ($scanner->getNames() as $p_name) {
                $p            = new \Application\DeskPRO\Entity\Permission();
                $p->usergroup = $USERGROUP_EVERYONE;
                $p->name      = $p_name;
                $p->value     = 1;
                $em->persist($p);
            }
            $em->flush();
        }

        ++$count;

        return $count;
    }

    private function loadFixtures()
    {
        $app = new Application($this->getContainer()->get('kernel'));

        $fixtures = [
            DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/DataFixtures/InstallFixtures',
            DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/DataFixtures/SeedFixtures',
        ];
        foreach ($fixtures as $path) {
            $app->setAutoExit(false);
            $input = new ArrayInput(array(
                'command'          => 'doctrine:fixtures:load',
                '--fixtures'       => $path,
                '--no-interaction' => true,
                '--append'         => true,
            ));
            $app->run($input);
        }
    }
}
