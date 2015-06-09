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

use Application\ImportBundle\Generator\GeneratorConfig;
use Application\ImportBundle\Generator\Exporter\ExporterInterface;
use Application\ImportBundle\Generator;
use Application\ImportBundle\Entity;
use Application\ImportBundle\Reader\Csv\CsvConfig;
use Application\ImportBundle\Reader\Json\JsonConfig;
use Application\ImportBundle\Reader\OsTicket\OsTicketReaderFactory;
use Application\ImportBundle\Reader\ZenDesk\ZenDeskReaderFactory;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Bridge\Monolog\Formatter\ConsoleFormatter;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Orb\Util\OptionsArray;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Base export command
 *
 * Class AbstractExportCommand
 * @package Application\ImportBundle\Command
 */
abstract class AbstractExportCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addArgument('script', InputArgument::REQUIRED, 'The target script to use')
            ->addOption(
                'input-path',
                null,
                InputOption::VALUE_REQUIRED,
                'The path to the directory where the exporting files are present'
            )
            ->addOption(
                'output-path',
                null,
                InputOption::VALUE_REQUIRED,
                'The path to the directory where the files should be exported'
            )
            ->addOption(
                'batch-config',
                null,
                InputOption::VALUE_REQUIRED,
                'The path to the directory where the exporter batch config is located'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'A writer does not flush data'
            )
            ->addOption(
                'silent',
                null,
                InputOption::VALUE_NONE,
                'No progressbar'
            );

    }

    /**
     * Creates a new generator config instance
     * The export is executing in the order of the entity type collection
     *
     * @param InputInterface $input
     * @param array          $supported_types
     *
     * @return GeneratorConfig
     * @throws RuntimeException
     */
    protected function createGeneratorConfig(InputInterface $input, array $supported_types)
    {
        $config = new GeneratorConfig();

        $this->setParamsByDeskProConfig($config);
        $this->setParamsByInputInterface($config, $input);
        $this->setBatchConfigByInputInterface($config, $input);

        foreach ($supported_types as $type) {
            $config->addEntityType($type);
        }

        return $config;
    }

    /**
     * Returns a list of supported entity types
     *
     * @return string[]
     */
    protected function getSupportedEntityTypes()
    {
        return array(
            Entity\EntityInterface::TYPE_TICKET,
            Entity\EntityInterface::TYPE_PERSON,
            Entity\EntityInterface::TYPE_ARTICLE,
            Entity\EntityInterface::TYPE_DOWNLOAD,
            Entity\EntityInterface::TYPE_FEEDBACK,
            Entity\EntityInterface::TYPE_NEWS,
        );
    }

    /**
     * Use project config to set up generator config params
     *
     * @param GeneratorConfig $config
     */
    protected function setParamsByDeskProConfig(GeneratorConfig $config)
    {
        $import_config = new OptionsArray(dp_get_config('import', array()));
        $config
            ->setOutputPath($import_config->get('output_path'))
            ->setLogPath($import_config->get('log_path', dp_get_log_dir() . '/export.log'));
    }

    /**
     * Use cli to set generator config params up
     *
     * @param GeneratorConfig $config
     * @param InputInterface  $input
     *
     * @throws RuntimeException
     */
    protected function setParamsByInputInterface(GeneratorConfig $config, InputInterface $input)
    {
        if ($input->hasArgument('script')) {
            $config->setExporterType($input->getArgument('script'));
        } else {
            throw new RuntimeException('Source type argument is not defined');
        }

        if ($input->hasOption('output-path') && $input->getOption('output-path')) {
            $config->setOutputPath(rtrim($input->getOption('output-path'), "\\/") . "/");
        }

        switch ($config->getExporterType()) {
            case ExporterInterface::TYPE_CSV:
                $readerConfig = new CsvConfig($input->getOption('input-path'));
                break;
            case ExporterInterface::TYPE_JSON:
                $readerConfig = new JsonConfig($input->getOption('input-path'));
                break;
            case ExporterInterface::TYPE_ZENDESK:
                $readerConfig = ZenDeskReaderFactory::getZenDeskConfig();
                break;
            case ExporterInterface::TYPE_OS_TICKET:
                $readerConfig = OsTicketReaderFactory::getDefaultConfig();
                break;
            default:
                throw new RuntimeException(sprintf(
                    'Unknown source type `%s`, expected: (%s)',

                    $config->getExporterType(),
                    implode(', ', array(
                        ExporterInterface::TYPE_CSV,
                        ExporterInterface::TYPE_JSON,
                        ExporterInterface::TYPE_OS_TICKET,
                        ExporterInterface::TYPE_ZENDESK,
                    ))
                ));
        }

        $config->setReaderConfig($readerConfig);
        // back compatibility
        $config->setInputPath($input->getOption('input-path'));

        if ($input->hasOption('log-path')) {
            $config->setLogPath($input->getOption('log-path'));
        }
        if ($input->hasOption('verbose')) {
            $config->setVerbose($input->getOption('verbose'));
        }
        if ($input->hasOption('dry-run')) {
            $config->setDryRun($input->getOption('dry-run'));
        }
        if ($input->hasOption('silent')) {
            $config->setSilent($input->getOption('silent'));
        }
    }

    /**
     * Use cli to set batch config up
     *
     * @param GeneratorConfig $config
     * @param InputInterface  $input
     *
     * @throws RuntimeException
     */
    protected function setBatchConfigByInputInterface(GeneratorConfig $config, InputInterface $input)
    {
        $batch_config_file = null;

        // Tries to get batch.json from output or input path
        if ($config->getBatchFilePath()) {
            if (@file_exists($config->getBatchFilePath()) === true) {
                $batch_config_file = $config->getBatchFilePath();
            }
        }

        // Tries to get custom batch.json from "batch-config" option
        if ($input->hasOption('batch-config')) {
            if ($input->getOption('batch-config')) {
                $batch_config_file = $input->getOption('batch-config');
            }
        }

        if ($batch_config_file) {
            /** @var Generator\Exporter\Batch $batch_exporter */
            $batch_exporter = $this->getContainer()->get('deskpro.import.batch_exporter');
            if (file_exists($batch_config_file) === false) {
                throw new FileNotFoundException(sprintf('Batch config `%s` not found', $batch_config_file));
            }

            $batch_config = @file_get_contents($batch_config_file);
            $batch_config = @json_decode($batch_config, true);

            if (is_array($batch_config) === false) {
                throw new IOException(sprintf('Invalid batch config `%s`', $batch_config_file));
            }

            $config->setExporterBatchConfig($batch_exporter->parse($batch_config));
        }
    }

    /**
     * Create a logger
     *
     * @param GeneratorConfig $config
     * @param OutputInterface $output
     *
     * @return LoggerInterface
     */
    protected function createLogger(GeneratorConfig $config, OutputInterface $output)
    {
        $logger = new Logger('exporter');

        if ($config->getLogPath()) {
            $formatter = new LineFormatter();
            $formatter->ignoreEmptyContextAndExtra(true);

            $handler = new StreamHandler($config->getLogPath());
            $handler->setFormatter($formatter);

            $logger->pushHandler($handler);
        }
        if ($config->isConsoleOutputEnabled()) {
            $formatter = new ConsoleFormatter();
            $formatter->ignoreEmptyContextAndExtra(true);

            $handler = new ConsoleHandler($output);
            $handler->setFormatter($formatter);

            $logger->pushHandler($handler);
        }

        return $logger;
    }

    /**
     * Create a generator
     *
     * @param GeneratorConfig $config
     * @param LoggerInterface $logger
     *
     * @return Generator\Generator
     */
    protected function createGenerator(GeneratorConfig $config, LoggerInterface $logger)
    {
        /** @var Generator\Generator $generator */
        $this->getContainer()->set('deskpro.import.config', $config);

        /** @var Generator\Generator $generator */
        $generator = $this->getContainer()->get('deskpro.import.generator');
        $generator->setLogger($logger);

        return $generator;
    }

    /**
     * Create a progress bar if verbose mode is disabled
     *
     * @param Generator\Generator $generator
     * @param OutputInterface     $output
     *
     * @return ProgressBar|null
     */
    protected function createAndSetProgressBar(Generator\Generator $generator, OutputInterface $output)
    {
        if ($generator->getConfig()->isProgressbarEnabled()) {
            $total_count = $generator->getTotalRecordsCount();
            $total_count = $generator->getConfig()->hasWriter() ? $total_count * 3 : $total_count * 2;

            $progress_bar = new ProgressBar($output, $total_count);
            $progress_bar->start();

            $generator->setProgressBarHelper($progress_bar);
            return $progress_bar;
        }

        return null;
    }

    /**
     * Override container to set correct type hinting
     *
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    protected function getContainer()
    {
        return parent::getContainer();
    }
}
