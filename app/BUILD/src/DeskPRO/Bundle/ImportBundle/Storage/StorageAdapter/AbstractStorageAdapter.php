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
