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

use Application\DeskPRO\App;
use Application\DeskPRO\EntityRepository;
use Application\ImportBundle\Importer\ImporterContext;
use Application\ImportBundle\Model\BatchConfig;
use DpSys\LowError\SystemErrorHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\MemoryUsageProcessor;
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
use Symfony\Component\Process\Process;

/**
 * Import command.
 * Read and parse an external data and import it to database.
 *
 * Class AbstractExportCommand
 */
class ApplyCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('import:apply')
            ->setHelp('Executes the importer.')
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
        $GLOBALS['DP_IS_IMPORTING'] = true;
        $GLOBALS['DP_NOSQL_LOG']    = true;

        @ini_set('memory_limit', -1);
        @set_time_limit(0);

        $em = App::getOrm();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        if ($input->getOption('batch')) {
            $pid = App::$container->getParameter('dp.user.tmp_dir').'/importer.pid';
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
            $input   = new ArrayInput(['']);
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
        $arguments = array_map(function ($argument) { return escapeshellarg($argument); }, $_SERVER['argv']);
        $arguments[] = '-b';

        // todo always verbose mode by now
        // todo check for progress bar in unattended mode
        $arguments[] = '-vvv';

        if (defined('DPC_SITE_ID')) {
            $arguments[] = '--dpc-site-id '.DPC_SITE_ID;
        }

        $appEnv = $this->getContainer()->get('deskpro.app_env');

        $cmd = sprintf('%s %s', dp_get_php_path(), implode(' ', $arguments));

        do {
            $process = new Process($cmd, realpath($appEnv->getDpRoot()));
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

            $config          = $this->createGeneratorContext($input);
            $exporter_config = $config->getBatchConfig();

            if ($exporter_config) {
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
            $context = $this->createGeneratorContext($input);
            $logger  = $this->setLoggerHandlers($context, $input, $output);

            if ($context->isSilent()) {
                $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
            }

            $generator = $this->getContainer()->get('dp.importer');
            $generator->generate($context);

            $output->writeln('');
            $output->writeln(sprintf(
                'Done. Import was successful. Look at the log file `%s` to see details.',
                $context->getLogPath()
            ));

            return 0;
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e, true);
            $output->writeln($e->getMessage());

            if (isset($logger)) {
                $logger->critical($e);
            }
            if (isset($context)) {
                $output->writeln(sprintf(
                    'An error has occurred while import. Look at the log file `%s` to see details.',
                    $context->getLogPath()
                ));
            }

            // Mark batch as successful even an error has occurred
            return 0;
        }
    }

    /**
     * Creates a new generator config instance
     * The export is executing in the order of the entity type collection.
     *
     * @param InputInterface $input
     *
     * @throws RuntimeException
     *
     * @return ImporterContext
     */
    protected function createGeneratorContext(InputInterface $input)
    {
        $config = new ImporterContext();

        $import_config = new OptionsArray($this->getContainer()->get('deskpro.app_env')->getConfig('import', []));
        $config->setLogPath($import_config->get('log_path', dp_get_log_dir().'/export.log'));

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

        if (!$config->getInputPath()) {
            throw new RuntimeException('Input path must be specified');
        }

        $batch_config_file = null;
        $config->setBatchConfig(null);

        // Tries to get batch.json from output or input path
        if ($config->getBatchFilePath()) {
            if (@file_exists($config->getBatchFilePath())) {
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
            if (!file_exists($batch_config_file)) {
                throw new FileNotFoundException(sprintf('Batch config `%s` not found', $batch_config_file));
            }

            $serializer = $this->getContainer()->get('serializer');
            $data       = file_get_contents($batch_config_file);

            $config->setBatchConfig($serializer->deserialize($data, BatchConfig::class, 'json'));
        }

        return $config;
    }

    /**
     * Create a logger.
     *
     * @param ImporterContext $config
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return LoggerInterface
     */
    protected function setLoggerHandlers(ImporterContext $config, InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('dp.importer_logger');

        $formatter = new LineFormatter();
        $formatter->ignoreEmptyContextAndExtra(true);
        $formatter->allowInlineLineBreaks(true);

        if ($config->getLogPath()) {
            $handler = new StreamHandler($config->getLogPath());
            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);
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
     * Override container to set correct type hinting.
     *
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    protected function getContainer()
    {
        return parent::getContainer();
    }
}
