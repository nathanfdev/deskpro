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

/**
 * DeskPRO.
 */

namespace Application\InstallBundle\Upgrade\Build;

class Build1442938090 extends AbstractBuild
{
    public function run()
    {
        $this->out('Add usersources.user_permission_group_id (note: any warnings here are normal)');
        $this->execMutateSql('ALTER TABLE usersources ADD user_permission_group_id INT DEFAULT NULL', true);
        $this->execMutateSql('ALTER TABLE usersources ADD CONSTRAINT FK_4E3C994C21AF6383 FOREIGN KEY (user_permission_group_id) REFERENCES usergroups (id) ON DELETE SET NULL', true);
        $this->execMutateSql('CREATE INDEX IDX_4E3C994C21AF6383 ON usersources (user_permission_group_id)', true);
    }
}
