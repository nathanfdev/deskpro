<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1543332348 extends AbstractBuild implements BlockingBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE ticket_statuses (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, status_type VARCHAR(255) NOT NULL, sys_id VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, display_order INT DEFAULT NULL, INDEX IDX_CE99E290727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE ticket_statuses ADD CONSTRAINT FK_CE99E290727ACA70 FOREIGN KEY (parent_id) REFERENCES ticket_statuses (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX UNIQ_CE99E290283C4F22 ON ticket_statuses (sys_id)');
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->execDbQuery('default', <<<'SQL'
INSERT INTO `ticket_statuses` (`status_type`, `sys_id`, `title`) VALUES
('hidden', 'deleted', 'Deleted'),
('hidden', 'spam', 'Spam')
SQL
        );
    }
}
