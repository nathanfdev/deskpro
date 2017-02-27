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

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure;

use DeskPRO\Bundle\AppStoreBundle\Domain;

class AppZipArchiveBundle implements Domain\AppBundle
{
    /** @var \ZipArchive  */
    private $archive;

    /** @var \SplFileInfo  */
    private $fileInfo;

    public function __construct(\ZipArchive $archive, \SplFileInfo $fileInfo)
    {
        $this->archive = $archive;
        $this->fileInfo = $fileInfo;
    }

    /**
     * @return string
     */
    function getManifestAsString()
    {
        $path = $this->fileInfo->getRealPath();
        try {
            $resource = $this->archive->open($path, \ZipArchive::CREATE);
            if (true === $resource) {
                $manifest = $this->archive->getFromName(Domain\Constants::BUNDLE_MANIFEST_PATH);
                if (!is_null($manifest) && is_string($manifest)) {
                    return $manifest;
                }
            }
        } finally {
            $this->archive->close();
        }

        return null;
    }

    /**
     * @return Domain\AppBundleResource[];
     */
    function listAllResources()
    {
        // skip folders, accept only file entries
        $acceptor = function ($index, \ZipArchive $archive) {
            $path = $archive->getNameIndex($index);
            return '/' != substr($path, -1);
        };

        return $this->collectResources($acceptor);
    }

    /**
     * @param \Closure $acceptor
     * @return array|Domain\AppBundleResource[]
     */
    private function collectResources(\Closure $acceptor) {
        $collectedResources = [];

        $archivePath = $this->fileInfo->getRealPath();
        try {
            $resource = $this->archive->open($archivePath, \ZipArchive::CREATE);
            if (true === $resource) {
                //online docs for ziparchive claim getNameIndex and getFromIndex leak memory in long running loops.
                //TODO investigate
                for($i = 0; $i < $this->archive->numFiles; $i++) {

                    if ($acceptor($i, $this->archive)) {
                        $path = $this->archive->getNameIndex($i);
                        $content = $this->archive->getFromIndex($i);
                        $collectedResources[] = new Domain\AppBundleSimpleResource($path, $content);
                    }
                }
            }
        } finally {
            $this->archive->close();
        }

        return $collectedResources;
    }

}
