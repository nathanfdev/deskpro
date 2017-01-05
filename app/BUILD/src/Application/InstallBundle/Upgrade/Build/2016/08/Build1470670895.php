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

class Build1470670895 extends AbstractBuild
{
    public function run()
    {
        $this->out('Remove logo from brands table as it set in assets');
        $connection = $this->getDbConnection('default');
        $sql        = 'SELECT `logo_blob_id` FROM `brands` WHERE `logo_blob_id` IS NOT NULL';
        $ids        = $connection->fetchAllCol($sql);
        $connection->deleteIn('blobs_storage', $ids, 'blob_id');
        $connection->deleteIn('blobs', $ids);

        $this->execDbQuery('default', 'ALTER TABLE brands DROP FOREIGN KEY FK_7EA24434D91464D5');
        $this->execDbQuery('default', 'DROP INDEX UNIQ_7EA24434D91464D5 ON brands');
        $this->execDbQuery('default', 'ALTER TABLE brands DROP logo_blob_id');
    }
}
