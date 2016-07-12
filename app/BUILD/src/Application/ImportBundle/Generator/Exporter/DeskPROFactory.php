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
use Application\ImportBundle\Reader\DeskPRO\DeskPROConfig;
use Application\ImportBundle\Reader\DeskPRO\DeskPROReader;
use Application\ImportBundle\Reader\ReaderInterface;

/**
 * DeskPRO data exporter factory.
 *
 * Class DeskPROFactory
 */
class DeskPROFactory extends AbstractExporterFactory
{
    /**
     * {@inheritdoc}
     */
    public function createExporter(ReaderInterface $reader)
    {
        if (!$reader instanceof DeskPROReader) {
            throw new \RuntimeException('Config expected to be instance of DeskPROReader');
        }

        /** @var DeskPROConfig $config */
        $config = $reader->getConfig();

        $people_storage = new Parser\PeopleStorage();
        $ticket_people  = new Parser\DeskPRO\Storage\TicketPeopleStorage($reader, $people_storage);

        $helpers = new ParserHelperSet();
        $helpers->attach(new Parser\DeskPRO\Helper\CustomFields());

        $parsers = new Parser\Collection();
        $parsers
            ->attach(new Parser\DeskPRO\People($reader, $helpers, $people_storage))
            ->attach(new Parser\DeskPRO\PeopleCustomDef($reader, $helpers))
            ->attach(new Parser\DeskPRO\Tickets($reader, $helpers, $ticket_people, $config->getStartTicketId()))
            ->attach(new Parser\DeskPRO\TicketCustomDef($reader, $helpers))
            ->attach(new Parser\DeskPRO\Articles($reader, $helpers))
            ->attach(new Parser\DeskPRO\ArticleCategories($reader, $helpers))
            ->attach(new Parser\DeskPRO\ArticleCustomDef($reader, $helpers))
            ->attach(new Parser\DeskPRO\Downloads($reader, $helpers))
            ->attach(new Parser\DeskPRO\Feedback($reader, $helpers))
            ->attach(new Parser\DeskPRO\FeedbackCustomDef($reader, $helpers))
            ->attach(new Parser\DeskPRO\News($reader, $helpers))
            ->attach(new Parser\DeskPRO\Organizations($reader, $helpers))
            ->attach(new Parser\DeskPRO\OrganizationCustomDef($reader, $helpers))
        ;

        return new DeskPRO($parsers, $reader);
    }
}
