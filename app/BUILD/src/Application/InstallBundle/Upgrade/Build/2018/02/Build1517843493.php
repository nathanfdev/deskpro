<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1517843493 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE report_dashboard ADD person_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE report_dashboard ADD CONSTRAINT FK_722092E0217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE INDEX IDX_722092E0217BBB47 ON report_dashboard (person_id)');

        $this->execDbQuery('default', 'ALTER TABLE report_dashboard_permission ADD team_id INT DEFAULT NULL, ADD department_id INT DEFAULT NULL, CHANGE person_id person_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE report_dashboard_permission ADD CONSTRAINT FK_DED8DEF296CD8AE FOREIGN KEY(team_id) REFERENCES agent_teams (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE report_dashboard_permission ADD CONSTRAINT FK_DED8DEFAE80F5DF FOREIGN KEY (department_id) REFERENCES departments (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'CREATE INDEX IDX_DED8DEF296CD8AE ON report_dashboard_permission (team_id)');
        $this->execDbQuery('default', 'CREATE INDEX IDX_DED8DEFAE80F5DF ON report_dashboard_permission (department_id)');
    }

    public function run()
    {
    }
}
