<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
