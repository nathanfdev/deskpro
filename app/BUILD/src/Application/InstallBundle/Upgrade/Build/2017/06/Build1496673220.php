<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1496673220 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', '
SET foreign_key_checks = 0;
DROP TABLE IF EXISTS 
  tasks_new, 
  tasks_assignments, 
  task_attachments, 
  task_comments_new, 
  task_labels,
  task_links,
  task_lists,
  task_log,
  task_projects, 
  task_starred, 
  task_subtask;
SET foreign_key_checks = 1;
');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
