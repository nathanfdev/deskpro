<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1523890502 extends AbstractBuild implements BlockingBuildInterface
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

        $queries = ['create' => [], 'alter' => []];

        $queries['create'][] = 'CREATE TABLE ticket_filters2_sets (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, display_order INT NOT NULL, is_global TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';
        $queries['create'][] = 'CREATE TABLE ticket_filters2_set_teams (filter_set_id INT NOT NULL, agent_team_id INT NOT NULL, INDEX IDX_BF123BB83DD05366 (filter_set_id), INDEX IDX_BF123BB83414710B (agent_team_id), PRIMARY KEY(filter_set_id, agent_team_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';
        $queries['create'][] = 'CREATE TABLE ticket_filters2_set_agents (filter_set_id INT NOT NULL, person_id INT NOT NULL, INDEX IDX_35B5990F3DD05366 (filter_set_id), INDEX IDX_35B5990F217BBB47 (person_id), PRIMARY KEY(filter_set_id, person_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';
        $queries['create'][] = 'CREATE TABLE ticket_filters2_assoc (filter_set_id INT NOT NULL, filter_id INT NOT NULL, display_order INT NOT NULL, INDEX IDX_2480A943DD05366 (filter_set_id), INDEX IDX_2480A94D395B25E (filter_id), PRIMARY KEY(filter_set_id, filter_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';
        $queries['create'][] = 'CREATE TABLE ticket_filters2 (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, query VARCHAR(255) NOT NULL, is_enabled TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';
        $queries['alter'][]  = 'ALTER TABLE ticket_filters2_set_teams ADD CONSTRAINT FK_BF123BB83DD05366 FOREIGN KEY (filter_set_id) REFERENCES ticket_filters2_sets (id) ON DELETE CASCADE, ADD CONSTRAINT FK_BF123BB83414710B FOREIGN KEY (agent_team_id) REFERENCES agent_teams (id) ON DELETE CASCADE';
        $queries['alter'][]  = 'ALTER TABLE ticket_filters2_set_agents ADD CONSTRAINT FK_35B5990F3DD05366 FOREIGN KEY (filter_set_id) REFERENCES ticket_filters2_sets (id) ON DELETE CASCADE, ADD CONSTRAINT FK_35B5990F217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE';
        $queries['alter'][]  = 'ALTER TABLE ticket_filters2_assoc ADD CONSTRAINT FK_2480A943DD05366 FOREIGN KEY (filter_set_id) REFERENCES ticket_filters2_sets (id) ON DELETE CASCADE, ADD CONSTRAINT FK_2480A94D395B25E FOREIGN KEY (filter_id) REFERENCES ticket_filters2 (id) ON DELETE CASCADE';

        $this->execDbTableDefArray('default', $queries);
    }

    public function run()
    {
    }
}
