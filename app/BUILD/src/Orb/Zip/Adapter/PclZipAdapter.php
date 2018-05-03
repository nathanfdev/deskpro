<?php

/**
 * Orb.
 */

namespace Orb\Zip\Adapter;

use Orb\Zip\ZipException;

class PclZipAdapter implements ZipAdapterInterface
{
    /**
     * Compress a file or directory of files.
     *
     * @param string $path The file or directory to ZIP
     * @param string $to   Where to write the zip file to
     */
    public function compressPath($path, $to)
    {
        $z = new \PclZip($to);
        $z->create($path, PCLZIP_OPT_REMOVE_PATH, $path);
    }

    /**
     * Decompress a ZIP.
     *
     * @param string $path The ZIP file to unzip
     * @param string $to   The path to unzip to
     */
    public function decompressZip($path, $to)
    {
        if (!is_file($path)) {
            throw new ZipException('Invalid $path', ZipException::NO_FILE);
        }

        if (!is_writable($to)) {
            throw new ZipException('$to is not writable', ZipException::WRITE_ERROR);
        }

        $zip = new \PclZip($path);

        if (!is_array($zip->extract(
                \PCLZIP_OPT_PATH, $to,
                \PCLZIP_OPT_ADD_TEMP_FILE_ON,
                \PCLZIP_OPT_STOP_ON_ERROR
            ))) {
            switch ($zip->errorName()) {
                case 'PCLZIP_ERR_BAD_FORMAT':
                case 'PCLZIP_ERR_INVALID_ZIP':
                case 'PCLZIP_ERR_INVALID_ARCHIVE_ZIP':
                case 'PCLZIP_ERR_UNSUPPORTED_COMPRESSION':
                case 'PCLZIP_ERR_UNSUPPORTED_ENCRYPTION':
                    $code = ZipException::BAD_FORMAT;
                    break;
                default:
                    $code = ZipException::ZIP_ERROR;
            }
            throw new ZipException($zip->errorInfo(true), $code);
        }
    }
}
