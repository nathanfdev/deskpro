<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1467380461 extends AbstractBuild
{
    public function run()
    {
        $this->out('Fix old theme asset blobs');

        // The initial release of new-portal was saving theme assets incorrectly
        // by directly inserting data into blobs_storage, we need to fix this now

        $db = $this->getDbConnection('default');

        // A filesize of 0 is the easiest way to tell
        $assets = $db->fetchAll('
            SELECT a.id, a.blob_id, a.name
            FROM theme_set_assets AS a
            LEFT JOIN blobs b ON (b.id = a.blob_id)
            WHERE b.filesize = 0
        ');

        foreach ($assets as $asset) {
            $this->out(sprintf('  .. fixing %d %s', $asset['id'], $asset['name']));
            $this->fixAsset($asset);
        }
    }

    /**
     * @param array $asset
     */
    private function fixAsset(array $asset)
    {
        $db = $this->getDbConnection('default');

        $data = $db->fetchColumn('SELECT data FROM blobs_storage WHERE blob_id = ?', [$asset['blob_id']]);
        if (empty($data)) {
            $data = '';
        }

        $newBlobId = $this->saveBlob($data, $asset['name']);
        $db->update(
            'theme_set_assets',
            ['blob_id' => $newBlobId],
            ['id'      => $asset['id']]
        );
    }
}
