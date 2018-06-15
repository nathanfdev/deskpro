<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppBundleAdapters;

use DeskPRO\Bundle\AppStoreBundle\Domain;

class PclzipBundleWriter
{
    /**
     * Creates a ZipArchiveBuilder that will create the bundle in a randomly named file from the system temp dir.
     *
     * @param null $dir
     *
     * @return PclzipBundleWriter
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
     * @return PclzipBundleWriter
     */
    public static function fromFile($file)
    {
        $fileInfo = new \SplFileInfo($file);
        if (!$fileInfo->isWritable()) {
            $exMsg = sprintf('trying to write an application zip bundle file to a non-writable location: %s', $file);
            throw new \RuntimeException($exMsg);
        }

        $archive = new \PclZip($file);
        return new PclzipBundleWriter($archive, $fileInfo);
    }

    /**
     * @var \PclZip
     */
    private $archive;

    /** @var string open or close */
    private $archiveFileState;

    /** @var string */
    private $archivePath;

    public function __construct(\PclZip $archive, \SplFileInfo $fileInfo)
    {
        $this->archive = $archive;
        $this->archivePath = $fileInfo->getRealPath();

        $this->archiveFileState = 'open';
    }

    /**
     * @param string $dir
     * @param int    $maxDepth
     *
     * @return PclzipBundleWriter
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

                $this->archive->add($filePath, "", $dir);
                $filePathList[] = $filePath;
            }
        }

        return $this;
    }

    /**
     * @param string|\SplFileInfo $file
     * @param null                $localName
     *
     * @return PclzipBundleWriter
     */
    public function addFile($file, $localName = null)
    {
        if ($this->archiveFileState === 'close') {
            throw new \RuntimeException('archive is closed');
        }

        $fileInfo = $file instanceof \SplFileInfo ? $file : new \SplFileInfo($file);
        if ($fileInfo->isFile()) {
            if (empty($localName)) {
                $this->archive->add($file);
            } else {
                $this->archive->add($file, "", $localName);

            }

            return $this;
        }

        $exMsg = sprintf('File %s is missing', $file);
        throw new \RuntimeException($exMsg);
    }

    /**
     * @param string $manifest
     *
     * @return PclzipBundleWriter
     */
    public function setManifest($manifest)
    {
        if ($this->archiveFileState === 'close') {
            throw new \RuntimeException('archive is closed');
        }
        $stringHandler = new PclzipStringHandler();
        $stringHandler->write($this->archive, $manifest, Domain\Constants::BUNDLE_MANIFEST_PATH);
        return $this;
    }

    /**
     * @return PclzipAdapter
     */
    public function build()
    {
        if ($this->archiveFileState === 'open') {
            $this->archiveFileState = 'close';
            return new PclzipAdapter($this->archive, new \SplFileInfo($this->archivePath));
        }

        throw new \RuntimeException("writer was closed");
    }
}
