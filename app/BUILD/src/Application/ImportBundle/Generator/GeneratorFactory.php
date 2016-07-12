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

namespace Application\ImportBundle\Generator;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\ImportBundle\Entity\EntityInterface;
use Application\ImportBundle\Generator\Exporter\ExporterFactoryInterface;
use Application\ImportBundle\Generator\Exporter\ExporterInterface;
use Application\ImportBundle\Generator\Writer\WriterFactoryInterface;
use Application\ImportBundle\Generator\Writer\WriterInterface;
use Application\ImportBundle\Reader\ReaderFactoryInterface;
use Application\ImportBundle\Reader\ReaderInterface;

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
     * @param GeneratorConfig  $config
     *
     * @return Generator
     */
    public static function createGenerator(DeskproContainer $container, GeneratorConfig $config)
    {
        $exporter = self::createExporter($container, $config);
        $writer   = self::createWriter($container, $config);

        $symfony_validator = $container->get('validator');
        $validators        = new Validator\Collection();
        $validators
            ->attach(new Validator\ConstraintValidator($symfony_validator, EntityInterface::TYPE_DOWNLOAD))
            ->attach(new Validator\ConstraintValidator($symfony_validator, EntityInterface::TYPE_FEEDBACK))
            ->attach(new Validator\ConstraintValidator($symfony_validator, EntityInterface::TYPE_ARTICLE))
            ->attach(new Validator\ConstraintValidator($symfony_validator, EntityInterface::TYPE_ARTICLE_CATEGORY))
            ->attach(new Validator\ConstraintValidator($symfony_validator, EntityInterface::TYPE_NEWS))
            ->attach(new Validator\ConstraintValidator($symfony_validator, EntityInterface::TYPE_PERSON))
            ->attach(new Validator\ConstraintValidator($symfony_validator, EntityInterface::TYPE_PERSON_CUSTOM_DEF))
            ->attach(new Validator\ConstraintValidator($symfony_validator, EntityInterface::TYPE_TICKET))
            ->attach(new Validator\ConstraintValidator($symfony_validator, EntityInterface::TYPE_TICKET_CUSTOM_DEF))
            ->attach(new Validator\ConstraintValidator($symfony_validator, EntityInterface::TYPE_ORGANIZATION))
            ->attach(new Validator\ConstraintValidator($symfony_validator, EntityInterface::TYPE_ORGANIZATION_CUSTOM_DEF))
        ;

        /** @var \Application\ImportBundle\Importer\Importer $import_service */
        $import_service = $container->get('deskpro.import');

        return new Generator($exporter, $validators, $config, $import_service, $writer);
    }

    /**
     * Creates generator reader instance.
     *
     * @param DeskproContainer $container
     * @param GeneratorConfig  $config
     *
     * @return ReaderInterface
     */
    public static function createReader(DeskproContainer $container, GeneratorConfig $config)
    {
        /** @var ReaderFactoryInterface $factory */
        $factory = $container->get(sprintf('deskpro.import.%s_reader_factory', $config->getExporterType()));

        return $factory->createReader($config->getReaderConfig());
    }

    /**
     * Creates generator exporter instance.
     *
     * @param DeskproContainer $container
     * @param GeneratorConfig  $config
     *
     * @throws \RuntimeException
     *
     * @return ExporterInterface
     */
    private static function createExporter(DeskproContainer $container, GeneratorConfig $config)
    {
        $factories = [
            ExporterInterface::TYPE_CSV       => 'Application\ImportBundle\Generator\Exporter\CsvFactory',
            ExporterInterface::TYPE_JSON      => 'Application\ImportBundle\Generator\Exporter\JsonFactory',
            ExporterInterface::TYPE_OS_TICKET => 'Application\ImportBundle\Generator\Exporter\OsTicketFactory',
            ExporterInterface::TYPE_ZENDESK   => 'Application\ImportBundle\Generator\Exporter\ZenDeskFactory',
            ExporterInterface::TYPE_DESKPRO   => 'Application\ImportBundle\Generator\Exporter\DeskPROFactory',
        ];

        if (!isset($factories[$config->getExporterType()])) {
            throw new \RuntimeException(sprintf('Invalid exporter type `%s`', $config->getExporterType()));
        }

        /** @var ExporterFactoryInterface $factory */
        $factory  = new $factories[$config->getExporterType()]($container);
        $exporter = $factory->createExporter(self::createReader($container, $config));

        if ($exporter instanceof Exporter\ExporterBatchInterface) {
            if (!$config->getExporterBatchConfig()) {
                $config->setExporterBatchConfig($exporter->getDefaultBatchConfig());
            }
        }

        return $exporter;
    }

    /**
     * Creates generator writer instance.
     *
     * @param DeskproContainer $container
     * @param GeneratorConfig  $config
     *
     * @throws \RuntimeException
     *
     * @return WriterInterface
     */
    private static function createWriter(DeskproContainer $container, GeneratorConfig $config)
    {
        $factories = [
            WriterInterface::TYPE_DESK_PRO => 'Application\ImportBundle\Generator\Writer\DeskPRO\DeskProWriterFactory',
            WriterInterface::TYPE_JSON     => 'Application\ImportBundle\Generator\Writer\Json\JsonWriterFactory',
        ];

        if (!$config->getWriterType()) {
            return;
        }
        if (!isset($factories[$config->getWriterType()])) {
            throw new \RuntimeException(sprintf('Invalid writer type `%s`', $config->getWriterType()));
        }

        /** @var WriterFactoryInterface $factory */
        $factory = new $factories[$config->getWriterType()]($container);
        $writer  = $factory->createWriter();

        return $writer;
    }
}
