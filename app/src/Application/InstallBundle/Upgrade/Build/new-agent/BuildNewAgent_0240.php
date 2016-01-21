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

class BuildNewAgent_0240 extends AbstractBuild
{
    public function run()
    {
        $this->out('test entity');
        $this->execMutateSql('ALTER TABLE api_sandbox_widgets ADD parent_id INT DEFAULT NULL');
        $this->execMutateSql('ALTER TABLE api_sandbox_widgets ADD CONSTRAINT FK_CD41AFC1727ACA70 FOREIGN KEY (parent_id) REFERENCES api_sandbox_widgets (id)');
        $this->execMutateSql('CREATE INDEX IDX_CD41AFC1727ACA70 ON api_sandbox_widgets (parent_id)');
        $this->execMutateSql('ALTER TABLE custom_ticket_filters CHANGE term term LONGTEXT NOT NULL');
    }
}

//[[build:1456790424]]

