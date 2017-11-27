<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

class Build1510932693 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->out('Drop old reports2 tables');
        $this->execDbQuery('default', 'SET FOREIGN_KEY_CHECKS = 0');
        $this->execDbQuery('default', 'DROP TABLE IF EXISTS report_widget');
        $this->execDbQuery('default', 'DROP TABLE IF EXISTS report_widget_favorite');
        $this->execDbQuery('default', 'DROP TABLE IF EXISTS report_dashboard');
        $this->execDbQuery('default', 'DROP TABLE IF EXISTS report_dashboard_report');
        $this->execDbQuery('default', 'DROP TABLE IF EXISTS report_dashboard_widget');
        $this->execDbQuery('default', 'DROP TABLE IF EXISTS report_dashboard_permission');
        $this->execDbQuery('default', 'SET FOREIGN_KEY_CHECKS = 1');

        $this->out('Recreate reports2 tables');

        $queries = ['create' => [], 'alter' => []];

        $queries['create'][] = 'CREATE TABLE report_widget (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, unique_key VARCHAR(50) DEFAULT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, query LONGTEXT NOT NULL, is_custom TINYINT(1) NOT NULL, labels TINYTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', display_order INT NOT NULL, display_types LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', variables LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', INDEX parent_id_idx (parent_id), UNIQUE INDEX unique_key_idx (unique_key), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';
        $queries['alter'][]  = 'ALTER TABLE report_widget ADD CONSTRAINT FK_AC0ACB4F727ACA70 FOREIGN KEY (parent_id) REFERENCES report_widget (id) ON DELETE SET NULL';

        $queries['create'][] = 'CREATE TABLE report_widget_favorite (id INT AUTO_INCREMENT NOT NULL, report_widget_id INT DEFAULT NULL, person_id INT DEFAULT NULL, params VARCHAR(100) NOT NULL, INDEX IDX_AA4CD2D5D55CE8FA (report_widget_id), INDEX IDX_AA4CD2D5217BBB47 (person_id), UNIQUE INDEX unique_key_idx (report_widget_id, person_id, params), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';
        $queries['alter'][]  = 'ALTER TABLE report_widget_favorite ADD CONSTRAINT FK_AA4CD2D5D55CE8FA FOREIGN KEY (report_widget_id) REFERENCES report_widget (id) ON DELETE CASCADE, ADD CONSTRAINT FK_AA4CD2D5217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE';

        $queries['create'][] = 'CREATE TABLE report_dashboard (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, is_default TINYINT(1) DEFAULT \'0\' NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';

        $queries['create'][] = 'CREATE TABLE report_dashboard_report (id INT AUTO_INCREMENT NOT NULL, dashboard_id INT NOT NULL, title VARCHAR(255) NOT NULL, sort_order INT DEFAULT 0 NOT NULL, columns INT DEFAULT 24 NOT NULL, INDEX IDX_6EE5C64BB9D04D2B (dashboard_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';
        $queries['alter'][]  = 'ALTER TABLE report_dashboard_report ADD CONSTRAINT FK_6EE5C64BB9D04D2B FOREIGN KEY (dashboard_id) REFERENCES report_dashboard (id) ON DELETE CASCADE';

        $queries['create'][] = 'CREATE TABLE report_dashboard_widget (id INT AUTO_INCREMENT NOT NULL, widget_id INT DEFAULT NULL, report_id INT NOT NULL, title VARCHAR(255) NOT NULL, position VARCHAR(5) NOT NULL, size VARCHAR(5) NOT NULL, hc_data VARCHAR(50) DEFAULT NULL, type VARCHAR(50) DEFAULT NULL, variables TINYTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', INDEX report_id_idx (report_id), INDEX widget_id_idx (widget_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';
        $queries['alter'][]  = 'ALTER TABLE report_dashboard_widget ADD CONSTRAINT FK_2F33AF1FFBE885E2 FOREIGN KEY (widget_id) REFERENCES report_widget (id) ON DELETE CASCADE, ADD CONSTRAINT FK_2F33AF1F4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report_dashboard_report (id) ON DELETE CASCADE';

        $queries['create'][] = 'CREATE TABLE report_dashboard_permission (id INT AUTO_INCREMENT NOT NULL, dashboard_id INT NOT NULL, person_id INT NOT NULL, name VARCHAR(50) NOT NULL, INDEX IDX_DED8DEFB9D04D2B (dashboard_id), INDEX IDX_DED8DEF217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';
        $queries['alter'][]  = 'ALTER TABLE report_dashboard_permission ADD CONSTRAINT FK_DED8DEFB9D04D2B FOREIGN KEY (dashboard_id) REFERENCES report_dashboard (id) ON DELETE CASCADE, ADD CONSTRAINT FK_DED8DEF217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE';

        foreach ($queries['create'] as $sql) {
            $this->execDbQuery('default', $sql);
        }
        foreach ($queries['alter'] as $sql) {
            $this->execDbQuery('default', $sql);
        }
    }

    public function run()
    {
    }
}
