<?php

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
