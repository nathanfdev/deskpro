<?php

/**
 * Orb.
 */

namespace Orb\Zip\Adapter;

use Orb\Zip\ZipException;

class ZipArchiveAdapter implements ZipAdapterInterface
{
    /**
     * Compress a file or directory of files.
     *
     * @param string $path The file or directory to ZIP
     * @param string $to   Where to write the zip file to
     */
    public function compressPath($path, $to)
    {
        $pathInfo   = pathinfo($path);
        $parentPath = $pathInfo['dirname'];
        $dirName    = $pathInfo['basename'];

        $z = new \ZipArchive();
        $z->open($to, \ZipArchive::CREATE);
        $z->addEmptyDir($dirName);
        self::folderToZip($path, $z, strlen("$parentPath/"));
        $z->close();
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

        $zip = new \ZipArchive();

        if (($code = $zip->open($path)) !== true) {
            throw new ZipException(sprintf('[%s/%s] %s %s', $zip->status, $zip->statusSys, $code, @$zip->getStatusString() ?: 'Invalid or unitialized Zip object'), ZipException::ZIP_ERROR);
        }

        if ($zip->extractTo($to) !== true) {
            throw new ZipException(sprintf('[%s/%s] %s %s', $zip->status, $zip->statusSys, $code, @$zip->getStatusString() ?: 'Invalid or unitialized Zip object'), ZipException::ZIP_ERROR);
        }

        $zip->close();
    }

    private static function folderToZip($folder, &$zipFile, $exclusiveLength)
    {
        $handle = opendir($folder);
        while (false !== $f = readdir($handle)) {
            if ($f != '.' && $f != '..') {
                $filePath = "$folder/$f";
                // Remove prefix from file path before add to zip.
                $localPath = substr($filePath, $exclusiveLength);
                if (is_file($filePath)) {
                    $zipFile->addFile($filePath, $localPath);
                } elseif (is_dir($filePath)) {
                    // Add sub-directory.
                    $zipFile->addEmptyDir($localPath);
                    self::folderToZip($filePath, $zipFile, $exclusiveLength);
                }
            }
        }
        closedir($handle);
    }
}
