<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\ImportBundle\Generator\Exporter\Parser\Csv\Helper\Blob;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\AbstractParserFormatterHelper;

/**
 * Class Blob.
 */
class Blob extends AbstractParserFormatterHelper
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_BLOB;
    }

    /**
     * Returns a blob entity.
     *
     * @param int    $oid
     * @param string $destination_prefix
     * @param array  $data
     * @param string $ref_column
     *
     * @return Entity\Blob|null
     */
    public function export($oid, $destination_prefix, array $data, $ref_column)
    {
        $formatted = $this->formatter->format($data, [
            $ref_column   => TransformerInterface::TYPE_STRING,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, [
                'prefix' => $destination_prefix,
                'ref'    => $ref_column,
            ]),
            'file_name'    => TransformerInterface::TYPE_STRING,
            'content_type' => TransformerInterface::TYPE_STRING,
            'blob_url'     => TransformerInterface::TYPE_STRING,
            'blob_path'    => TransformerInterface::TYPE_STRING,
        ]);

        $entity = new Entity\Blob();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($oid)
            ->setBlobUrl($formatted['blob_url'])
            ->setBlobPath($formatted['blob_path'])
            ->setFileName($formatted['file_name'])
            ->setContentType($formatted['content_type'])
        ;

        return $entity;
    }
}
