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

/**
 * Class AppZipArchiveBundle.
 */
class AppZipArchiveBundle implements Domain\AppBundle
{
    /**
     * @var \ZipArchive
     */
    private $archive;

    /**
     * @var \SplFileInfo
     */
    private $fileInfo;

    /**
     * Creates a ZipArchiveBuilder that will create the bundle at the specified location.
     *
     * @param string $file path to a file
     *
     * @return AppZipArchiveBundle
     */
    public static function fromFile($file)
    {
        $fileInfo = new \SplFileInfo($file);
        if (!$fileInfo->isReadable()) {
            $exMsg = sprintf('trying to read an application zip bundle from a non-readable location: %s', $file);
            throw new \RuntimeException($exMsg);
        }

        $archive = new \ZipArchive();

        return new self($archive, $fileInfo);
    }

    /**
     * Constructor.
     *
     * @param \ZipArchive  $archive
     * @param \SplFileInfo $fileInfo
     */
    public function __construct(\ZipArchive $archive, \SplFileInfo $fileInfo)
    {
        $this->archive  = $archive;
        $this->fileInfo = $fileInfo;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->fileInfo->getRealPath();
    }

    /**
     * @return string
     */
    public function getManifestAsString()
    {
        return $this->getResourceByPath(Domain\Constants::BUNDLE_MANIFEST_PATH);
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->getResourceByPath('assets/icon.png');
    }

    /**
     * @return Domain\AppBundleResource[];
     */
    public function listAllResources()
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
     *
     * @return array|Domain\AppBundleResource[]
     */
    private function collectResources(\Closure $acceptor)
    {
        $collectedResources = [];

        $archivePath = $this->fileInfo->getRealPath();
        try {
            $resource = $this->archive->open($archivePath, \ZipArchive::CREATE);
            if (true === $resource) {
                //online docs for ziparchive claim getNameIndex and getFromIndex leak memory in long running loops.
                //TODO investigate
                for ($i = 0; $i < $this->archive->numFiles; ++$i) {
                    if ($acceptor($i, $this->archive)) {
                        $path                 = $this->archive->getNameIndex($i);
                        $content              = $this->archive->getFromIndex($i);
                        $collectedResources[] = new Domain\AppBundleSimpleResource($path, $content);
                    }
                }
            }
        } finally {
            if (true === $resource) {
                $this->archive->close();
            }
        }

        return $collectedResources;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function getResourceByPath($path)
    {
        try {
            $resource = $this->archive->open($this->fileInfo->getRealPath(), \ZipArchive::CREATE);
            if (true === $resource) {
                $resource = $this->archive->getFromName($path);

                return $resource === false ? null : $resource;
            }
        } finally {
            if (true === $resource) {
                $this->archive->close();
            }
        }

        return;
    }
}
