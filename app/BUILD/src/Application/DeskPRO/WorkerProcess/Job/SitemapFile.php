<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;

/**
 * Updates the sitemap file.
 */
class SitemapFile extends AbstractJob
{
    const DEFAULT_INTERVAL = 604800; // 7 days

    public function run()
    {
        $old_sitemap_file = App::getSetting('core.sitemap_blob_id');

        if ($old_sitemap_file) {
            try {
                $blob = App::getOrm()->find('DeskPRO:Blob', $old_sitemap_file);
                if ($blob) {
                    App::getContainer()->getBlobStorage()->deleteBlobRecord($blob);
                }
            } catch (\Exception $e) {
            }
        }

        $gen = new \Application\DeskPRO\Portal\SitemapGenerator(
            App::getOrm(),
            App::getRouter());
        $file = $gen->getXml();

        $blob_info = App::getContainer()->getBlobStorage()->createBlobRowFromString(
            $file,
            'sitemap.xml',
            'text/xml',
            ['sys_name' => 'sitemap_xml']
        );

        App::getContainer()->getSettingsHandler()->setSetting('core.sitemap_blob_id', $blob_info['id']);
    }
}
