<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppBundleAdapters;

use DeskPRO\Bundle\AppStoreBundle\Domain;

class PclzipAdapter implements Domain\AppBundle
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
     * @return PclzipAdapter
     */
    public static function fromFile($file)
    {
        $fileInfo = $file instanceof \SplFileInfo ? $file : new \SplFileInfo($file);
        if (!$fileInfo->isReadable()) {
            $exMsg = sprintf('trying to read an application zip bundle from a non-readable location: %s', $file);
            throw new \RuntimeException($exMsg);
        }

        $archive = new \PclZip($fileInfo->getRealPath());

        return new self($archive, $fileInfo);
    }

    /**
     * Constructor.
     *
     * @param \PclZip  $archive
     * @param \SplFileInfo $fileInfo
     */
    public function __construct(\PclZip $archive, \SplFileInfo $fileInfo)
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
        $resources = $this->collectResources();
        $list = [];
        /** @var array $entry */
        foreach ($resources as $entry) {
            $list[] = new PclzipBundleResource($this->archive, $entry);
        }

        return $list;
    }

    /**
     * @param \Closure $acceptor
     *
     * @return array|Domain\AppBundleResource[]
     */
    private function collectResources(\Closure $acceptor = null)
    {
        $filters = [
            function (array $entry) {
                return !$entry['folder'];
            }
        ];

        if ($acceptor) {
            $filters[] = $acceptor;
        }

        $filterChain = function (array $entry) use ($filters) {
            foreach ($filters as $filter) {
                if (! $filter($entry)) {
                    return false;
                }
            }
            return true;
        };

        // skip folders, accept only file entries
        return array_filter($this->archive->listContent(), $filterChain);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function getResourceByPath($path)
    {
        $acceptor = function (array $entry) use ($path) {
            return $entry['stored_filename'] === $path;
        };

        $entries = $this->collectResources($acceptor);
        if (empty($entries)) {
            return null;
        }

        $entry = array_pop($entries);
        if (! empty($entries)) {
            throw new \RuntimeException(sprintf('ambiguous path: %s', $path));
        }

        $extractor = new PclzipStringHandler();
        return $extractor->read($this->archive, $entry);
    }
}
