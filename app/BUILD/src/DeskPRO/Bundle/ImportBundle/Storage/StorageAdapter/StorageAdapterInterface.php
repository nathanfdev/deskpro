<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
