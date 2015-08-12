<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Generator\Exporter\Parser\Csv;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerException;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;

/**
 * Feedback csv file parser
 *
 * Class Feedback
 * @package Application\ImportBundle\Generator\Exporter\Parser\Csv
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
        return $this->getReaderCount($this->getFeedbackReaderConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection     = new Entity\Collection();

        $feedback_items = $this->getReaderData($this->getFeedbackReaderConfig());
        $attachments    = $this->exportFeedbackAttachments();
        $custom_fields  = $this->exportFeedbackCustomFields();

        foreach ($feedback_items as $num => $feedback) {
            $this->advanceProgressBar();

            try {
                $entity = $this->exportFeedback($num, $feedback);

                foreach ($attachments as $attachment) {
                    /** @var Entity\Attachment $attachment */
                    if ($attachment->getDestination() === self::FEEDBACK_PREFIX . $entity->getOid()) {
                        $entity->addAttachment($attachment);
                    }
                }
                foreach ($custom_fields as $custom_field_entity) {
                    /** @var Entity\CustomField $custom_field_entity */
                    if ($entity->getDestination() === $custom_field_entity->getDestination()) {
                        $entity->addCustomField($custom_field_entity);
                    }
                }

                $inline_custom_fields = $this->getInlineCustomFieldsParser()->export($entity->getDestination(), $feedback);
                foreach ($inline_custom_fields as $custom_field_entity) {
                    $entity->addCustomField($custom_field_entity);
                }

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));

            } catch (TransformerException $e) {
                $this->logWarning(sprintf(
                    'Invalid feedback record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));

            } catch (\Exception $e) {
                $this->logError(sprintf(
                    'Invalid contact data record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));
            }
        }

        return $collection;
    }

    /**
     * Returns a feedback entity
     *
     * @param int   $num
     * @param array $data
     *
     * @return Entity\Feedback|null
     */
    private function exportFeedback($num, array $data)
    {
        $configuration = array(
            'id'             => TransformerConfiguration::create(TransformerInterface::TYPE_STRING, array(
                'default' => 'num_' . $num,
            )),
            'destination'    => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => self::FEEDBACK_PREFIX,
                'ref'    => 'id',
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
        );

        $formatted = $this->formatter->format($data, $configuration);
        $entity    = new Entity\Feedback();
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
     * Returns a collection of ticket attachments
     *
     * @return Entity\Collection
     */
    private function exportFeedbackAttachments()
    {
        $config = $this->getFeedbackAttachmentsReaderConfig();
        $data   = $this->getReaderData($config);

        return $this->getAttachmentParser()->exportAttachments($data, self::FEEDBACK_PREFIX, 'feedback_id');
    }

    /**
     * Returns a collection of ticket custom field data
     *
     * @return Entity\Collection
     */
    private function exportFeedbackCustomFields()
    {
        $config = $this->getFeedbackCustomFieldReaderConfig();
        $data   = $this->getReaderData($config);

        return $this->getMultipleCustomFieldsParser()->export($data, self::FEEDBACK_PREFIX, 'feedback_id');
    }

    /**
     * Returns record type reader config
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getFeedbackReaderConfig()
    {
        return $this->getReaderConfig(self::FILE_FEEDBACK);
    }

    /**
     * Returns reader config of feedback attachment records
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getFeedbackAttachmentsReaderConfig()
    {
        return $this->getReaderConfig(self::FILE_FEEDBACK_ATTACHMENTS);
    }

    /**
     * Returns reader config for feedback custom field records
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getFeedbackCustomFieldReaderConfig()
    {
        return $this->getReaderConfig(self::FILE_FEEDBACK_CUSTOM_FIELDS);
    }
}
