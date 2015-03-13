<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
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
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

class Build1426256649 extends AbstractBuild
{
    public function run()
    {
        $this->out("new filters");
        $this->execMutateSql(
            "CREATE TABLE filters (id INT AUTO_INCREMENT NOT NULL, filter_set_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, display_order INT NOT NULL, INDEX IDX_7877678D3DD05366 (filter_set_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci"
        );
        $this->execMutateSql(
            "CREATE TABLE filter_preferences (id INT AUTO_INCREMENT NOT NULL, filter_id INT DEFAULT NULL, filter_view_id INT DEFAULT NULL, agent_id INT DEFAULT NULL, display_order INT NOT NULL, main_grouping VARCHAR(255) NOT NULL, result_grouping VARCHAR(255) NOT NULL, show_sla TINYINT(1) NOT NULL, INDEX IDX_90337324D395B25E (filter_id), INDEX IDX_90337324B4856FA2 (filter_view_id), INDEX IDX_903373243414710B (agent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci"
        );
        $this->execMutateSql(
            "CREATE TABLE filter_sets (id INT AUTO_INCREMENT NOT NULL, private_agent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, display_order INT NOT NULL, is_default TINYINT(1) NOT NULL, INDEX IDX_FCB722A89382AEA6 (private_agent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci"
        );
        $this->execMutateSql(
            "CREATE TABLE filter_set_agents (filter_set_id INT NOT NULL, agent_id INT NOT NULL, INDEX IDX_EFBEEBF63DD05366 (filter_set_id), INDEX IDX_EFBEEBF63414710B (agent_id), PRIMARY KEY(filter_set_id, agent_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci"
        );
        $this->execMutateSql(
            "CREATE TABLE filter_views (id INT AUTO_INCREMENT NOT NULL, filter_id INT DEFAULT NULL, agent_id INT DEFAULT NULL, type VARCHAR(10) NOT NULL, fields LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', icon_fields LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', options LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', INDEX IDX_38416F78D395B25E (filter_id), INDEX IDX_38416F783414710B (agent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci"
        );
        $this->execMutateSql(
            "ALTER TABLE filters ADD CONSTRAINT FK_7877678D3DD05366 FOREIGN KEY (filter_set_id) REFERENCES filter_sets (id)"
        );
        $this->execMutateSql(
            "ALTER TABLE filter_preferences ADD CONSTRAINT FK_90337324D395B25E FOREIGN KEY (filter_id) REFERENCES filters (id)"
        );
        $this->execMutateSql(
            "ALTER TABLE filter_preferences ADD CONSTRAINT FK_90337324B4856FA2 FOREIGN KEY (filter_view_id) REFERENCES filter_views (id)"
        );
        $this->execMutateSql(
            "ALTER TABLE filter_preferences ADD CONSTRAINT FK_903373243414710B FOREIGN KEY (agent_id) REFERENCES people (id)"
        );
        $this->execMutateSql(
            "ALTER TABLE filter_sets ADD CONSTRAINT FK_FCB722A89382AEA6 FOREIGN KEY (private_agent_id) REFERENCES people (id)"
        );
        $this->execMutateSql(
            "ALTER TABLE filter_set_agents ADD CONSTRAINT FK_EFBEEBF63DD05366 FOREIGN KEY (filter_set_id) REFERENCES filter_sets (id)"
        );
        $this->execMutateSql(
            "ALTER TABLE filter_set_agents ADD CONSTRAINT FK_EFBEEBF63414710B FOREIGN KEY (agent_id) REFERENCES people (id)"
        );
        $this->execMutateSql(
            "ALTER TABLE filter_views ADD CONSTRAINT FK_38416F78D395B25E FOREIGN KEY (filter_id) REFERENCES filters (id)"
        );
        $this->execMutateSql(
            "ALTER TABLE filter_views ADD CONSTRAINT FK_38416F783414710B FOREIGN KEY (agent_id) REFERENCES people (id)"
        );
    }
}