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

/**
 * DeskPRO.
 */
namespace Application\InstallBundle\Upgrade\Build;

class BuildNewAgent_0270 extends AbstractBuild
{
    public function run()
    {
        $this->out('Added assigned_person_id to the article_pending_create table');
        $this->execMutateSql('ALTER TABLE article_pending_create ADD assigned_person_id INT DEFAULT NULL');
        $this->execMutateSql('ALTER TABLE article_pending_create ADD CONSTRAINT FK_27A971C358DA0EE5 FOREIGN KEY (assigned_person_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execMutateSql('CREATE INDEX IDX_27A971C358DA0EE5 ON article_pending_create (assigned_person_id)');
    }
}

//[[build:1456790427]]

