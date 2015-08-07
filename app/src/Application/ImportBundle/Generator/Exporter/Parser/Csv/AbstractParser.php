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

use Application\ImportBundle\ContactData\ContactDataFactory;
use Application\ImportBundle\Generator\Exporter\Parser\Csv\ContactData\Inline\InlineContactDataFactory;
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
    const FILE_ARTICLES                   = 'articles.csv';
    const FILE_ARTICLE_CUSTOM_FIELDS      = 'article_custom_fields.csv';
    const FILE_DOWNLOADS                  = 'downloads.csv';
    const FILE_DOWNLOAD_ATTACHMENTS       = 'downloads_attachments.csv';
    const FILE_FEEDBACK                   = 'feedback.csv';
    const FILE_FEEDBACK_ATTACHMENTS       = 'feedback_attachments.csv';
    const FILE_FEEDBACK_CUSTOM_FIELDS     = 'feedback_custom_fields.csv';
    const FILE_NEWS                       = 'news.csv';
    const FILE_PEOPLE                     = 'people.csv';
    const FILE_PEOPLE_CONTACT_DATA        = 'people_contact_data.csv';
    const FILE_PEOPLE_CUSTOM_FIELDS       = 'people_custom_fields.csv';
    const FILE_TICKETS                    = 'tickets.csv';
    const FILE_TICKET_MESSAGES            = 'ticket_messages.csv';
    const FILE_TICKET_ATTACHMENTS         = 'ticket_attachments.csv';
    const FILE_TICKET_CUSTOM_FIELDS       = 'ticket_custom_fields.csv';
    const FILE_ORGANIZATIONS              = 'organizations.csv';
    const FILE_ORGANIZATION_CONTACT_DATA  = 'organization_contact_data.csv';
    const FILE_ORGANIZATION_CUSTOM_FIELDS = 'organization_custom_fields.csv';

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
        $config->setResource(rtrim($base->getResource(), '/') . '/' . $record_type);

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
            $this->logInfo(sprintf('Resource `%s` not found (Skipping)', $config->getResource()));
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
            $this->logInfo(sprintf('Resource `%s` not found (Skipping)', $config->getResource()));

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
        /** @var Entity\Attachment $entity */
        $entity = $this->exportBlob($num, $destination_prefix, $attachment, $ref_column, new Entity\Attachment());
        if ($entity && $this->isAttachmentValid($attachment, $ref_column)) {
            $entity
                ->setPersonEmail($attachment['person'])
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
            'person',
            'is_inline',
        );

        return $this->isBlobValid($attachment, $ref_column)
            && $this->hasRequiredColumns($attachment, $columns);
    }

    /**
     * Returns a blob entity
     *
     * @param int              $num
     * @param string           $destination_prefix
     * @param array            $blob
     * @param string           $ref_column
     * @param Entity\Blob|null $entity
     *
     * @return Entity\Blob|null
     */
    protected function exportBlob($num, $destination_prefix, array $blob, $ref_column, Entity\Blob $entity = null)
    {
        if ($this->isBlobValid($blob, $ref_column)) {
            $entity = $entity ? : new Entity\Blob();
            $entity
                ->setRawData($blob)
                ->setDestination($this->formatDestination($destination_prefix, $blob[$ref_column]))
                ->setOid($num)
                ->setBlobUrl(@$blob['blob_url'])
                ->setBlobPath(@$blob['blob_path'])
                ->setFileName($blob['file_name'])
                ->setContentType($blob['content_type'])
            ;

            return $entity;
        }

        return null;
    }

    /**
     * Check if a blob has all required columns
     *
     * @param array  $blob
     * @param string $ref_column
     *
     * @return bool
     */
    protected function isBlobValid(array $blob, $ref_column)
    {
        $columns = array(
            $ref_column,
            'file_name',
            'content_type',
        );

        $blob_columns = array(
            'blob_url',
            'blob_path',
        );

        return $this->hasRequiredColumns($blob, $columns)
            && $this->hasAnyRequiredColumn($blob, $blob_columns);
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
                $entity = $this->exportCustomField($num, $destination_prefix, $custom_field, $ref_column);
                if ($entity) {
                    $collection->attach($entity);
                    $this->logInfo(sprintf('Custom field of entity `%s` parsed successfully!', $entity->getDestination()));
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
        if ($this->isCustomFieldValid($custom_field, $ref_column)) {
            $entity = new Entity\CustomField();
            $entity
                ->setRawData($custom_field)
                ->setDestination($this->formatDestination($destination_prefix, $custom_field[$ref_column]))
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

    /**
     * Returns a collection of inline custom fields
     *
     * @param string $destination
     * @param array  $entity
     *
     * @return Entity\Collection
     */
    protected function exportInlineCustomFields($destination, array $entity)
    {
        $collection    = new Entity\Collection();
        $custom_fields = $this->parseInlineCustomFields($entity);

        foreach ($custom_fields as $num => $custom_field) {
            $entity = new Entity\CustomField();
            $entity
                ->setOid($custom_field['property'])
                ->setDestination($destination)
                ->setKey($custom_field['field_name'])
                ->setValue($custom_field['value'])
            ;

            $collection->attach($entity);

        }

        return $collection;
    }

    /**
     * Returns a collection of organization contact data entities
     *
     * @param CsvConfig $config
     * @param string    $destination_prefix
     * @param string    $ref_column
     *
     * @return Entity\Collection
     */
    protected function exportContactData(CsvConfig $config, $destination_prefix, $ref_column)
    {
        $collection   = new Entity\Collection();
        $contact_info = $this->exportContactDataFields($config, $destination_prefix, $ref_column);

        foreach ($contact_info as $destination => $contacts) {
            foreach ($contacts as $oid => $contact) {
                try {
                    if ($this->isContactValid($contact)) {
                        $handler = ContactDataFactory::getHandler($contact['contact_type']);
                        $entity  = $handler->toEntity($contact);
                        $entity
                            ->setOid($oid)
                            ->setDestination($destination)
                        ;

                        $collection->attach($entity);
                        $this->logInfo(sprintf('Entity `%s%s` parsed successfully!', $entity->getDestination()));
                    } else {
                        $this->logWarning(sprintf('Invalid contact record `%d` found (Skipping)', $destination));
                    }

                } catch (NoColumnException $e) {
                    $this->logWarning(sprintf(
                        'Invalid contact record `%d` found (Skipping): %s',
                        $destination, $e->getMessage()
                    ));
                }

            }
        }

        return $collection;
    }

    /**
     * Merges contact data fields to array
     *
     * @param CsvConfig $config
     * @param string    $destination_prefix
     * @param string    $ref_column
     *
     * @return array
     */
    protected function exportContactDataFields(CsvConfig $config, $destination_prefix, $ref_column)
    {
        $contact_fields = $this->getReaderData($config);
        $contact_info  = array();

        foreach ($contact_fields as $num => $field) {
            try {
                if ($this->isContactFieldValid($field, $ref_column)) {
                    $destination = $this->formatDestination($destination_prefix, $field[$ref_column]);
                    $contact_info[$destination][$field['contact_id']][$field['field_name']] = $field['value'];

                    $this->logInfo(sprintf('Contact field `%s` parsed successfully!', $destination));
                } else {
                    $this->logWarning(sprintf('Invalid contact field record `%d` found (Skipping)', $num));
                }

            } catch (NoColumnException $e) {
                $this->logWarning(sprintf(
                    'Invalid contact field record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));
            }
        }

        return $contact_info;
    }

    /**
     * Check if contact data field has all required columns
     *
     * @param array  $contact
     * @param string $ref_column
     *
     * @return bool
     */
    protected function isContactFieldValid(array $contact, $ref_column)
    {
        $columns = array(
            $ref_column,
            'contact_id',
            'field_name',
            'value',
        );

        return $this->hasRequiredColumns($contact, $columns);
    }

    /**
     * Check if contact data has all required columns
     *
     * @param array $contact
     * @return bool
     */
    protected function isContactValid(array $contact)
    {
        $columns = array(
            'contact_type',
        );

        return $this->hasRequiredColumns($contact, $columns);
    }

    /**
     * Parses inline custom fields from entity
     *
     * @param array $entity
     * @return array
     */
    protected function parseInlineCustomFields(array $entity)
    {
        $properties    = array_keys($entity);
        $custom_fields = array();

        foreach ($properties as $property) {
            if (preg_match('/^custom "([^"]+)"$/', $property, $matches)) {
                $custom_fields[] = array(
                    'property'   => $property,
                    'field_name' => $matches[1],
                    'value'      => $entity[$property],
                );
            }
        }

        return $custom_fields;
    }

    /**
     * @param array $entity
     * @return Entity\ContactData[]
     */
    protected function exportInlineContactData(array $entity)
    {
        $parser = InlineContactDataFactory::getParser();
        return $parser->parse($entity);
    }
}
