<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppBundleAdapters;

use DeskPRO\Bundle\AppStoreBundle\Domain;

class ZipArchiveBundleWriter implements ZipBundleWriter
{
    /**
     * Creates a ZipArchiveBuilder that will create the bundle in a randomly named file from the system temp dir.
     *
     * @param null $dir
     *
     * @return ZipArchiveBundleWriter
     */
    public static function fromTmp($dir = null)
    {
        $root = is_null($dir) ? sys_get_temp_dir() : $dir;
        $file = tempnam($root, 'foo');

        return self::fromFile($file);
    }

    /**
     * Creates a ZipArchiveBuilder that will create the bundle at the specified location.
     *
     * @param string $file path to a file
     *
     * @return ZipArchiveBundleWriter
     */
    public static function fromFile($file)
    {
        $fileInfo = new \SplFileInfo($file);
        if (!$fileInfo->isWritable()) {
            $exMsg = sprintf('trying to write an application zip bundle file to a non-writable location: %s', $file);
            throw new \RuntimeException($exMsg);
        }

        return new self($fileInfo);
    }

    /**
     * @var \ZipArchive
     */
    private $archive;

    /** @var string open or close */
    private $archiveFileState;

    /** @var string */
    private $archivePath;

    public function __construct(\SplFileInfo $fileInfo)
    {
        $path          = $fileInfo->getRealPath();
        $this->archive = new \ZipArchive();
        $this->archive->open($path, \ZipArchive::CREATE);

        $this->archiveFileState = 'open';

        $this->archivePath = $path;
    }

    /**
     * @param string $dir
     * @param int    $maxDepth
     *
     * @return ZipArchiveBundleWriter
     */
    public function addFolder($dir, $maxDepth = -1)
    {
        if ($this->archiveFileState === 'close') {
            throw new \RuntimeException('archive is closed');
        }

        if (!is_integer($maxDepth) || $maxDepth === 0) {
            throw new \BadMethodCallException('maxLevels must be a positive integer');
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::LEAVES_ONLY);

        if ($maxDepth > 0) {
            $iterator->setMaxDepth($maxDepth);
        }
        foreach ($iterator as $name => $file) {
            if (!$file->isDir()) { //only add files
                $filePath = $file->getRealPath();

                $localName = substr($filePath, strlen($dir));
                $localName = ltrim($localName, '/');

                $this->archive->addFile($filePath, $localName);
                $filePathList[] = $filePath;
            }
        }

        return $this;
    }

    /**
     * @param string|\SplFileInfo $file
     * @param null                $localName
     *
     * @return ZipArchiveBundleWriter
     */
    public function addFile($file, $localName = null)
    {
        if ($this->archiveFileState === 'close') {
            throw new \RuntimeException('archive is closed');
        }

        $fileInfo = $file instanceof \SplFileInfo ? $file : new \SplFileInfo($file);
        if ($fileInfo->isFile()) {
            if (!empty($localName)) {
                $this->archive->addFile($file, $localName);
            } else {
                $this->archive->addFile($file);
            }

            return $this;
        }

        $exMsg = sprintf('File %s is missing', $file);
        throw new \RuntimeException($exMsg);
    }

    /**
     * @param string $manifest
     *
     * @return ZipArchiveBundleWriter
     */
    public function setManifest($manifest)
    {
        if ($this->archiveFileState === 'close') {
            throw new \RuntimeException('archive is closed');
        }

        $this->archive->addFromString(Domain\Constants::BUNDLE_MANIFEST_PATH, $manifest);

        return $this;
    }

    /**
     * @return ZipArchiveAdapter
     */
    public function build()
    {
        if ($this->archiveFileState === 'open') {
            $this->archive->close();
            $this->archiveFileState = 'close';
        }

        $archive = new \ZipArchive();
        return new ZipArchiveAdapter($archive, new \SplFileInfo($this->archivePath));
    }
}
