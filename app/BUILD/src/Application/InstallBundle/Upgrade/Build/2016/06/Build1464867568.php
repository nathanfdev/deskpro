<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1464867568 extends AbstractBuild
{
    public function run()
    {
        $this->execDbQuery('system', 'SET FOREIGN_KEY_CHECKS = 0');
        foreach ([
            'system_storage_key_value',
            'system_alerts_events',
            'system_alerts_incidents',
            'system_alerts_incident_events',
        ] as $t) {
            $this->execDbQueryQuiet('system', 'DROP TABLE IF EXISTS '.$t);
        }
        $this->execDbQuery('system', 'SET FOREIGN_KEY_CHECKS = 1');

        $queries[] = 'CREATE TABLE system_alerts_events (id INT AUTO_INCREMENT NOT NULL, subject_unique_id VARCHAR(255) NOT NULL, date_created DATETIME NOT NULL, processed TINYINT(1) NOT NULL, type VARCHAR(30) NOT NULL, exception_class VARCHAR(255) DEFAULT NULL, exception_code INT UNSIGNED DEFAULT NULL, message VARCHAR(255) DEFAULT NULL, trace LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', file VARCHAR(255) DEFAULT NULL, line INT UNSIGNED DEFAULT NULL, email_account_id INT UNSIGNED DEFAULT NULL, email_account_address VARCHAR(255) DEFAULT NULL, error_type INT UNSIGNED DEFAULT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';
        $queries[] = 'CREATE TABLE system_alerts_incidents (id INT AUTO_INCREMENT NOT NULL, subject_unique_id VARCHAR(255) DEFAULT NULL, date_created DATETIME NOT NULL, raised TINYINT(1) NOT NULL, dismissed TINYINT(1) NOT NULL, type VARCHAR(30) NOT NULL, resolved TINYINT(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';
        $queries[] = 'CREATE TABLE system_alerts_incident_events (abstractincident_id INT NOT NULL, abstractevent_id INT NOT NULL, INDEX IDX_AA906E6DA11F9D9D (abstractincident_id), INDEX IDX_AA906E6DA7086F66 (abstractevent_id), PRIMARY KEY(abstractincident_id, abstractevent_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';
        $queries[] = 'ALTER TABLE system_alerts_incident_events ADD CONSTRAINT FK_AA906E6DA11F9D9D FOREIGN KEY (abstractincident_id) REFERENCES system_alerts_incidents (id) ON DELETE CASCADE, ADD CONSTRAINT FK_AA906E6DA7086F66 FOREIGN KEY (abstractevent_id) REFERENCES system_alerts_events (id) ON DELETE CASCADE';

        foreach ($queries as $q) {
            $this->execDbQuery('system', $q);
        }
    }
}
