<?php

namespace DeskPRO\Bundle\ImportBundle\Storage\StorageAdapter;

/**
 * Interface StorageAdapterInterface.
 */
interface StorageAdapterInterface
{
    /**
     * @return string
     */
    public function getBasePath();

    /**
     * @param string $basePath
     */
    public function setBasePath($basePath);

    /**
     * @param string $type
     *
     * @return int
     */
    public function getNextBatchId($type);

    /**
     * @param string $type
     * @param int    $batchId
     *
     * @return bool
     */
    public function hasBatch($type, $batchId);

    /**
     * @param string $type
     * @param int    $batchId
     *
     * @return array
     */
    public function readBatch($type, $batchId);

    /**
     * @param string $type
     * @param int    $batchId
     * @param string $filename
     *
     * @retun bool
     */
    public function hasModel($type, $batchId, $filename);

    /**
     * @param string $type
     * @param int    $batchId
     * @param string $filename
     *
     * @retun string
     */
    public function readModel($type, $batchId, $filename);

    /**
     * @param string $type
     * @param int    $batchId
     * @param string $filename
     * @param string $encodedData
     */
    public function writeModel($type, $batchId, $filename, $encodedData);

    /**
     * @return string
     */
    public function readBatchConfig();

    /**
     * @param string $encodedData
     */
    public function writeBatchConfig($encodedData);

    /**
     * @param string $maxSize
     *
     * @return string
     */
    public function getLastLogFile($maxSize);

    /**
     * @return string[]
     */
    public function getLogFilenames();

    /**
     * @param string $filename
     *
     * @return string
     */
    public function readLogFile($filename);

    /**
     * @param string $filename
     * @param string $data
     */
    public function writeLogFile($filename, $data);

    /**
     * Removes json imported files.
     */
    public function clean();
}
