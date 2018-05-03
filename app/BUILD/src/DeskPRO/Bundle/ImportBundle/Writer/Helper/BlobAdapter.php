<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Helper;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use DeskPRO\Bundle\ImportBundle\Model;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\BlobDataMapper;

/**
 * Blob storage adapter.
 *
 * Class BlobAdapter
 */
class BlobAdapter
{
    /**
     * @var DeskproBlobStorage
     */
    private $blobStorage;

    /**
     * Constructor.
     *
     * @param DeskproBlobStorage $blobStorage
     */
    public function __construct(DeskproBlobStorage $blobStorage)
    {
        $this->blobStorage = $blobStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function createBySourceData($source_data, $filename, $content_type)
    {
        return $this->blobStorage->createBlobRecordFromString($source_data, $filename, $content_type);
    }

    /**
     * {@inheritdoc}
     */
    public function createByBlob(Model\Blob $blob, $throwException = true)
    {
        $blobData = BlobDataMapper::findOneByParams(
            $blob->getBlobData(),
            $blob->getBlobPath(),
            $blob->getBlobUrl()
        );

        if (!$blobData) {
            if ($throwException) {
                throw new \Exception('Blob data is empty');
            }

            return false;
        }

        if (!$blob->getFileName() || !$blob->getContentType()) {
            if ($throwException) {
                throw new \Exception('Blob info is empty');
            }

            return false;
        }

        return $this->createBySourceData($blobData, $blob->getFileName(), $blob->getContentType());
    }
}
