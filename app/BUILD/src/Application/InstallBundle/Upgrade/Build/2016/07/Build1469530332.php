<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

class Build1469530332 extends AbstractBuild
{
    public function run()
    {
        $this->out('Recreate system alerts tables');
        $this->execDbQuery('system', 'DROP TABLE IF EXISTS `system_alerts_incident_events`');
        $this->execDbQuery('system', 'DROP TABLE IF EXISTS `system_alerts_events`');
        $this->execDbQuery('system', 'DROP TABLE IF EXISTS `system_alerts_incidents`');
        $this->execDbQuery('system', "CREATE TABLE system_alerts_events (id INT AUTO_INCREMENT NOT NULL, subject_unique_id VARCHAR(255) NOT NULL, date_created DATETIME NOT NULL, processed TINYINT(1) NOT NULL, expiration_strategy VARCHAR(255) NOT NULL, type VARCHAR(30) NOT NULL, exception_class VARCHAR(255) DEFAULT NULL, exception_code INT UNSIGNED DEFAULT NULL, message VARCHAR(255) DEFAULT NULL, trace LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', file VARCHAR(255) DEFAULT NULL, line INT UNSIGNED DEFAULT NULL, email_account_id INT UNSIGNED DEFAULT NULL, email_account_address VARCHAR(255) DEFAULT NULL, error_type INT UNSIGNED DEFAULT NULL, data LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        $this->execDbQuery('system', 'CREATE TABLE system_alerts_incidents (id INT AUTO_INCREMENT NOT NULL, subject_unique_id VARCHAR(255) DEFAULT NULL, date_created DATETIME NOT NULL, raised TINYINT(1) NOT NULL, dismissed TINYINT(1) NOT NULL, type VARCHAR(30) NOT NULL, resolved TINYINT(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execDbQuery('system', 'CREATE TABLE system_alerts_incident_events (incident_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_AA906E6D59E53FB9 (incident_id), INDEX IDX_AA906E6D71F7E88B (event_id), PRIMARY KEY(incident_id, event_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execDbQuery('system', 'ALTER TABLE system_alerts_incident_events ADD CONSTRAINT FK_AA906E6D59E53FB9 FOREIGN KEY (incident_id) REFERENCES system_alerts_incidents (id) ON DELETE CASCADE');
        $this->execDbQuery('system', 'ALTER TABLE system_alerts_incident_events ADD CONSTRAINT FK_AA906E6D71F7E88B FOREIGN KEY (event_id) REFERENCES system_alerts_events (id) ON DELETE CASCADE');
    }
}
