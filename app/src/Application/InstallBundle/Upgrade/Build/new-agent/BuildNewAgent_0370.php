<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

class BuildNewAgent_0370 extends AbstractBuild
{
    public function run()
    {
        $this->out('add more date fields to content entities');
        $this->execMutateSql('ALTER TABLE downloads ADD date_last_comment DATETIME DEFAULT NULL, CHANGE date_updated date_updated DATETIME DEFAULT NULL');
        $this->execMutateSql('CREATE INDEX date_updated_idx ON downloads (date_updated)');
        $this->execMutateSql('CREATE INDEX date_last_comment_idx ON downloads (date_last_comment)');
        $this->execMutateSql('CREATE INDEX date_updated_idx ON feedback (date_updated)');
        $this->execMutateSql('CREATE INDEX date_last_comment_idx ON feedback (date_last_comment)');
        $this->execMutateSql('ALTER TABLE news ADD date_updated DATETIME DEFAULT NULL, ADD date_last_comment DATETIME DEFAULT NULL');
        $this->execMutateSql('CREATE INDEX date_updated_idx ON news (date_updated)');
        $this->execMutateSql('CREATE INDEX date_last_comment_idx ON news (date_last_comment)');
    }
}

//[[build:1456790437]]

