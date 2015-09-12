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
    const FILE_ARTICLE_CATEGORIES         = 'article_categories.csv';
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
     * @return Helper\Blob\Blob
     */
    protected function getBlobParser()
    {
        return $this->helpers->get($this, Entity\EntityInterface::TYPE_BLOB);
    }

    /**
     * @return Helper\Blob\Attachment
     */
    protected function getAttachmentParser()
    {
        return $this->helpers->get($this, Entity\EntityInterface::TYPE_ATTACHMENT);
    }

    /**
     * @return Helper\ContactData\MultipleContactData
     */
    protected function getMultipleContactDataParser()
    {
        return $this->helpers->get($this, 'multiple_' . Entity\EntityInterface::TYPE_CONTACT_DATA);
    }

    /**
     * @return Helper\ContactData\Inline\InlineContactData
     */
    protected function getInlineContactDataParser()
    {
        return $this->helpers->get($this, 'inline_' . Entity\EntityInterface::TYPE_CONTACT_DATA);
    }

    /**
     * @return Helper\CustomFields\MultipleCustomFields
     */
    protected function getMultipleCustomFieldsParser()
    {
        return $this->helpers->get($this, 'multiple_' . Entity\EntityInterface::TYPE_CUSTOM_FIELD);
    }

    /**
     * @return Helper\CustomFields\InlineCustomFields
     */
    protected function getInlineCustomFieldsParser()
    {
        return $this->helpers->get($this, 'inline_' . Entity\EntityInterface::TYPE_CUSTOM_FIELD);
    }
}
