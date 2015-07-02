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

use Application\ImportBundle\Generator\Exporter\Parser\NoColumnException;
use Application\ImportBundle\Reader\Csv\CsvConfig;
use Application\ImportBundle\Reader\Csv\CsvReaderException;
use Application\ImportBundle\Reader\Csv\CsvReaderInterface;
use Application\ImportBundle\Entity;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

/**
 * Abstract csv parser
 *
 * Class AbstractCsv
 * @package Application\ImportBundle\Generator\Exporter\Parser\Csv
 */
abstract class AbstractParser extends \Application\ImportBundle\Generator\Exporter\Parser\AbstractParser
{
    const FILE_ARTICLES               = 'articles.csv';
    const FILE_ARTICLE_CUSTOM_FIELDS  = 'article_custom_fields.csv';
    const FILE_DOWNLOADS              = 'downloads.csv';
    const FILE_DOWNLOAD_ATTACHMENTS   = 'downloads_attachments.csv';
    const FILE_FEEDBACK               = 'feedback.csv';
    const FILE_FEEDBACK_ATTACHMENTS   = 'feedback_attachments.csv';
    const FILE_FEEDBACK_CUSTOM_FIELDS = 'feedback_custom_fields.csv';
    const FILE_NEWS                   = 'news.csv';
    const FILE_PEOPLE                 = 'people.csv';
    const FILE_PEOPLE_CUSTOM_FIELDS   = 'people_custom_fields.csv';
    const FILE_TICKETS                = 'tickets.csv';
    const FILE_TICKET_MESSAGES        = 'ticket_messages.csv';
    const FILE_TICKET_ATTACHMENTS     = 'ticket_attachments.csv';
    const FILE_TICKET_CUSTOM_FIELDS   = 'ticket_custom_fields.csv';

    const CUSTOM_FIELD_PREFIX         = 'custom_field_';

    /**
     * @var CsvReaderInterface
     */
    protected $reader;

    /**
     * Constructor
     *
     * @param CsvReaderInterface $reader
     */
    public function __construct(CsvReaderInterface $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Get csv reader config
     *
     * @param string $record_type
     * @return CsvConfig
     */
    protected function getReaderConfig($record_type)
    {
        /** @var CsvConfig $base */
        $base = $this->reader->getConfig();

        $config = clone $base;
        $config->setResource($base->getResource() . '/' . $record_type);

        return $config;
    }

    /**
     * Returns rows count of csv file
     *
     * @param CsvConfig $config
     * @return int
     */
    protected function getReaderCount(CsvConfig $config)
    {
        try {
            return $this->reader->getRowsCount($config);

        } catch (NotFoundResourceException $e) {
            $this->logWarning(sprintf('Resource `%s` not found (Skipping)', $config->getResource()));
        }

        return 0;
    }

    /**
     * Returns a collection of exporting data
     *
     * @param CsvConfig $config
     * @return array
     */
    protected function getReaderData(CsvConfig $config)
    {
        try {
            $data = $this->reader->getData($config);
            if (count($data) === 0) {
                $this->logWarning(sprintf('No records found in resource `%s`', $config->getResource()));
            }

            return $data;

        } catch (NotFoundResourceException $e) {
            $this->logWarning(sprintf('Resource `%s` not found (Skipping)', $config->getResource()));

        } catch (CsvReaderException $e) {
            $this->logWarning(sprintf(
                'Csv reader throws an exception while reading `%s`. Reason: %s',
                $config->getResource(), $e->getMessage()
            ));
        }

        return array();
    }

    /**
     * Returns a collection of attachments
     *
     * @param CsvConfig $config
     * @param string    $destination_prefix
     * @param string    $ref_column
     *
     * @return Entity\Collection
     */
    protected function exportAttachments(CsvConfig $config, $destination_prefix, $ref_column)
    {
        $collection  = new Entity\Collection();
        $attachments = $this->getReaderData($config);

        foreach ($attachments as $num => $attachment) {
            try {
                $entity = $this->exportAttachment($num, $destination_prefix, $attachment, $ref_column);
                if ($entity) {
                    $collection->attach($entity);
                    $this->logInfo(sprintf(
                        'Attachment of entity `%s%s` parsed successfully!',
                        $destination_prefix, $entity->getOid())
                    );
                } else {
                    $this->logWarning(sprintf('Invalid attachment record `%d` found (Skipping)', $num));
                }

            } catch (NoColumnException $e) {
                $this->logWarning(sprintf(
                    'Invalid attachment record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));
            }
        }

        return $collection;
    }

    /**
     * Returns an attachment entity
     *
     * @param int    $num
     * @param string $destination_prefix
     * @param array  $attachment
     * @param string $ref_column
     *
     * @return Entity\Attachment|null
     */
    protected function exportAttachment($num, $destination_prefix, array $attachment, $ref_column)
    {
        if ($this->isAttachmentValid($attachment, $ref_column)) {
            $entity = new Entity\Attachment();
            $entity
                ->setDestination($destination_prefix . $attachment[$ref_column])
                ->setOid($num)
                ->setPersonEmail($attachment['person'])
                ->setBlobUrl($attachment['blob_url'])
                ->setBlobPath($attachment['blob_path'])
                ->setFileName($attachment['file_name'])
                ->setContentType($attachment['content_type'])
                ->setAsInline($this->isBooleanTrue($attachment['is_inline']))
            ;

            return $entity;
        }

        return null;
    }

    /**
     * Check if an attachment has all required columns
     *
     * @param array  $attachment
     * @param string $ref_column
     *
     * @return bool
     */
    protected function isAttachmentValid(array $attachment, $ref_column)
    {
        $columns = array(
            $ref_column,
            'person',
            'blob_url',
            'blob_path',
            'file_name',
            'content_type',
            'is_inline',
        );

        return $this->hasRequiredColumns($attachment, $columns);
    }

    /**
     * Returns a collection of custom fields
     *
     * @param CsvConfig $config
     * @param string    $destination_prefix
     * @param string    $ref_column
     *
     * @return Entity\Collection
     */
    protected function exportCustomFields(CsvConfig $config, $destination_prefix, $ref_column)
    {
        $collection    = new Entity\Collection();
        $custom_fields = $this->getReaderData($config);

        foreach ($custom_fields as $num => $custom_field) {
            try {
                $entity = $this->exportAttachment($num, $destination_prefix, $custom_field, $ref_column);
                if ($entity) {
                    $collection->attach($entity);
                    $this->logInfo(sprintf(
                        'Custom field of entity `%s%s` parsed successfully!',
                        $destination_prefix, $entity->getOid())
                    );
                } else {
                    $this->logWarning(sprintf('Invalid custom field record `%d` found (Skipping)', $num));
                }

            } catch (NoColumnException $e) {
                $this->logWarning(sprintf(
                    'Invalid custom field record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));
            }
        }

        return $collection;
    }

    /**
     * Returns an attachment entity
     *
     * @param int    $num
     * @param string $destination_prefix
     * @param array  $custom_field
     * @param string $ref_column
     *
     * @return Entity\CustomField|null
     */
    protected function exportCustomField($num, $destination_prefix, array $custom_field, $ref_column)
    {
        if ($this->isAttachmentValid($custom_field, $ref_column)) {
            $entity = new Entity\CustomField();
            $entity
                ->setDestination($destination_prefix . $custom_field[$ref_column])
                ->setOid($num)
                ->setKey($custom_field['field_name'])
                ->setValue($custom_field['value'])
            ;

            return $entity;
        }

        return null;
    }

    /**
     * Check if a custom field has all required columns
     *
     * @param array  $custom_field
     * @param string $ref_column
     *
     * @return bool
     */
    protected function isCustomFieldValid(array $custom_field, $ref_column)
    {
        $columns = array(
            $ref_column,
            'field_name',
            'value',
        );

        return $this->hasRequiredColumns($custom_field, $columns);
    }
}
