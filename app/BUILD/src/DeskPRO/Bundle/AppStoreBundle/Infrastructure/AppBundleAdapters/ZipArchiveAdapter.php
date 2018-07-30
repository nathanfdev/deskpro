<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppBundleAdapters;

use DeskPRO\Bundle\AppStoreBundle\Domain;

class ZipArchiveAdapter implements Domain\AppBundle
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
     * @param string $file|\SplFileInfo path to a file
     *
     * @return ZipArchiveAdapter
     */
    public static function fromFile($file)
    {
        $fileInfo = $file instanceof \SplFileInfo ? $file : new \SplFileInfo($file);

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
