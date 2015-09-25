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

use Application\ImportBundle\Generator\Exporter\Formatter\FormatterInterface;
use Application\ImportBundle\Generator\Exporter\Parser\ParserHelperSet;
use Application\ImportBundle\Reader\ReaderConfigInterface;
use Application\ImportBundle\Reader\ZenDesk\ZenDeskConfig;
use Application\ImportBundle\Reader\ZenDesk\ZenDeskReaderFactoryInterface;
use Guzzle\Http\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ZenDesk data exporter factory.
 *
 * Class ZenDeskFactory
 */
class ZenDeskFactory extends AbstractFactory
{
    /**
     * {@inheritdoc}
     */
    public static function createExporter(ContainerInterface $container, ReaderConfigInterface $config)
    {
        if (!$config instanceof ZenDeskConfig) {
            throw new \RuntimeException('Config expected to be instance of ZenDeskConfig');
        }

        $http_client = new Client();

        /** @var ZenDeskReaderFactoryInterface $reader_factory */
        $reader_factory = $container->get('deskpro.import.zendesk_reader_factory');
        /** @var FormatterInterface $formatter */
        $formatter = $container->get('deskpro.import.formatter');

        $reader  = $reader_factory->createReader($config);
        $storage = new Parser\PeopleStorage();

        $helpers = new ParserHelperSet();
        $helpers
            ->attach(new Parser\ZenDesk\Helper\Attachment($formatter, $http_client))
            ->attach(new Parser\ZenDesk\Helper\Translations($formatter))
        ;

        $ticket_people  = new Parser\ZenDesk\TicketPeopleStorage($reader, $storage);
        $article_people = new Parser\ZenDesk\ArticlePeopleStorage($reader, $storage);

        // Parsers collection
        $parsers = new Parser\Collection();
        $parsers
            ->attach(new Parser\ZenDesk\Downloads($reader, $formatter, $helpers))
            ->attach(new Parser\ZenDesk\Feedback($reader, $formatter, $helpers))
            ->attach(new Parser\ZenDesk\Articles($reader, $formatter, $helpers, $article_people))
            ->attach(new Parser\ZenDesk\ArticleCategories($reader, $formatter, $helpers))
            ->attach(new Parser\ZenDesk\News($reader, $formatter, $helpers))
            ->attach(new Parser\ZenDesk\People($reader, $formatter, $helpers, $storage))
            ->attach(new Parser\ZenDesk\Tickets($reader, $formatter, $helpers, $ticket_people))
            ->attach(new Parser\ZenDesk\Organizations($reader, $formatter, $helpers))
        ;

        return new ZenDesk($parsers, $reader);
    }
}
