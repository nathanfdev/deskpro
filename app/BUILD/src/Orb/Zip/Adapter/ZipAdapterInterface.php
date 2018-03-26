<?php

/**
 * Orb.
 */

namespace Orb\Zip\Adapter;

interface ZipAdapterInterface
{
    /**
     * Compress a file or directory of files.
     *
     * @param string $path The file or directory to ZIP
     * @param string $to   Where to write the zip file to
     */
    public function compressPath($path, $to);

    /**
     * Decompress a ZIP.
     *
     * @param string $path The ZIP file to unzip
     * @param string $to   The path to unzip to
     */
    public function decompressZip($path, $to);
}
