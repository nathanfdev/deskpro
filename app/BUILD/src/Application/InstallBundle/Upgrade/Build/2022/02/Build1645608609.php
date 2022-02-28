<?php
namespace Application\InstallBundle\Upgrade\Build;

class Build1645608609 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $db = $this->getDbConnection('default');

        // Mark all temp blobs as DB blobs.
        // DB blobs are the only type we can conditionally refuse to serve based on how
        // long they've been temp. This is a workaround for older buggy code that didn't clean
        // temp blobs properly.
        $this->logger->info('Marking temp blobs for DB sotrage');

        $bytes = $db->fetchColumn("
            SELECT SUM(filesize) as f
            FROM blobs
            WHERE storage_loc != 'db' AND is_temp = 1
        ");
        if (!$bytes) {
            $bytes = 0;
        }

        $this->logger->info("$bytes bytes of blobs not in db");

        // if it'd be too much data to move to the db, skip it
        // so the user can do it manually if they are affected by the issue
        if ($bytes > 1073741824) {
            $this->logger->info("Too many blobs to move automatically");
            $this->logger->warning("
Temp blobs are still visible via permalink. You can move these blobs to the database
to prevent them being visible for long periods of time. To do so, execute this query:

UPDATE blobs
SET storage_loc_pref = 'db'
WHERE storage_loc != 'db' AND is_temp = 1;

Note that this will increase your database size by $bytes bytes.
            ");
            return;
        }

        $db->executeUpdate("
            UPDATE blobs
            SET storage_loc_pref = 'db'
            WHERE storage_loc != 'db' AND is_temp = 1
        ");
    }
}
