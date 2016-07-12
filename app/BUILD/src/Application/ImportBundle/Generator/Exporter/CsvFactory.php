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

namespace Application\ImportBundle\Generator\Exporter;

use Application\ImportBundle\Generator\Exporter\Parser\ParserHelperSet;
use Application\ImportBundle\Reader\Csv\CsvReaderInterface;
use Application\ImportBundle\Reader\ReaderInterface;

/**
 * Csv data exporter factory.
 *
 * Class CsvFactory
 */
class CsvFactory extends AbstractExporterFactory
{
    /**
     * {@inheritdoc}
     */
    public function createExporter(ReaderInterface $reader)
    {
        if (!$reader instanceof CsvReaderInterface) {
            throw new \RuntimeException('Reader expected to be instance of CsvReaderInterface');
        }

        $formatter = $this->container->get('deskpro.import.formatter');

        $helpers = new ParserHelperSet();
        $helpers
            ->attach(new Parser\Csv\Helper\ContactData\MultipleContactData($formatter))
            ->attach(Parser\Csv\Helper\ContactData\Inline\InlineContactDataFactory::create())
            ->attach(new Parser\Csv\Helper\CustomFields\MultipleCustomFields($formatter))
            ->attach(new Parser\Csv\Helper\CustomFields\InlineCustomFields())
            ->attach(new Parser\Csv\Helper\Blob\Blob($formatter))
            ->attach(new Parser\Csv\Helper\Blob\Attachment($formatter))
        ;

        $parsers = new Parser\Collection();
        $parsers
            ->attach(new Parser\Csv\Downloads($reader, $formatter, $helpers))
            ->attach(new Parser\Csv\Feedback($reader, $formatter, $helpers))
            ->attach(new Parser\Csv\FeedbackCustomDef($reader, $formatter, $helpers))
            ->attach(new Parser\Csv\Articles($reader, $formatter, $helpers))
            ->attach(new Parser\Csv\ArticleCategories($reader, $formatter, $helpers))
            ->attach(new Parser\Csv\ArticleCustomDef($reader, $formatter, $helpers))
            ->attach(new Parser\Csv\News($reader, $formatter, $helpers))
            ->attach(new Parser\Csv\People($reader, $formatter, $helpers))
            ->attach(new Parser\Csv\PeopleCustomDef($reader, $formatter, $helpers))
            ->attach(new Parser\Csv\Tickets($reader, $formatter, $helpers))
            ->attach(new Parser\Csv\TicketCustomDef($reader, $formatter, $helpers))
            ->attach(new Parser\Csv\Organizations($reader, $formatter, $helpers))
            ->attach(new Parser\Csv\OrganizationCustomDef($reader, $formatter, $helpers))
        ;

        return new Csv($parsers, $reader);
    }
}
