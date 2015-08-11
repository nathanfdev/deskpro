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

use Application\ImportBundle\Generator\Exporter\Formatter\FormatterInterface;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerException;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\Csv\Helper\ContactData\Inline\InlineContactData;
use Application\ImportBundle\Generator\Exporter\Parser\Csv\Helper\ContactData\MultipleContactData;
use Application\ImportBundle\Generator\Exporter\Parser\ParserHelperSet;
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
     * @var FormatterInterface
     */
    protected $formatter;

    /**
     * Constructor
     *
     * @param CsvReaderInterface $reader
     * @param FormatterInterface $formatter
     * @param ParserHelperSet    $helpers
     */
    public function __construct(CsvReaderInterface $reader, FormatterInterface $formatter, ParserHelperSet $helpers)
    {
        $this->reader    = $reader;
        $this->formatter = $formatter;
        $this->helpers   = $helpers;
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

                $collection->attach($entity);
                $this->logInfo(sprintf(
                    'Attachment of entity `%s%s` parsed successfully!',
                    $destination_prefix, $entity->getOid())
                );

            } catch (\Exception $e) {
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
     * @param array  $data
     * @param string $ref_column
     *
     * @return Entity\Attachment|null
     */
    protected function exportAttachment($num, $destination_prefix, array $data, $ref_column)
    {
        $configuration = array(
            'person'    => TransformerInterface::TYPE_STRING,
            'is_inline' => TransformerInterface::TYPE_BOOLEAN,
        );

        $formatted = $this->formatter->format($data, $configuration);

        /** @var Entity\Attachment $entity */
        $entity = $this->exportBlob($num, $destination_prefix, $data, $ref_column, new Entity\Attachment());
        $entity
            ->setPersonEmail($formatted['person'])
            ->setAsInline($formatted['is_inline'])
        ;

        return $entity;
    }

    /**
     * Returns a blob entity
     *
     * @param int              $num
     * @param string           $destination_prefix
     * @param array            $data
     * @param string           $ref_column
     * @param Entity\Blob|null $entity
     *
     * @return Entity\Blob|null
     */
    protected function exportBlob($num, $destination_prefix, array $data, $ref_column, Entity\Blob $entity = null)
    {
        if (isset($data[$ref_column])) {
            $data['destination'] = $destination_prefix . $data[$ref_column];
        }

        $configuration = array(
            $ref_column    => TransformerInterface::TYPE_STRING,
            'destination'  => TransformerInterface::TYPE_DESTINATION,
            'file_name'    => TransformerInterface::TYPE_STRING,
            'content_type' => TransformerInterface::TYPE_STRING,
            'blob_url'     => TransformerInterface::TYPE_STRING,
            'blob_path'    => TransformerInterface::TYPE_STRING,
        );

        $formatted = $this->formatter->format($data, $configuration);
        $entity    = $entity ? : new Entity\Blob();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($num)
            ->setBlobUrl($formatted['blob_url'])
            ->setBlobPath($formatted['blob_path'])
            ->setFileName($formatted['file_name'])
            ->setContentType($formatted['content_type'])
        ;

        return $entity;
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

                $collection->attach($entity);
                $this->logInfo(sprintf('Custom field of entity `%s` parsed successfully!', $entity->getDestination()));

            } catch (TransformerException $e) {
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
     * @param array  $data
     * @param string $ref_column
     *
     * @return Entity\CustomField|null
     */
    protected function exportCustomField($num, $destination_prefix, array $data, $ref_column)
    {
        if (isset($data[$ref_column])) {
            $data['destination'] = $destination_prefix . $data[$ref_column];
        }

        $configuration = array(
            $ref_column   => TransformerInterface::TYPE_STRING,
            'destination' => TransformerInterface::TYPE_DESTINATION,
            'field_name'  => TransformerInterface::TYPE_STRING,
            'value'       => TransformerInterface::TYPE_STRING,
        );

        $formatted = $this->formatter->format($data, $configuration);
        $entity    = new Entity\CustomField();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($num)
            ->setKey($formatted['field_name'])
            ->setValue($formatted['value'])
        ;

        return $entity;
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
                ->setRawData($custom_field)
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
     * Returns a collection of contact data
     *
     * @param CsvConfig $config
     * @param string    $destination_prefix
     * @param string    $ref_column
     *
     * @return Entity\Collection
     */
    public function exportContactData(CsvConfig $config, $destination_prefix, $ref_column)
    {
        /** @var MultipleContactData $parser */
        $parser = $this->getHelper('multiple_contact_data');

        return $parser->export($this->getReaderData($config), $destination_prefix, $ref_column);
    }

    /**
     * @param array  $data
     * @param string $destination
     *
     * @return Entity\ContactData[]
     */
    protected function exportInlineContactData(array $data, $destination)
    {
        /** @var InlineContactData $parser */
        $parser = $this->getHelper('inline_contact_data');

        return $parser->parse($data, $destination);
    }
}
