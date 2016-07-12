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

namespace Application\ImportBundle\Generator\Exporter\Parser\Json\Helper;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\AbstractParserFormatterHelper;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;

/**
 * Class Attachment.
 */
class Attachment extends AbstractParserFormatterHelper
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_ATTACHMENT;
    }

    /**
     * Returns a collection of attachments entities.
     *
     * @param array $attachments
     *
     * @return Entity\Attachment[]
     */
    public function exportAttachments(array $attachments)
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($attachments)
            ->setPrefix('JSONAttachment')
            ->setRefColumn('oid')
            ->setMethod('exportAttachment')
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns an attachment entity.
     *
     * @param array $data
     *
     * @return Entity\Attachment|null
     */
    public function exportAttachment(array $data = null)
    {
        if (empty($data)) {
            return;
        }

        $formatted = $this->formatter->format($data, [
            'oid'         => TransformerInterface::TYPE_STRING,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, [
                'prefix' => 'attachment_',
                'ref'    => 'oid',
            ]),
            'blob_data'    => TransformerInterface::TYPE_STRING,
            'blob_url'     => TransformerInterface::TYPE_STRING,
            'blob_path'    => TransformerInterface::TYPE_STRING,
            'file_name'    => TransformerInterface::TYPE_STRING,
            'content_type' => TransformerInterface::TYPE_STRING,
            'person'       => TransformerInterface::TYPE_STRING,
            'is_inline'    => TransformerInterface::TYPE_BOOLEAN,
        ]);

        $entity = new Entity\Attachment();
        $entity
            ->setRawData($data)
            ->setOid($formatted['oid'])
            ->setDestination($formatted['destination'])
            ->setBlobData($formatted['blob_data'])
            ->setBlobUrl($formatted['blob_url'])
            ->setBlobPath($formatted['blob_path'])
            ->setFileName($formatted['file_name'])
            ->setContentType($formatted['content_type'])
            ->setPersonEmail($formatted['person'])
            ->setAsInline($formatted['is_inline']);

        return $entity;
    }
}
