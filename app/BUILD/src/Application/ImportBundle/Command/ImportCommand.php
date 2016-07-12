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

namespace Application\ImportBundle\Command;

use Application\ImportBundle\Generator;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Import command
 * Read and parse an external data and import it to database.
 *
 * Class ImportCommand
 */
class ImportCommand extends AbstractGenerateCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dp:import:run');
        $this->setHelp('Executes the importer.');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute(Generator\GeneratorConfig $config, LoggerInterface $logger, InputInterface $input, OutputInterface $output)
    {
        $config->setWriterType(Generator\Writer\WriterInterface::TYPE_DESK_PRO);
        $generator = $this->createGenerator($config, $logger);

        $this->createAndSetProgressBar($generator, $input, $output);
        $this->generate($generator, $output);
    }

    /**
     * {@inheritdoc}
     */
    protected function checkConfiguration(Generator\GeneratorConfig $config)
    {
        if ($config->needInputPath() && !$config->getInputPath()) {
            throw new RuntimeException('Input path must be specified');
        }
        if ($config->isBatchExporter() && !$config->getOutputPath() && !$config->getInputPath()) {
            switch ($config->getExporterType()) {
                case Generator\Exporter\ExporterInterface::TYPE_ZENDESK:
                case Generator\Exporter\ExporterInterface::TYPE_OS_TICKET:
                case Generator\Exporter\ExporterInterface::TYPE_DESKPRO:
                    throw new RuntimeException(sprintf(
                        'Output path must be specified for batch exporter `%s`',
                        $config->getExporterType()
                    ));
                case Generator\Exporter\ExporterInterface::TYPE_JSON:
                case Generator\Exporter\ExporterInterface::TYPE_CSV:
                    throw new RuntimeException(sprintf(
                        'Input path must be specified for batch exporter `%s`',
                        $config->getExporterType()
                    ));
            }
        }
    }
}
