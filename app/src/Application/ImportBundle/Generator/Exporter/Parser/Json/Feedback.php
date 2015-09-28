<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\ImportBundle\Generator\Exporter\Parser\Json;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;
use Application\ImportBundle\Reader\Json\JsonReaderInterface;

/**
 * Feedback json file parser.
 *
 * Class Feedback
 */
final class Feedback extends AbstractParser
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_FEEDBACK;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->reader->getDirectoryFilesCount(JsonReaderInterface::ENTITY_FEEDBACK_PATH, $this->getBatchNum());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($this->reader->getData(JsonReaderInterface::ENTITY_FEEDBACK_PATH, $this->getBatchNum()))
            ->setPrefix('JSONFeedback')
            ->setRefColumn('oid')
            ->setMethod('exportFeedback')
            ->setAdvanceProgressbar(true)
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns a feedback entity.
     *
     * @param array $data
     *
     * @return Entity\Feedback
     */
    protected function exportFeedback(array $data)
    {
        $formatted = $this->formatter->format($data, array(
            'oid'            => TransformerInterface::TYPE_STRING,
            'import_map_key' => TransformerInterface::TYPE_STRING,
            'destination'    => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix'     => 'feedback_',
                'ref'        => 'oid',
            )),
            'person'         => TransformerInterface::TYPE_STRING,
            'language'       => TransformerInterface::TYPE_STRING,
            'title'          => TransformerInterface::TYPE_STRING,
            'slug'           => TransformerInterface::TYPE_STRING,
            'content'        => TransformerInterface::TYPE_STRING,
            'popularity'     => TransformerInterface::TYPE_STRING,
            'status'         => TransformerInterface::TYPE_STRING,
            'total_rating'   => TransformerInterface::TYPE_INT,
            'num_comments'   => TransformerInterface::TYPE_INT,
            'num_ratings'    => TransformerInterface::TYPE_INT,
            'view_count'     => TransformerInterface::TYPE_INT,
            'category'       => TransformerInterface::TYPE_STRING,
            'date_created'   => TransformerInterface::TYPE_DATE,
            'date_published' => TransformerConfiguration::create(TransformerInterface::TYPE_DATE, array(
                'null'       => true,
            )),
            'labels'        => TransformerInterface::TYPE_ARRAY,
            'attachments'   => TransformerInterface::TYPE_ARRAY,
            'custom_fields' => TransformerInterface::TYPE_ARRAY,
        ));

        $entity = new Entity\Feedback();
        $entity
            ->setRawData($data)
            ->setOid($formatted['oid'])
            ->setImportMapKey($formatted['import_map_key'])
            ->setDestination($formatted['destination'])
            ->setPersonEmail($formatted['person'])
            ->setLanguage($formatted['language'])
            ->setTitle($formatted['title'])
            ->setContent($formatted['content'])
            ->setSlug($formatted['slug'])
            ->setPopularity($formatted['popularity'])
            ->setStatus($formatted['status'])
            ->setTotalRating($formatted['total_rating'])
            ->setNumComments($formatted['num_comments'])
            ->setNumRatings($formatted['num_ratings'])
            ->setViewCount($formatted['view_count'])
            ->setCategory($formatted['category'])
            ->setDateCreated($formatted['date_created'])
            ->setDatePublished($formatted['date_published'])
        ;

        foreach ($formatted['labels'] as $label) {
            $entity->addLabel($label);
        }

        $attachments = $this->getAttachmentParser()->exportAttachments($formatted['attachments']);
        foreach ($attachments as $attachment) {
            $entity->addAttachment($attachment);
        }

        $custom_fields = $this->getCustomFieldsParser()->export($formatted['custom_fields']);
        foreach ($custom_fields as $custom_field) {
            $entity->addCustomField($custom_field);
        }

        return $entity;
    }
}
