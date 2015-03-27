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

namespace Application\ImportBundle\Generator;

use Application\ImportBundle\Generator\Exporter\LazyExporter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Importer generator factory
 *
 * Class GeneratorFactory
 * @package Application\ImportBundle\Generator
 */
class GeneratorFactory
{
    /**
     * Create a generator
     *
     * @param ContainerInterface $container
     * @return GeneratorInterface
     */
    public static function createGenerator(ContainerInterface $container)
    {
        return new Generator(
            self::createExportersCollection($container),
            self::createValidatorsCollection($container),
            self::createWritersCollection($container)
        );
    }

    /**
     * Create a json writer
     *
     * @param ContainerInterface $container
     * @return Writer\Collection
     */
    private static function createWritersCollection(ContainerInterface $container)
    {
        $jsonFactory   =  new Writer\Json\JsonWriterFactory($container);
        $deskProFactory = new Writer\DeskPro\DeskProWriterFactory($container);

        $writers = new Writer\Collection();
        $writers
            ->attach($jsonFactory->createWriter())
            ->attach($deskProFactory->createWriter());

        return $writers;
    }

    /**
     * Returns a collection of exporters
     *
     * @param ContainerInterface $container
     * @return Exporter\Collection
     */
    private static function createExportersCollection(ContainerInterface $container)
    {
        $exporters = new Exporter\Collection();
        $exporters
            ->attach(new LazyExporter(new Exporter\CsvFactory($container)))
            ->attach(new LazyExporter(new Exporter\JsonFactory($container)))
            ->attach(new LazyExporter(new Exporter\OsTicketFactory($container)))
            ->attach(new LazyExporter(new Exporter\ZenDeskFactory($container)));

        return $exporters;
    }

    /**
     * Returns a collection of validators
     *
     * @param ContainerInterface $container
     * @return Validator\Collection
     */
    private static function createValidatorsCollection(ContainerInterface $container)
    {
        /** @var \Symfony\Component\Validator\Validator $validator */
        $validator  = $container->get('validator');

        $validators = new Validator\Collection();
        $validators
            ->attach(new Validator\Download($validator))
            ->attach(new Validator\Feedback($validator))
            ->attach(new Validator\Articles($validator))
            ->attach(new Validator\News($validator))
            ->attach(new Validator\Person($validator))
            ->attach(new Validator\Ticket($validator));

        return $validators;
    }
}
