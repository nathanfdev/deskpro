<?php

namespace DeskPRO\Bundle\ImportBundle\Storage\StorageAdapter;

/**
 * Class AbstractStorageAdapter.
 */
abstract class AbstractStorageAdapter implements StorageAdapterInterface
{
    /**
     * @var string
     */
    protected $basePath;

    /**
     * Constructor.
     *
     * @param $basePath
     */
    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * {@inheritdoc}
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function getTypePath($type)
    {
        return sprintf('%s/%s/', $this->basePath, $type);
    }

    /**
     * Returns entity type path.
     *
     * @param string $type
     * @param int    $batchId
     *
     * @return string
     */
    protected function getBatchPath($type, $batchId)
    {
        return sprintf('%s%d/', $this->getTypePath($type), $batchId);
    }

    /**
     * @param string $type
     * @param int    $batchId
     * @param string $filename
     *
     * @return string
     */
    protected function getFilePath($type, $batchId, $filename)
    {
        return $this->getBatchPath($type, $batchId).$filename;
    }

    /**
     * @return string
     */
    protected function getBatchFilePath()
    {
        return sprintf('%s/batch.json', $this->basePath);
    }

    /**
     * @return string
     */
    protected function getLogsPath()
    {
        return sprintf('%s/logs/', $this->basePath);
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    protected function getLogFilePath($filename)
    {
        return sprintf('%s%s', $this->getLogsPath(), $filename);
    }
}
