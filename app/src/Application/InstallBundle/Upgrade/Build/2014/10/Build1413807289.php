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

class Build1413807289 extends AbstractBuild
{
    public function run()
    {
        $this->out("Table updates");

        // Experimental facebook tables
        $this->execMutateSql("CREATE TABLE facebook_apps (id INT AUTO_INCREMENT NOT NULL, app_id VARCHAR(255) NOT NULL, app_secret VARCHAR(256) DEFAULT NULL, name VARCHAR(256) DEFAULT NULL, icon_url VARCHAR(256) DEFAULT NULL, logo_url VARCHAR(256) DEFAULT NULL, UNIQUE INDEX UNIQ_3E57D977987212D (app_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        $this->execMutateSql("CREATE TABLE facebook_pages (id INT AUTO_INCREMENT NOT NULL, app_id INT DEFAULT NULL, graph_id VARCHAR(255) NOT NULL, page_token VARCHAR(256) DEFAULT NULL, user_token VARCHAR(256) DEFAULT NULL, user_graph_id VARCHAR(256) DEFAULT NULL, name VARCHAR(256) DEFAULT NULL, verify_token VARCHAR(256) DEFAULT NULL, picture_url VARCHAR(256) DEFAULT NULL, import_wall_posts TINYINT(1) NOT NULL, disable_own_wall_posts TINYINT(1) NOT NULL, import_direct_messages TINYINT(1) NOT NULL, is_enabled TINYINT(1) NOT NULL, is_connected TINYINT(1) NOT NULL, is_tested TINYINT(1) NOT NULL, date_user_token_received DATETIME DEFAULT NULL, date_created DATETIME NOT NULL, UNIQUE INDEX UNIQ_C5B2A27B99134837 (graph_id), INDEX IDX_C5B2A27B7987212D (app_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        $this->execMutateSql("ALTER TABLE facebook_pages ADD CONSTRAINT FK_C5B2A27B7987212D FOREIGN KEY (app_id) REFERENCES facebook_apps (id)");

        // Misc db changes
        $this->execMutateSql("ALTER TABLE agent_team_members DROP FOREIGN KEY FK_CC952C03217BBB47", true);
        $this->execMutateSql("ALTER TABLE agent_team_members DROP FOREIGN KEY FK_CC952C03296CD8AE", true);
        $this->execMutateSql("ALTER TABLE agent_team_members ADD CONSTRAINT FK_CC952C03217BBB47 FOREIGN KEY (person_id) REFERENCES people (id)", true);
        $this->execMutateSql("ALTER TABLE agent_team_members ADD CONSTRAINT FK_CC952C03296CD8AE FOREIGN KEY (team_id) REFERENCES agent_teams (id)", true);
        $this->execMutateSql("ALTER TABLE api_key_log CHANGE time time INT NOT NULL", true);
        $this->execMutateSql("ALTER TABLE log_round_robin CHANGE timestamp timestamp INT NOT NULL, CHANGE round_robin_id round_robin_id INT NOT NULL, CHANGE agent_id agent_id INT NOT NULL, CHANGE ticket_id ticket_id INT NOT NULL, CHANGE trigger_id trigger_id INT NOT NULL", true);
    }
}
