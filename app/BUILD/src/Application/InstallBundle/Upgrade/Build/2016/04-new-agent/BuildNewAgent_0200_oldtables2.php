<?php

/**
 * DeskPRO.
 */

namespace Application\InstallBundle\Upgrade\Build;

class BuildNewAgent_0200_oldtables2 extends AbstractBuild
{
    public function run()
    {
        $this->out('Drop old tables (part 2)');
        $this->execMutateSql('DROP TABLE IF EXISTS styles');

        // Turn off so this is a fast op
        $this->execMutateSql('SET FOREIGN_KEY_CHECKS = 0');
        $this->execMutateSql('DROP TABLE IF EXISTS visitors');
        $this->execMutateSql('DROP TABLE IF EXISTS visitor_tracks');
        $this->execMutateSql('SET FOREIGN_KEY_CHECKS = 1');

        $this->execMutateSql('DROP TABLE IF EXISTS people_emails_validating');
    }
}

//[[build:1460678424]]
