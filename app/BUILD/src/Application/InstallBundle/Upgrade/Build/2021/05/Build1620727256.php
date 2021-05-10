<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1620727256 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->execDbQuery('default', <<<'SQL'
INSERT INTO `ticket_statuses` (`status_type`, `sys_id`, `title`) VALUES
('awaiting_agent', 'awaiting_agent', 'awaiting_agent'),
('awaiting_user', 'awaiting_user', 'awaiting_user'),
('pending', 'pending', 'Pending'),
('resolved', 'resolved', 'Resolved')
SQL
        );
    }
}
