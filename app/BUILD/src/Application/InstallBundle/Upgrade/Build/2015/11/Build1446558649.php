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

class Build1446558649 extends AbstractBuild
{
    public function run()
    {
        $this->out('Correct round_robin_log FK');

        $sh = $this->getSchemaHelper();

        $fk = $this->getSchemaHelper()->findForeignKey('round_robin_log', 'rr_id', 'round_robin', 'id');
        if ($fk) {
            $sh->getSchemaManager()->dropForeignKey($fk, 'round_robin_log');
        }

        $this->execMutateSql('ALTER TABLE round_robin_log ADD CONSTRAINT FK_4CB426EE1D063087 FOREIGN KEY (rr_id) REFERENCES round_robin (id) ON DELETE CASCADE', true);
    }
}
