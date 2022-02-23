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
        // Mark all temp blobs as DB blobs.
        // DB blobs are the only type we can conditionally refuse to serve based on how
        // long they've been temp. This is a workaround for older buggy code that didn't clean
        // temp blobs properly.
        $this->logger->info('Marking temp blobs for DB sotrage');


        // Taken from MoveBlobsCommand
        $set_aid = 'db';
        $db = $this->getDbConnection('default');
        $minId      = $db->fetchColumn('SELECT id FROM blobs ORDER BY id ASC LIMIT 1');
        $maxId      = $db->fetchColumn('SELECT id FROM blobs ORDER BY id DESC LIMIT 1');
        $batchStart = $minId;
        $batchSize  = 250000;

        $c = 0;
        while ($batchStart < $maxId) {
            $batchEnd = ($batchStart + $batchSize) - 1;
            $this->logger->info("Batch $batchStart -> $batchEnd");
            $c += $db->executeUpdate('
                UPDATE blobs
                SET storage_loc_pref = ?
                WHERE
                    id BETWEEN ? AND ?
                    AND storage_loc != ?
                    AND storage_loc != \'\'
                    AND storage_loc IS NOT NULL
                    AND storage_loc_specific IS NULL
            ', [$set_aid, $batchStart, $batchEnd, $set_aid]);
            $batchStart = $batchEnd;
        }
    }
}
