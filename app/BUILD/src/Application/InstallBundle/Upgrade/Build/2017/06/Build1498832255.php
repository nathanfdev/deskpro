<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1498832255 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE tasks ADD assigned_department_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE tasks ADD CONSTRAINT FK_5058659714B25C9A FOREIGN KEY (assigned_department_id) REFERENCES departments (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE INDEX IDX_5058659714B25C9A ON tasks (assigned_department_id)');
        $this->execDbQuery('default', 'ALTER TABLE ticket_macros ADD department_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE ticket_macros ADD CONSTRAINT FK_8E373A2CAE80F5DF FOREIGN KEY (department_id) REFERENCES departments (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE INDEX IDX_8E373A2CAE80F5DF ON ticket_macros (department_id)');
    }

    public function run()
    {
    }
}
