<?php

namespace Application\InstallBundle\Upgrade\Build;

use Application\DeskPRO\BlobStorage\Blob;

class Build1565606461 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $blobStorage   = $this->container->getBlobStorage();
        $storageMethod = $this->container->getSetting('core.filestorage_method');

        if ($storageMethod === 's3' && $blobStorage->hasAdapter($storageMethod)) {
            $blob = new Blob('robots.txt', 'text/plain');
            $blob->setPath('robots.txt');

            try {
                $blobStorage
                    ->getAdapter($storageMethod)
                    ->writeBlobString($blob, "User-agent: *\r\nDisallow: /");
            } catch (\Exception $e) {
            }
        }
    }
}
