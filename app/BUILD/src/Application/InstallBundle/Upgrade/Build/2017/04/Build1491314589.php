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

class Build1491314589 extends AbstractBuild
{
    public function run()
    {
        $this->out('Create Topic subscriptions table');
        $this->execDbQuery('default', 'CREATE TABLE topic_subscriptions (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, topic_id INT DEFAULT NULL, root_category TINYINT(1) DEFAULT NULL, INDEX IDX_F34BD8DB217BBB47 (person_id), INDEX IDX_F34BD8DB1F55203D (topic_id), INDEX root_category_idx (root_category), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execDbQuery('default', 'ALTER TABLE topic_subscriptions ADD CONSTRAINT FK_F34BD8DB217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE topic_subscriptions ADD CONSTRAINT FK_F34BD8DB1F55203D FOREIGN KEY (topic_id) REFERENCES topics (id) ON DELETE CASCADE');
    }
}
