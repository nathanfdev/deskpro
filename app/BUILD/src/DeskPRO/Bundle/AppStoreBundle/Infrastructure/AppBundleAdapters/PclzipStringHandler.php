<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppBundleAdapters;

class PclzipStringHandler
{
    public function read( \PclZip $from, array $fileEntry)
    {
        if ($fileEntry['folder']) {
            throw new \DomainException('entry is a folder not a file');
        }

        $tmpPath = $this->createTempDir();
        $filename = $tmpPath . PATH_SEPARATOR . $fileEntry['stored_filename'];
        $from->extractByIndex($fileEntry['index'], $tmpPath);


        if (is_dir($filename)) {
            throw new \DomainException('extracted entry is a folder not a file');
        }

        $contents = file_get_contents($filename);
        unlink($tmpPath);
        unlink($filename);
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
            mkdir($contentDir, 0777);
        }

        file_put_contents($actualPath, pathinfo($actualPath, PATHINFO_BASENAME));
        $into->add($actualPath, "", $tempDir);
        $this->removeDir($tempDir);
    }

    private function removeDir($dir)
    {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR. $file;
            is_dir($path) ? $this->removeDir($dir) : unlink($path);
        }
        return rmdir($dir);
    }

    /**
     * @return null|string
     */
    private function createTempDir()
    {
        $tmpFile = tempnam(sys_get_temp_dir(),'');
        if (file_exists($tmpFile)) {
            unlink($tmpFile);
        }
        mkdir($tmpFile);
        if (is_dir($tmpFile)) {
            return $tmpFile;
        }
        return null;
    }
}
