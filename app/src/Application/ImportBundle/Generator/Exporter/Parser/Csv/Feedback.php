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

namespace Application\ImportBundle\Generator\Exporter\Parser\Csv;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;
use Application\ImportBundle\Reader\Csv\CsvReaderInterface;

/**
 * Feedback csv file parser.
 *
 * Class Feedback
 */
final class Feedback extends AbstractParser
{
    const FEEDBACK_PREFIX = 'feedback_';

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
        return $this->getReaderCount(CsvReaderInterface::FILE_FEEDBACK);
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($this->getReaderData(CsvReaderInterface::FILE_FEEDBACK))
            ->setPrefix('CSVFeedback')
            ->setRefColumn('id')
            ->setMethod('exportFeedback')
            ->setAdvanceProgressbar(true)
        ;

        $collection    = $this->exportCollection($config);
        $attachments   = $this->exportFeedbackAttachments();
        $custom_fields = $this->exportFeedbackCustomFields();

        foreach ($collection as $num => $feedback) {
            /* @var Entity\Feedback $feedback */
            foreach ($attachments as $attachment) {
                if ($attachment->getDestination() === self::FEEDBACK_PREFIX.$feedback->getOid()) {
                    $feedback->addAttachment($attachment);
                }
            }
            foreach ($custom_fields as $custom_field_entity) {
                if ($feedback->getDestination() === $custom_field_entity->getDestination()) {
                    $feedback->addCustomField($custom_field_entity);
                }
            }

            $inline_custom_fields = $this->getInlineCustomFieldsParser()->export($feedback->getDestination(), $feedback->getRawData());
            foreach ($inline_custom_fields as $custom_field_entity) {
                $feedback->addCustomField($custom_field_entity);
            }
        }

        return $collection;
    }

    /**
     * Returns a feedback entity.
     *
     * @param array $data
     * @param int   $num
     *
     * @return Entity\Feedback|null
     */
    protected function exportFeedback(array $data, $num)
    {
        $formatted = $this->formatter->format($data, array(
            'id'          => TransformerConfiguration::create(TransformerInterface::TYPE_STRING, array(
                'default' => 'num_'.$num,
            )),
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix'  => self::FEEDBACK_PREFIX,
                'ref'     => 'id',
            )),
            'person'         => TransformerInterface::TYPE_STRING,
            'title'          => TransformerInterface::TYPE_STRING,
            'content'        => TransformerInterface::TYPE_STRING,
            'slug'           => TransformerInterface::TYPE_STRING,
            'language'       => TransformerInterface::TYPE_STRING,
            'popularity'     => TransformerInterface::TYPE_STRING,
            'status'         => TransformerInterface::TYPE_STRING,
            'category'       => TransformerInterface::TYPE_STRING,
            'label'          => TransformerInterface::TYPE_STRING,
            'date_created'   => TransformerInterface::TYPE_DATE,
            'date_published' => TransformerInterface::TYPE_DATE,
        ));

        $entity = new Entity\Feedback();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($formatted['id'])
            ->setPersonEmail($formatted['person'])
            ->setLanguage($formatted['language'])
            ->setTitle($formatted['title'])
            ->setContent($formatted['content'])
            ->setSlug($formatted['slug'])
            ->setPopularity($formatted['popularity'])
            ->setStatus($formatted['status'])
            ->setCategory($formatted['category'])
            ->setDateCreated($formatted['date_created'])
            ->setDatePublished($formatted['date_published'])
        ;

        if ($formatted['label']) {
            $entity->addLabel($formatted['label']);
        }

        return $entity;
    }

    /**
     * Returns a collection of ticket attachments.
     *
     * @return Entity\Attachment[]|Entity\Collection
     */
    private function exportFeedbackAttachments()
    {
        $data = $this->getReaderData(CsvReaderInterface::FILE_FEEDBACK_ATTACHMENTS);

        return $this->getAttachmentParser()->exportAttachments($data, self::FEEDBACK_PREFIX, 'feedback_id');
    }

    /**
     * Returns a collection of ticket custom field data.
     *
     * @return Entity\CustomField[]|Entity\Collection
     */
    private function exportFeedbackCustomFields()
    {
        $data = $this->getReaderData(CsvReaderInterface::FILE_FEEDBACK_CUSTOM_FIELDS);

        return $this->getMultipleCustomFieldsParser()->export($data, self::FEEDBACK_PREFIX, 'feedback_id');
    }
}
