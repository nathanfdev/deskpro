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

class Build1430211196 extends AbstractBuild
{
    public function run()
    {
        $this->out('Upgrade Round Robin Log');
        $this->execMutateSql('DROP TABLE IF EXISTS log_round_robin');
        $this->execMutateSql("CREATE TABLE round_robin_log (id INT AUTO_INCREMENT NOT NULL, rr_id INT DEFAULT NULL, ticket_id INT NOT NULL, ticket_subject VARCHAR(255) NOT NULL, actions LONGBLOB NOT NULL COMMENT '(DC2Type:array)', created DATETIME NOT NULL, INDEX IDX_4CB426EE1D063087 (rr_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 ENGINE = InnoDB DEFAULT CHARSET=utf8");
        $this->execMutateSql('ALTER TABLE round_robin_log ADD CONSTRAINT FK_4CB426EE1D063087 FOREIGN KEY (rr_id) REFERENCES round_robin (id)');
    }
}
