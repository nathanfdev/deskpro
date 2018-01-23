<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace Application\InstallBundle\Upgrade\Build;

class Build1520329001 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'DROP TABLE IF EXISTS ticket_filter_preferences');
        $this->execDbQuery('default', 'DROP TABLE IF EXISTS ticket_filter_views');
        $this->execDbQuery('default', 'DROP TABLE IF EXISTS custom_ticket_filters');
        $this->execDbQuery('default', 'DROP TABLE IF EXISTS filter_set_agents');
        $this->execDbQuery('default', 'DROP TABLE IF EXISTS ticket_filter_sets');

        $this->execDbQuery('default', 'CREATE TABLE ticket_filters2_sets (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, display_order INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execDbQuery('default', 'CREATE TABLE ticket_filters2_set_teams (filter_set_id INT NOT NULL, agent_id INT NOT NULL, INDEX IDX_BF123BB83DD05366 (filter_set_id), INDEX IDX_BF123BB83414710B (agent_id), PRIMARY KEY(filter_set_id, agent_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execDbQuery('default', 'CREATE TABLE ticket_filters2_set_agents (filter_set_id INT NOT NULL, person_id INT NOT NULL, INDEX IDX_35B5990F3DD05366 (filter_set_id), INDEX IDX_35B5990F217BBB47 (person_id), PRIMARY KEY(filter_set_id, person_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execDbQuery('default', 'CREATE TABLE ticket_filters2 (id INT AUTO_INCREMENT NOT NULL, filter_set_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, term VARCHAR(255) NOT NULL, display_order INT NOT NULL, INDEX IDX_CCDCA763DD05366 (filter_set_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execDbQuery('default', 'ALTER TABLE ticket_filters2_set_teams ADD CONSTRAINT FK_BF123BB83DD05366 FOREIGN KEY (filter_set_id) REFERENCES ticket_filters2_sets (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE ticket_filters2_set_teams ADD CONSTRAINT FK_BF123BB83414710B FOREIGN KEY (agent_id) REFERENCES agent_teams (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE ticket_filters2_set_agents ADD CONSTRAINT FK_35B5990F3DD05366 FOREIGN KEY (filter_set_id) REFERENCES ticket_filters2_sets (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE ticket_filters2_set_agents ADD CONSTRAINT FK_35B5990F217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE ticket_filters2 ADD CONSTRAINT FK_CCDCA763DD05366 FOREIGN KEY (filter_set_id) REFERENCES ticket_filters2_sets (id) ON DELETE CASCADE');
    }

    public function run()
    {
    }
}
