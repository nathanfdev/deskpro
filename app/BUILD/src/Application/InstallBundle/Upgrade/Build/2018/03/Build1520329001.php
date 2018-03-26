<?php

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

        $this->execDbQuery('default', '
            CREATE TABLE `ticket_filters2_sets` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `title` varchar(255) NOT NULL,
              `display_order` int(11) NOT NULL,
              `share_mode` varchar(50) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
        ');
        $this->execDbQuery('default', 'CREATE TABLE ticket_filters2_set_teams (filter_set_id INT NOT NULL, agent_id INT NOT NULL, INDEX IDX_BF123BB83DD05366 (filter_set_id), INDEX IDX_BF123BB83414710B (agent_id), PRIMARY KEY(filter_set_id, agent_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execDbQuery('default', 'CREATE TABLE ticket_filters2_set_agents (filter_set_id INT NOT NULL, person_id INT NOT NULL, INDEX IDX_35B5990F3DD05366 (filter_set_id), INDEX IDX_35B5990F217BBB47 (person_id), PRIMARY KEY(filter_set_id, person_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execDbQuery('default', '
            CREATE TABLE `ticket_filters2` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `filter_set_id` int(11) DEFAULT NULL,
              `title` varchar(255) NOT NULL,
              `query` varchar(255) NOT NULL,
              `display_order` int(11) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `IDX_CCDCA763DD05366` (`filter_set_id`),
              CONSTRAINT `FK_CCDCA763DD05366` FOREIGN KEY (`filter_set_id`) REFERENCES `ticket_filters2_sets` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
        ');
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
