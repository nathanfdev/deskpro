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

use Application\DeskPRO\App;
use Application\DeskPRO\EntityRepository;
use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator;
use Application\ImportBundle\Generator\Exporter\ExporterInterface;
use Application\ImportBundle\Generator\GeneratorConfig;
use Application\ImportBundle\Importer\Importer;
use Application\ImportBundle\Logger\ImporterProcessingHandler;
use Application\ImportBundle\Reader\Csv\CsvConfig;
use Application\ImportBundle\Reader\DeskPRO\DeskPROReaderFactory;
use Application\ImportBundle\Reader\Json\JsonConfig;
use Application\ImportBundle\Reader\OsTicket\OsTicketReaderFactory;
use Application\ImportBundle\Reader\ZenDesk\ZenDeskReaderFactory;
use DeskPRO\Kernel\KernelErrorHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryUsageProcessor;
use Orb\Util\Env;
use Orb\Util\OptionsArray;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Bridge\Monolog\Formatter\ConsoleFormatter;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Process\Process;

/**
 * Base export command.
 *
 * Class AbstractExportCommand
 */
abstract class AbstractExportCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addArgument(
                'script',
                InputArgument::OPTIONAL,
                'The target script to use'
            )
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
            )
            ->addOption(
                'batch',
                'b',
                InputOption::VALUE_NONE,
                'Runs only the next batch'
            )
            ->addOption(
                'memory-usage',
                'm',
                InputOption::VALUE_NONE,
                'Shows memory usage'
            )
            ->addOption(
                'config-from-db',
                'c',
                InputOption::VALUE_NONE,
                'Whether to load config from DB'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('config-from-db')) {
            /** @var Importer $is */
            $is = $this->getContainer()->get('deskpro.import');
            $input->setArgument('script', $is->getCurrentName());
        }

        $allowed = array(
            ExporterInterface::TYPE_CSV,
            ExporterInterface::TYPE_JSON,
            ExporterInterface::TYPE_OS_TICKET,
            ExporterInterface::TYPE_ZENDESK,
            ExporterInterface::TYPE_DESKPRO,
        );

        if (!in_array($input->getArgument('script'), $allowed)) {
            throw new RuntimeException(sprintf(
                'Unknown source type `%s`, expected: (%s)',
                $input->getArgument('script'),
                implode(', ', $allowed)
            ));
        }

        $GLOBALS['DP_IS_IMPORTING'] = true;
        $GLOBALS['DP_NOSQL_LOG']    = true;

        @ini_set('memory_limit', -1);
        @set_time_limit(0);

        $em = App::getOrm();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        if ($input->getOption('batch')) {
            $pid = dp_get_data_dir().'/importer.pid';
            $fh  = @fopen($pid, 'a');

            if (!$fh) {
                throw new \RuntimeException(sprintf('Unable to create lock file: %s', $pid));
            }
            if (!@flock($fh, LOCK_EX | LOCK_NB)) {
                $output->writeln('Another instance is running...');

                return 0;
            }

            $exit_code = $this->executeBatchRun($input, $output);

            @flock($fh, LOCK_UN);
            @fclose($fh);
        } else {
            $exit_code = $this->executeUnattendedRun($input, $output);
        }

        if ($this->getContainer()->getSetting('elastica.enabled')) {
            $command = $this->getApplication()->find('fos:elastica:populate');
            $input   = new ArrayInput(array(''));
            $output  = new NullOutput();
            $command->run($input, $output);
        }

        unset($GLOBALS['DP_IS_IMPORTING']);
        $GLOBALS['DP_NOSQL_LOG'] = false;

        return $exit_code;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function executeUnattendedRun(InputInterface $input, OutputInterface $output)
    {
        $out = $this->checkPhpInfo();
        if ($out !== true) {
            $output->write('<error>Could not find path to PHP (Detected PHP appears different than running PHP)</error>');
            $output->write('<error>Specify path to PHP in config.php by setting the $DP_CONFIG[\'php_path\'] option.</error>');

            return 1;
        }

        $out = $this->checkRequirements();
        if ($out !== true) {
            $output->write('<error>PHP sub-command binary fails server checks: '.$out.'</error>');
            $output->write('<error>Check your config.php file to make sure $DP_CONFIG[\'php_path\'] is set to the correct PHP path.</error>');

            return 1;
        }

        $arguments = array_map(function ($argument) { return escapeshellarg($argument); }, $_SERVER['argv']);
        $arguments[] = '-b';

        // todo always verbose mode by now
        // todo check for progress bar in unattended mode
        $arguments[] = '-vvv';

        if (defined('DPC_SITE_ID')) {
            $arguments[] = '--dpc-site-id '.DPC_SITE_ID;
        }

        $cmd = sprintf('%s %s', dp_get_php_path(), implode(' ', $arguments));

        do {
            $process = new Process($cmd, realpath(DP_ROOT.'/../'));
            $process->setTimeout(18000);
            $process->run(function ($type, $data) use ($output) {
                $output->write($data);
            });

            if (!$process->isSuccessful()) {
                $output->writeln('<error>Detected error, halting process</error>');

                return 1;
            }

            $output->writeln('<info>Done batch</info>');
            $output->writeln('<info>Updating search tables.</info>');

            /** @var EntityRepository\Ticket $ticket_repository */
            $ticket_repository = $this->getContainer()->getEm()->getRepository('DeskPRO:Ticket');
            $ticket_repository->fillSearchTable();

            $config          = $this->createGeneratorConfig($input);
            $exporter_config = $config->getExporterBatchConfig();

            if ($exporter_config instanceof Generator\Exporter\Parser\AbstractBatchConfig) {
                $rerun = $exporter_config->getHasRemaining();
                if ($rerun) {
                    $output->writeln('<info>Running next batch</info>');
                }
            } else {
                $rerun = false;
            }
        } while ($rerun);

        $output->writeln('<info>Done all.</info>');

        return 0;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function executeBatchRun(InputInterface $input, OutputInterface $output)
    {
        $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);

        try {
            $config = $this->createGeneratorConfig($input);
            $logger = $this->createLogger($config, $input, $output);

            if ($config->isSilent()) {
                $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
            }
            if ($config->getRetryWaitTimeout()) {
                $logger->warning(sprintf('Retry timeout, %d seconds left', $config->getRetryWaitTimeout()));
            } else {
                $this->doExecute($config, $logger, $input, $output);
            }

            return 0;
        } catch (\Exception $e) {
            KernelErrorHandler::logException($e, true);
            $output->writeln($e->getMessage());

            if (isset($logger)) {
                $logger->critical($e);
            }
            if (isset($config)) {
                $output->writeln(sprintf(
                    'An error has occurred while %s. Look at the log file `%s` to see details.',

                    strtolower($config->getGenerationType()),
                    $config->getLogPath()
                ));
            }

            // Mark batch as successful even an error has occurred
            return 0;
        }
    }

    /**
     * Checks generator configuration.
     *
     * @param GeneratorConfig $config
     *
     * @throws RuntimeException
     */
    abstract protected function checkConfiguration(GeneratorConfig $config);

    /**
     * Executes command.
     *
     * @param GeneratorConfig $config
     * @param LoggerInterface $logger
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    abstract protected function doExecute(GeneratorConfig $config, LoggerInterface $logger, InputInterface $input, OutputInterface $output);

    /**
     * Creates a new generator config instance
     * The export is executing in the order of the entity type collection.
     *
     * @param InputInterface $input
     *
     * @throws RuntimeException
     *
     * @return GeneratorConfig
     */
    protected function createGeneratorConfig(InputInterface $input)
    {
        $config = new GeneratorConfig();

        $this->setParamsByDeskProConfig($config);
        $this->setParamsByInputInterface($config, $input);
        $this->setBatchConfigByInputInterface($config, $input);

        $this->checkConfiguration($config);

        return $config;
    }

    /**
     * Use project config to set up generator config params.
     *
     * @param GeneratorConfig $config
     */
    protected function setParamsByDeskProConfig(GeneratorConfig $config)
    {
        $import_config = new OptionsArray(dp_get_config('import', array()));
        $config
            ->setOutputPath($import_config->get('output_path'))
            ->setLogPath($import_config->get('log_path', dp_get_log_dir().'/export.log'))
        ;
    }

    /**
     * Use cli to set generator config params up.
     *
     * @param GeneratorConfig $config
     * @param InputInterface  $input
     *
     * @throws \Exception
     */
    protected function setParamsByInputInterface(GeneratorConfig $config, InputInterface $input)
    {
        if ($input->hasArgument('script')) {
            $config->setExporterType($input->getArgument('script'));
        } else {
            throw new RuntimeException('Source type argument is not defined');
        }

        $readerConfig = null;

        if ($input->getOption('config-from-db')) {
            /** @var Importer $is */
            $is         = $this->getContainer()->get('deskpro.import');
            $importer   = $is->getImporter($input->getArgument('script'));
            $configData = $importer->getData('config');
            if (!@$configData['temp']) {
                throw new \Exception('Importer directory is not defined');
            }
            $input->setOption('input-path', $configData['temp'].'/in');
            $input->setOption('output-path', $configData['temp'].'/out/');

            $readerConfig = $is->getReaderConfig($input->getArgument('script'));
            $logfile      = $importer->getData('logfile');

            if ($logfile) {
                $config->setLogPath($logfile);
            }
        }

        if ($input->hasOption('output-path') && $input->getOption('output-path')) {
            $config->setOutputPath(rtrim($input->getOption('output-path'), '\\/').'/');
        }

        if (!$readerConfig) {
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
                case ExporterInterface::TYPE_DESKPRO:
                    $readerConfig = DeskPROReaderFactory::getDefaultConfig();
                    break;
                default:
                    throw new \RuntimeException('No reader config defined');
            }
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
     * Use cli to set batch config up.
     *
     * @param GeneratorConfig $config
     * @param InputInterface  $input
     *
     * @throws RuntimeException
     */
    protected function setBatchConfigByInputInterface(GeneratorConfig $config, InputInterface $input)
    {
        $batch_config_file = null;
        $config->setExporterBatchConfig(null);

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
     * Create a logger.
     *
     * @param GeneratorConfig $config
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return LoggerInterface
     */
    protected function createLogger(GeneratorConfig $config, InputInterface $input, OutputInterface $output)
    {
        $logger = new Logger('exporter');

        $formatter = new LineFormatter();
        $formatter->ignoreEmptyContextAndExtra(true);
        $formatter->allowInlineLineBreaks(true);

        if ($config->getLogPath()) {
            $handler = new StreamHandler($config->getLogPath());
            $handler->setFormatter($formatter);

            $logger->pushHandler($handler);

            if ($input->getOption('config-from-db')) {
                $importer = $this->getContainer()->get('deskpro.import')->getImporter($input->getArgument('script'));
                $handler  = new ImporterProcessingHandler($importer, $this->getContainer()->getEm());
                $handler->setFormatter($formatter);
                $logger->pushHandler($handler);
            }
        }

        $handler = new StreamHandler(dp_get_log_dir().'/export_perm.log');
        $handler->setFormatter($formatter);

        $logger->pushHandler($handler);

        if ($config->isConsoleOutputEnabled()) {
            $formatter = new ConsoleFormatter();
            $formatter->ignoreEmptyContextAndExtra(true);
            $formatter->allowInlineLineBreaks(true);

            $handler = new ConsoleHandler($output);
            $handler->setFormatter($formatter);

            $logger->pushHandler($handler);
        }
        if ($input->getOption('memory-usage')) {
            $logger->pushProcessor(new MemoryUsageProcessor());
        }

        return $logger;
    }

    /**
     * Create a generator.
     *
     * @param GeneratorConfig $config
     * @param LoggerInterface $logger
     *
     * @return Generator\Generator
     */
    protected function createGenerator(GeneratorConfig $config, LoggerInterface $logger)
    {
        /** @var Generator\Generator $generator */
        $generator = Generator\GeneratorFactory::createGenerator($this->getContainer(), $config);
        $generator->setLogger($logger);

        return $generator;
    }

    /**
     * Create a progress bar if verbose mode is disabled.
     *
     * @param Generator\Generator $generator
     * @param OutputInterface     $output
     *
     * @return ProgressBar|null
     */
    protected function createAndSetProgressBar(Generator\Generator $generator, InputInterface $input, OutputInterface $output)
    {
        if (!$generator->getConfig()->isProgressbarEnabled()) {
            return;
        }

        $total_count = $generator->getTotalRecordsCount();
        $total_count = $generator->getConfig()->hasWriter() ? $total_count * 3 : $total_count * 2;

        $progress_bar = $input->getOption('config-from-db')
            ? $this->getContainer()->get('deskpro.import')->createProgressBar($total_count)
            : new ProgressBar($output, $total_count);

        $progress_bar->start();

        $generator->setProgressBarHelper($progress_bar);

        return $progress_bar;
    }

    /**
     * Override container to set correct type hinting.
     *
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    protected function getContainer()
    {
        return parent::getContainer();
    }

    /**
     * Check PHP info.
     *
     * @return bool
     */
    protected function checkPhpInfo()
    {
        if (defined('DPC_SITE_ID')) {
            return true;
        }

        if (dp_is_php_path_guessed()) {
            $cmd = sprintf('%s %s', dp_get_php_path(), escapeshellarg('bin/phpinfo.php'));

            $process = new Process($cmd, realpath(DP_ROOT));
            $process->run();

            return $process->isSuccessful() && Env::isSamePhpInfo(Env::getPhpInfo(), $process->getOutput());
        }

        return true;
    }

    /**
     * Make sure we have passes requirements.
     *
     * @return bool|string
     */
    protected function checkRequirements()
    {
        if (defined('DPC_SITE_ID')) {
            return true;
        }

        $cmd = sprintf('%s %s', dp_get_php_path(), escapeshellarg('bin/check-req.php'));

        $process = new Process($cmd, realpath(DP_ROOT));
        $process->run();

        $output = $process->getOutput();
        if (!$process->isSuccessful() || strpos($output, 'OKAY') === false) {
            return $output;
        }

        return true;
    }
}
