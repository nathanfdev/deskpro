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

namespace Application\ImportBundle\Generator\Exporter;

use Application\ImportBundle\Generator\Exporter\Parser\ParserHelperSet;
use Application\ImportBundle\Reader\ReaderInterface;
use Application\ImportBundle\Reader\ZenDesk\ZenDeskReaderInterface;
use Guzzle\Http\Client;

/**
 * ZenDesk data exporter factory.
 *
 * Class ZenDeskFactory
 */
class ZenDeskFactory extends AbstractExporterFactory
{
    /**
     * {@inheritdoc}
     */
    public function createExporter(ReaderInterface $reader)
    {
        if (!$reader instanceof ZenDeskReaderInterface) {
            throw new \RuntimeException('Reader expected to be instance of ZenDeskReaderInterface');
        }

        $http_client = new Client();
        $formatter   = $this->container->get('deskpro.import.formatter');

        $helpers = new ParserHelperSet();
        $helpers
            ->attach(new Parser\ZenDesk\Helper\Attachment($formatter, $http_client))
            ->attach(new Parser\ZenDesk\Helper\Translations($formatter))
        ;

        $people_storage = new Parser\PeopleStorage();
        $ticket_people  = new Parser\ZenDesk\Storage\TicketPeopleStorage($reader, $people_storage);
        $article_people = new Parser\ZenDesk\Storage\ArticlePeopleStorage($reader, $people_storage);

        $parsers = new Parser\Collection();
        $parsers
            ->attach(new Parser\ZenDesk\Downloads($reader, $formatter, $helpers))
            ->attach(new Parser\ZenDesk\Feedback($reader, $formatter, $helpers))
            ->attach(new Parser\ZenDesk\FeedbackCustomDef($reader, $formatter, $helpers))
            ->attach(new Parser\ZenDesk\Articles($reader, $formatter, $helpers, $article_people))
            ->attach(new Parser\ZenDesk\ArticleCategories($reader, $formatter, $helpers))
            ->attach(new Parser\ZenDesk\ArticleCustomDef($reader, $formatter, $helpers))
            ->attach(new Parser\ZenDesk\News($reader, $formatter, $helpers))
            ->attach(new Parser\ZenDesk\People($reader, $formatter, $helpers, $people_storage))
            ->attach(new Parser\ZenDesk\PeopleCustomDef($reader, $formatter, $helpers))
            ->attach(new Parser\ZenDesk\Tickets($reader, $formatter, $helpers, $ticket_people))
            ->attach(new Parser\ZenDesk\TicketCustomDef($reader, $formatter, $helpers))
            ->attach(new Parser\ZenDesk\Organizations($reader, $formatter, $helpers))
            ->attach(new Parser\ZenDesk\OrganizationCustomDef($reader, $formatter, $helpers))
        ;

        return new ZenDesk($parsers, $reader);
    }
}
