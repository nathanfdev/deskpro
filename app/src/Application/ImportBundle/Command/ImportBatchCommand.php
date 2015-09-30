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

namespace Application\ImportBundle\Command;

use Application\ImportBundle\Generator;
use Application\ImportBundle\Reader\Json\JsonConfig;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Importing batch command
 * Exports and imports data at the same time, imports data and saves json files.
 *
 * Class ImportBatchCommand
 */
class ImportBatchCommand extends AbstractGenerateCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dp:import:batch');
        $this->setHelp('Imports data and saves json files');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute(Generator\GeneratorConfig $config, LoggerInterface $logger, InputInterface $input, OutputInterface $output)
    {
        // Export data
        $config->setWriterType(Generator\Writer\WriterInterface::TYPE_JSON);

        $generator = $this->createGenerator($config, $logger);
        $this->createAndSetProgressBar($generator, $input, $output);
        $this->generate($generator, $output);

        // Import data
        $config
            ->setInputPath($config->getOutputPath())
            ->setOutputPath(null)
            ->setExporterType(Generator\Exporter\ExporterInterface::TYPE_JSON)
            ->setReaderConfig(new JsonConfig($config->getInputPath()))
            ->setWriterType(Generator\Writer\WriterInterface::TYPE_DESK_PRO)
        ;

        $this->setBatchConfigByInputInterface($config, $input);
        if (!$config->getExporterBatchConfig()) {
            $config->setExporterBatchConfig(new Generator\Exporter\Parser\Json\BatchConfig());
        }

        $generator = $this->createGenerator($config, $logger);
        $this->createAndSetProgressBar($generator, $input, $output);
        $this->generate($generator, $output);
    }

    /**
     * {@inheritdoc}
     */
    protected function checkConfiguration(Generator\GeneratorConfig $config)
    {
        if ($config->isDryRun() === false && !$config->getOutputPath()) {
            throw new RuntimeException('Output path must be specified');
        }
        if ($config->needInputPath()) {
            if (!$config->getInputPath()) {
                throw new RuntimeException('Input path must be specified');
            }
            if ($config->getInputPath() === $config->getOutputPath()) {
                throw new RuntimeException('Output path must be different from input path');
            }
        }
    }
}
