<?php

/**
 * DeskPRO.
 */

namespace Application\InstallBundle\Upgrade\Build;

class BuildNewAgent_0003_oldtables extends AbstractBuild
{
    public function run()
    {
        $this->out('Drop old tables');
        $this->execMutateSql('DROP TABLE IF EXISTS pretickets_content');
        $this->execMutateSql('DROP TABLE IF EXISTS article_to_product');
        $this->execMutateSql('DROP TABLE IF EXISTS log_request_stats');

        $this->execMutateSql('DROP TABLE IF EXISTS `auditlog`');
    }
}

//[[build:1460678403]]
