<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppBundleAdapters;

use DeskPRO\Component\Filesystem\SafeFile;
use DeskPRO\Component\Filesystem\TmpDir;

class PclzipStringHandler
{
    public function read( \PclZip $from, array $fileEntry)
    {
        if ($fileEntry['folder']) {
            throw new \DomainException('entry is a folder not a file');
        }

        $tmpPath = $this->createTempDir();
        $filename = $tmpPath . DIRECTORY_SEPARATOR . $fileEntry['stored_filename'];
        $from->extractByIndex($fileEntry['index'], $tmpPath);


        if (SafeFile::is_dir($filename, $tmpPath)) {
            throw new \DomainException('extracted entry is a folder not a file');
        }

        $contents = SafeFile::file_get_contents($filename, $tmpPath);
        return $contents;
    }

    /**
     * @param \PclZip $into
     * @param string $content
     * @param string $path
     */
    public function write(\PclZip $into, $content, $path)
    {
        $tempDir = $this->createTempDir();
        $actualPath = $tempDir . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);

        $contentDir = pathinfo($actualPath, PATHINFO_DIRNAME);
        if ($contentDir !== $tempDir) {
            SafeFile::mkdir($content, $tempDir, 0777, true);
        }

        SafeFile::file_put_contents($actualPath, $content, $tempDir);
        $into->add($actualPath, "", $tempDir);
    }

    /**
     * @return null|string
     */
    private function createTempDir()
    {
        return TmpDir::makeTmpDir();
    }
}
