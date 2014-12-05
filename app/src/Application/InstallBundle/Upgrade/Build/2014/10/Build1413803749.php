<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\ORM\EntityManager;

class Build1413803749 extends AbstractBuild
{
    public function run()
    {
        $this->out("Upgrade usersources to new auth settings");

        $did_do = $this->container->getDb()->fetchColumn("SELECT data FROM install_data WHERE build = 1416914310 AND name = 'did_app_triggers'");
        if (!$did_do) {
            $this->out("!!!!!!!!!");
            $this->out("Add app_packages.trigger_events");
            $this->execMutateSql("ALTER TABLE app_packages ADD trigger_events LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)'", true);

            $this->out("Add ticket_triggers.by_app_mode");
            $this->execMutateSql("ALTER TABLE ticket_triggers ADD by_app_mode LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)'", true);
            $this->container->getDb()->insertIgnore('install_data', array('build' => '1416914310', 'name' => 'did_app_triggers', 'data' => '1'));
        }

        $userType = Usersource::TYPE_USER;

        $did_do = $this->container->getDb()->fetchColumn("SELECT data FROM install_data WHERE build = 1413803749 AND name = 'did_pre_alter'");
        if (!$did_do) {
            $this->execMutateSql("ALTER TABLE usersources  ADD type VARCHAR(25) NOT NULL, ADD is_sso_auto TINYINT(1) NOT NULL, ADD is_sso_background TINYINT(1) NOT NULL");
            $this->execMutateSql("ALTER TABLE usersources ADD agent_permission_group_id INT DEFAULT NULL, ADD auto_agent TINYINT(1) NOT NULL");
            $this->execMutateSql("ALTER TABLE usersources ADD CONSTRAINT FK_4E3C994CF9C72B85 FOREIGN KEY (agent_permission_group_id) REFERENCES usergroups (id) ON DELETE SET NULL");
            $this->execMutateSql("CREATE INDEX IDX_4E3C994CF9C72B85 ON usersources (agent_permission_group_id)");
        }

        $this->execMutateSql("UPDATE usersources SET type = '$userType'");

        $em = $this->container->getEm();

        $this->setupDeskProUsersource($userType, $em);
    }


    private function setupDeskProUsersource($type, EntityManager $em)
    {
        $enabled = $this->container->getSetting('core.deskpro_source_enabled') ? 1 : 0;

        $deskProUsers = new Usersource();
        $deskProUsers->type = $type;
        $deskProUsers->source_type = 'Application\\DeskPRO\\Usersource\\Adapter\\DeskPRO';
        $deskProUsers->is_enabled = $enabled;
        $deskProUsers->display_order = -10; // ensure #1 order (initially!)
        $deskProUsers->title = 'DeskPRO';
        $deskProUsers->options = array();

        $em->persist($deskProUsers);
        $em->flush($deskProUsers);
        $em->clear();
        $this->runNext();

        return $deskProUsers;
    }


    public function runNext()
    {
        $this->out("Creates needed agent app instances and usersources and changes associations where necessary");
        $em = $this->container->getEm();

        /** @var \Application\DeskPRO\Usersource\UsersourceManager $usersourceManager */
        $usersourceManager = $this->container->getSystemService('usersource_manager');
        $userUsersources   = $usersourceManager->getAll()->configuredForUsers(true);

        /** @var \Application\DeskPRO\Entity\Usersource $userUsersource */
        foreach ($userUsersources as $userUsersource) {

            $agentApp = null;
            if ($userUsersource->app) {
                $agentApp = $this->copyAppInstance($userUsersource->app);
                $em->persist($agentApp);
                $em->flush($agentApp);
            }

            $agentDuplication                    = new Usersource();
            $agentDuplication->type              = Usersource::TYPE_AGENT;
            $agentDuplication->display_order     = $userUsersource->display_order;
            $agentDuplication->app               = $agentApp;
            $agentDuplication->is_enabled        = $userUsersource->is_enabled;
            $agentDuplication->lost_password_url = $userUsersource->lost_password_url;
            $agentDuplication->source_type       = $userUsersource->source_type;
            $agentDuplication->title             = $userUsersource->title;
            $agentDuplication->options           = $userUsersource->options;
            $em->persist($agentDuplication);
            $em->flush($agentDuplication);

            $this->changeUsersourcesFromUserToAgent($userUsersource, $agentDuplication);
        }
    }

    private function changeUsersourcesFromUserToAgent(Usersource $userUsersource, Usersource $agentDuplication)
    {
        $userUsersourceId  = $userUsersource->id;
        $agentUsersourceId = $agentDuplication->id;

        $this->execMutateSql(
            "
            UPDATE person_usersource_assoc pua
            LEFT JOIN people ON pua.person_id = people.id
            SET pua.usersource_id = $agentUsersourceId
            WHERE people.is_agent = 1 AND pua.usersource_id = $userUsersourceId
        "
        );
    }

    private function copyAppInstance(AppInstance $originApp)
    {
        $app = new AppInstance();
        $app->setSettings($originApp->getSettings());
        $app->title   = $originApp->title;
        $app->package = $originApp->package;

        return $app;
    }
}
