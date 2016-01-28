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
use Application\ImportBundle\Generator\Exporter\Formatter\FormatterInterface;
use Application\ImportBundle\Generator\Exporter\Parser\ParserHelperSet;
use Application\ImportBundle\Reader\Csv\CsvConfig;
use Application\ImportBundle\Reader\Csv\CsvReaderException;
use Application\ImportBundle\Reader\Csv\CsvReaderInterface;
use Application\ImportBundle\Reader\NotFoundException;

/**
 * Abstract csv parser.
 *
 * Class AbstractCsv
 */
abstract class AbstractParser extends \Application\ImportBundle\Generator\Exporter\Parser\AbstractParser
{
    /**
     * @var CsvReaderInterface
     */
    protected $reader;

    /**
     * @var FormatterInterface
     */
    protected $formatter;

    /**
     * Constructor.
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
     * Returns rows count of csv file.
     *
     * @param string $entity_type
     *
     * @return int
     */
    protected function getReaderCount($entity_type)
    {
        /** @var CsvConfig $config */
        $config = $this->reader->getConfig();

        try {
            return $this->reader->getRowsCount($entity_type);
        } catch (NotFoundException $e) {
            $this->logInfo(sprintf('Resource `%s/%s` not found (Skipping)', $config->getPath(), $entity_type));
        }

        return 0;
    }

    /**
     * Returns a collection of exporting data.
     *
     * @param string $entity_type
     *
     * @return array
     */
    protected function getReaderData($entity_type)
    {
        /** @var CsvConfig $config */
        $config = $this->reader->getConfig();

        try {
            $data = $this->reader->getData($entity_type);
            if (count($data) === 0) {
                $this->logWarning(sprintf('No records found in resource `%s/%s`', $config->getPath(), $entity_type));
            }

            return $data;
        } catch (NotFoundException $e) {
            $this->logInfo(sprintf('Resource `%s/%s` not found (Skipping)', $config->getPath(), $entity_type));
        } catch (CsvReaderException $e) {
            $this->logWarning(sprintf(
                'Csv reader throws an exception while reading `%s/%s`. Reason: %s',
                $config->getPath(), $entity_type, $e->getMessage()
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
        return $this->helpers->get($this, 'multiple_'.Entity\EntityInterface::TYPE_CONTACT_DATA);
    }

    /**
     * @return Helper\ContactData\Inline\InlineContactData
     */
    protected function getInlineContactDataParser()
    {
        return $this->helpers->get($this, 'inline_'.Entity\EntityInterface::TYPE_CONTACT_DATA);
    }

    /**
     * @return Helper\CustomFields\MultipleCustomFields
     */
    protected function getMultipleCustomFieldsParser()
    {
        return $this->helpers->get($this, 'multiple_'.Entity\EntityInterface::TYPE_CUSTOM_FIELD);
    }

    /**
     * @return Helper\CustomFields\InlineCustomFields
     */
    protected function getInlineCustomFieldsParser()
    {
        return $this->helpers->get($this, 'inline_'.Entity\EntityInterface::TYPE_CUSTOM_FIELD);
    }
}
