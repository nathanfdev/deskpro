<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
