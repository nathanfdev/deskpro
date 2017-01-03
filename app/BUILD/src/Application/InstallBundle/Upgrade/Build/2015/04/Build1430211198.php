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

class Build1430211198 extends AbstractBuild
{
    public function run()
    {
        $sh = $this->getSchemaHelper();

        $this->out('Upgrade Agent-Team relations');
        $this->execMutateSql('SET FOREIGN_KEY_CHECKS = 0');

        $fk = $sh->findForeignKey('agent_team_members', 'person_id', 'people', 'id');
        $sh->getSchemaManager()->dropForeignKey($fk, 'agent_team_members');

        $fk = $sh->findForeignKey('agent_team_members', 'team_id', 'agent_teams', 'id');
        $sh->getSchemaManager()->dropForeignKey($fk, 'agent_team_members');

        $this->execMutateSql('ALTER TABLE agent_team_members ADD CONSTRAINT FK_CC952C03217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execMutateSql('ALTER TABLE agent_team_members ADD CONSTRAINT FK_CC952C03296CD8AE FOREIGN KEY (team_id) REFERENCES agent_teams (id) ON DELETE CASCADE');

        $this->execMutateSql('SET FOREIGN_KEY_CHECKS = 1');
    }
}
