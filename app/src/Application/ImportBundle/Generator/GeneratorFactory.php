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

namespace Application\ImportBundle\Generator;

use Application\DeskPRO\DependencyInjection\DeskproContainer;

/**
 * Generator importer service factory.
 *
 * Class GeneratorFactory
 */
class GeneratorFactory
{
    /**
     * Creates importer generator instance.
     *
     * @param DeskproContainer $container
     *
     * @return Generator
     */
    public static function createGenerator(DeskproContainer $container)
    {
        /** @var GeneratorConfig $config */
        $config   = $container->get('deskpro.import.config');
        $exporter = $config->getExporterFactory($container)->createExporter($container, $config->getReaderConfig());

        $writer_factory = $config->getWriterFactory($container);
        $writer         = $writer_factory ? $writer_factory->createWriter() : null;

        if ($exporter instanceof Exporter\ExporterBatchInterface) {
            if (!$config->getExporterBatchConfig()) {
                $config->setExporterBatchConfig($exporter->getDefaultBatchConfig());
            }
        }

        $symfony_validator = $container->get('validator');
        $validators        = new Validator\Collection();
        $validators
            ->attach(new Validator\Download($symfony_validator))
            ->attach(new Validator\Feedback($symfony_validator))
            ->attach(new Validator\Article($symfony_validator))
            ->attach(new Validator\ArticleCategory($symfony_validator))
            ->attach(new Validator\News($symfony_validator))
            ->attach(new Validator\Person($symfony_validator))
            ->attach(new Validator\Ticket($symfony_validator))
            ->attach(new Validator\Organization($symfony_validator))
        ;

        /** @var \Application\ImportBundle\Service\Import $import_service */
        $import_service = $container->get('deskpro.import');

        return new Generator($exporter, $validators, $config, $import_service, $writer);
    }
}
