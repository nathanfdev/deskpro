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

namespace Application\ImportBundle\Command;

use Application\ImportBundle\Generator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use RuntimeException;

/**
 * Importing batch command
 * Exports and imports data at the same time, imports data and saves json files
 *
 * Class ImportBatchCommand
 * @package Application\ImportBundle\Command
 */
class ImportBatchCommand extends AbstractGenerateCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('dp:import:batch');
        $this->setHelp('Imports data and saves json files');

        parent::configure();
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);

        $config = $this->createGeneratorConfig($input, $this->getSupportedEntityTypes());
        $config->setWriterType(Generator\Writer\WriterInterface::TYPE_JSON);

        if ($config->isDryRun() === false && ! $config->getOutputPath()) {
            throw new RuntimeException('Output path must be specified');
        }
        if ($config->needInputPath()) {
            if ( ! $config->getInputPath()) {
                throw new RuntimeException('Input path must be specified');
            }
            if ($config->getInputPath() === $config->getOutputPath()) {
                throw new RuntimeException('Output path must be different from input path');
            }
        }
        if ($config->isSilent()) {
            $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        }

        $logger    = $this->createLogger($config, $output);
        $generator = $this->createGenerator($config, $logger);

        if ($config->getRetryWaitTimeout()) {
            $logger->warning(sprintf('Retry timeout, %d seconds left', $config->getRetryWaitTimeout()));

            return;
        }

        $progress_bar = $this->createAndSetProgressBar($generator, $output);

        // Export data
        $success = $this->generate($generator, $output, $logger);
        if ($success) {
            $config
                ->setInputPath($config->getOutputPath())
                ->setOutputPath(null)
                ->setExporterBatchConfig(null)
                ->setExporterType(Generator\Exporter\ExporterInterface::TYPE_JSON)
                ->setWriterType(Generator\Writer\WriterInterface::TYPE_DESK_PRO);

            $this->setBatchConfigByInputInterface($config, $input);

            if ($progress_bar) {
                $progress_bar->start();
            }

            // Import data
            $this->generate($generator, $output, $logger);
        }
    }
}
