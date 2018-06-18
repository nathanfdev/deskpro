<?php

/**
 * Orb.
 */

namespace Orb\Zip;

use Orb\Zip\Adapter\ZipAdapterInterface;

class Zip
{
    /**
     * @var ZipAdapterInterface
     */
    private $adapter;

    public function __construct(ZipAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Returns the fully qualified class name of its adapter
     *
     * @return string
     */
    public function getAdapterType()
    {
        return get_class($this->adapter);
    }

    /**
     * Compress a file or directory of files.
     *
     * @param string $path The file or directory to ZIP
     * @param string $to   Where to write the zip file to
     */
    public function compressPath($path, $to)
    {
        $this->adapter->compressPath($path, $to);
    }

    /**
     * Decompress a ZIP.
     *
     * @param string $path The ZIP file to unzip
     * @param string $to   The path to unzip to
     */
    public function decompressZip($path, $to)
    {
        $this->adapter->decompressZip($path, $to);
    }
}
