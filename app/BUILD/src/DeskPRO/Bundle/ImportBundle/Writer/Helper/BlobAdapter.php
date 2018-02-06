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
