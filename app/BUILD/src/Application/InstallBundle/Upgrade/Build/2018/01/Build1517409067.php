<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

class Build1517409067 extends AbstractBuild implements BlockingBuildInterface
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
