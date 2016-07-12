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

use Application\ImportBundle\Reader\OsTicket\OsTicketReaderInterface;
use Application\ImportBundle\Reader\ReaderInterface;

/**
 * OsTicket data exporter factory.
 *
 * Class OsTicketFactory
 */
class OsTicketFactory extends AbstractExporterFactory
{
    /**
     * {@inheritdoc}
     */
    public function createExporter(ReaderInterface $reader)
    {
        if (!$reader instanceof OsTicketReaderInterface) {
            throw new \RuntimeException('Config expected to be instance of OsTicketReaderInterface');
        }

        $formatter = $this->container->get('deskpro.import.formatter');

        $people_storage = new Parser\PeopleStorage();
        $ticket_people  = new Parser\OsTicket\Storage\TicketPeopleStorage($reader, $people_storage);

        $parsers = new Parser\Collection();
        $parsers
            ->attach(new Parser\OsTicket\Downloads($reader, $formatter))
            ->attach(new Parser\OsTicket\Feedback($reader, $formatter))
            ->attach(new Parser\OsTicket\FeedbackCustomDef($reader, $formatter))
            ->attach(new Parser\OsTicket\Articles($reader, $formatter))
            ->attach(new Parser\OsTicket\ArticleCategories($reader, $formatter))
            ->attach(new Parser\OsTicket\ArticleCustomDef($reader, $formatter))
            ->attach(new Parser\OsTicket\News($reader, $formatter))
            ->attach(new Parser\OsTicket\People($reader, $formatter, $people_storage))
            ->attach(new Parser\OsTicket\PeopleCustomDef($reader, $formatter))
            ->attach(new Parser\OsTicket\Tickets($reader, $formatter, $ticket_people))
            ->attach(new Parser\OsTicket\TicketCustomDef($reader, $formatter))
            ->attach(new Parser\OsTicket\Organizations($reader, $formatter))
            ->attach(new Parser\OsTicket\OrganizationCustomDef($reader, $formatter))
        ;

        return new OsTicket($parsers, $reader);
    }
}
