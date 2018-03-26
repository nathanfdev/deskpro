<?php

namespace DeskPRO\Bundle\ImportBundle\Storage\StorageAdapter;

use RecursiveIteratorIterator;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Symfony\Component\Finder\Iterator\SortableIterator;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class FilesystemAdapter.
 */
class FilesystemAdapter extends AbstractStorageAdapter
{
    /**
     * {@inheritdoc}
     */
    public function getNextBatchId($type)
    {
        $typePath = $this->basePath.'/'.$type;
        if (!is_dir($typePath)) {
            return 1;
        }

        $finder = new Finder();
        $finder
            ->in($typePath)
            ->directories()
            ->sort(function (\SplFileInfo $a, \SplFileInfo $b) {
                return (int) $b->getFilename() - (int) $a->getFilename();
            })
        ;
        /** @var \SplFileInfo $dir */
        $dir = $finder->getIterator()->current();
        if ($dir) {
            return (int) $dir->getFilename() + 1;
        }

        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function hasBatch($type, $batchId)
    {
        return is_dir($this->getBatchPath($type, $batchId));
    }

    /**
     * {@inheritdoc}
     */
    public function readBatch($type, $batchId)
    {
        $data     = [];
        $iterator = $this->getIterator($this->getBatchPath($type, $batchId));

        foreach ($iterator as $file) {
            $data[$file->getBasename('.'.$file->getExtension())] = $file->getContents();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function hasModel($type, $batchId, $filename)
    {
        return file_exists($this->getFilePath($type, $batchId, $filename));
    }

    /**
     * {@inheritdoc}
     */
    public function readModel($type, $batchId, $filename)
    {
        $filePath = $this->getFilePath($type, $batchId, $filename);
        if (!file_exists($filePath)) {
            return;
        }

        $encodedData = @file_get_contents($filePath);
        if (!$encodedData) {
            throw new \RuntimeException("Unable to read from $filePath");
        }

        return $encodedData;
    }

    /**
     * {@inheritdoc}
     */
    public function writeModel($type, $batchId, $filename, $encodedData)
    {
        $batchPath = $this->getBatchPath($type, $batchId);
        $this->createPath($batchPath);

        $filePath = $this->getBatchPath($type, $batchId).'/'.$filename;
        if (!@file_put_contents($filePath, $encodedData)) {
            throw new \RuntimeException("Unable to write to $filePath");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function readBatchConfig()
    {
        if (!file_exists($this->getBatchFilePath())) {
            return;
        }

        return @file_get_contents($this->getBatchFilePath());
    }

    /**
     * {@inheritdoc}
     */
    public function writeBatchConfig($encodedData)
    {
        if (!@file_put_contents($this->getBatchFilePath(), $encodedData)) {
            throw new \RuntimeException("Unable to write batch config to {$this->getBatchFilePath()}");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLastLogFile($maxSize)
    {
        $logPath = $this->getLogsPath();
        if (!is_dir($logPath)) {
            return;
        }

        $finder = new Finder();
        $finder
            ->in($logPath)
            ->files()
            ->sort(function (\SplFileInfo $a, \SplFileInfo $b) {
                return $b->getFilename() > $a->getFilename();
            })
        ;
        /** @var \SplFileInfo $file */
        $file = $finder->getIterator()->current();
        if ($file && $file->getSize() < $maxSize) {
            return $file->getFilename();
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function readLogFile($filename)
    {
        $filePath = $this->getLogFilePath($filename);
        if (!$data = @file_get_contents($filePath)) {
            throw new \RuntimeException("Unable to read from $filePath");
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function writeLogFile($filename, $data)
    {
        $filePath = $this->getLogFilePath($filename);
        $this->createPath($this->getLogsPath());

        if (!@file_put_contents($filePath, $data)) {
            throw new \RuntimeException("Unable to write log file to $filePath");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clean()
    {
        $fs = new Filesystem();
        $fs->remove($this->basePath);
    }

    /**
     * Returns directory json files iterator.
     *
     * @param string $path
     *
     * @throws \RuntimeException
     *
     * @return SortableIterator|SplFileInfo[]
     */
    private function getIterator($path)
    {
        if (is_dir($path) === false) {
            throw new \RuntimeException(sprintf('Path `%s` not found', $path));
        }

        return new SortableIterator(
            new RecursiveIteratorIterator(
                new DirectoryIteratorFilter(
                    new RecursiveDirectoryIterator(
                        $path,
                        RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::CURRENT_AS_FILEINFO
                    )
                ),
                RecursiveIteratorIterator::SELF_FIRST | RecursiveIteratorIterator::LEAVES_ONLY
            ),
            SortableIterator::SORT_BY_NAME
        );
    }

    /**
     * @param string $path
     */
    private function createPath($path)
    {
        if (file_exists($path)) {
            if (!is_dir($path)) {
                throw new IOException("Output path '$path' is not a directory");
            }
            if (!is_readable($path) || !is_writable($path)) {
                throw new IOException("Unable to access $path");
            }
        } else {
            $fs = new Filesystem();
            $fs->mkdir($path);
        }
    }
}
