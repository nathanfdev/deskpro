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

class Build1488470197 extends AbstractBuild
{
    public function run()
    {
        $this->out('Rename manuals to guides');
        $this->execDbQuery('default', 'RENAME TABLE manuals TO guides');
        $this->execDbQuery('default', 'RENAME TABLE manual_topics TO topics');
        $this->execDbQuery('default', 'RENAME TABLE manual_topic_comments TO topic_comments');
        $this->execDbQuery('default', 'RENAME TABLE manual_topic_revisions TO topic_revisions');
        $this->execDbQuery('default', 'RENAME TABLE manual_topic_slug_history TO topic_slug_history');
        $this->execDbQuery('default', 'RENAME TABLE manual2usergroup TO guide2usergroup');
        $this->execDbQuery('default', 'ALTER TABLE topics CHANGE manual_id guide_id INT');
        $this->execDbQuery('default', 'ALTER TABLE guide2usergroup CHANGE manual_id guide_id INT');
        $this->execDbQuery('default', 'ALTER TABLE topic_comments CHANGE manual_topic_id topic_id INT');
        $this->execDbQuery('default', 'ALTER TABLE topic_revisions CHANGE manual_topic_id topic_id INT');
        $this->execDbQuery('default', 'ALTER TABLE topic_slug_history CHANGE manual_topic_id topic_id INT');
    }
}
