<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Import command.
 * Read and parse an external data and import it to database.
 *
 * Class AbstractExportCommand
 */
class ApplyCommand extends AbstractImporterCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('import:apply')
            ->setHelp('Saves the imported data to the DeskPRO helpdesk.')
            ->addOption(
                'input-path',
                null,
                InputOption::VALUE_REQUIRED,
                'The path to the directory where the exporting files are present'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'A writer does not flush data'
            )
            ->addOption(
                'batch',
                'b',
                InputOption::VALUE_NONE,
                'Runs only the next batch'
            )
            ->addOption(
                'brand',
                null,
                InputOption::VALUE_REQUIRED,
                'Specify brand to import for multi-brand helpdesk'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkDbCredentials();

        $GLOBALS['DP_IS_IMPORTING'] = true;
        $GLOBALS['DP_NOSQL_LOG']    = true;

        @ini_set('memory_limit', -1);
        @set_time_limit(0);

        $em = App::getOrm();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        if ($input->getOption('batch')) {
            $pid = $this->getContainer()->get('deskpro.app_env')->getUserTmpDir().'/importer.pid';
            $fh  = @fopen($pid, 'a');

            if (!$fh) {
                throw new \RuntimeException(sprintf('Unable to create lock file: %s', $pid));
            }
            if (!@flock($fh, LOCK_EX | LOCK_NB)) {
                $output->writeln('Another instance is running...');

                return 0;
            }

            $exitCode = $this->executeBatchRun($input, $output);

            @flock($fh, LOCK_UN);
            @fclose($fh);
        } else {
            $exitCode = $this->executeUnattendedRun($input, $output);
        }

        unset($GLOBALS['DP_IS_IMPORTING']);
        $GLOBALS['DP_NOSQL_LOG'] = false;

        return $exitCode;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function executeUnattendedRun(InputInterface $input, OutputInterface $output)
    {
        $arguments = array_map(function ($argument) {
            return escapeshellarg($argument);
        }, $_SERVER['argv']);
        $arguments[] = '-b';

        // todo always verbose mode by now
        // todo check for progress bar in unattended mode
        $arguments[] = '-vvv';

        if (defined('DPC_SITE_ID')) {
            $arguments[] = '--dpc-site-id '.DPC_SITE_ID;
        }

        $appEnv = $this->getContainer()->get('deskpro.app_env');
        $cmd    = sprintf('%s %s', dp_get_php_path(), implode(' ', $arguments));

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

            $config          = $this->createGeneratorContext($input);
            $exporter_config = $config->getBatchConfig();

            if ($exporter_config) {
                $rerun = $exporter_config->hasRemaining();
                if ($rerun) {
                    $output->writeln('<info>Running next batch</info>');
                }
            } else {
                $rerun = false;
            }
        } while ($rerun);

        if ($this->getContainer()->getSetting('elastica.enabled')) {
            $output->writeln('<info>Update elasctic search.</info>');

            $command = $this->getApplication()->find('dp:elastica:populate');
            $input   = new ArrayInput(['']);
            $output  = new NullOutput();
            $command->run($input, $output);
        }

        $output->writeln('<info>Update ticket search tables.</info>');

        /** @var EntityRepository\Ticket $ticket_repository */
        $ticket_repository = $this->getContainer()->getEm()->getRepository('DeskPRO:Ticket');
        $ticket_repository->fillSearchTable();

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
        $logger = $this->getContainer()->get('dp.importer_logger');

        try {
            $importer = $this->getContainer()->get('dp.importer');
            $context  = $this->createGeneratorContext($input);
            $this->setLoggerHandlers($input, $output);

            $dataCollection = $importer->getImportData($context);

            // Writes batch config (even no entities to support "retry-after" timeout)
            // Writes batch config before validation to skip broken batches
            $nextBatchConfig = $importer->getNextBatchConfig($context);
            $importer->writeBatchConfig($context, $nextBatchConfig);

            $importer->validateData($dataCollection);
            $importer->writeData($dataCollection, $context->getBrand(), $input->getOption('dry-run', false));

            $output->writeln('');
            $output->writeln(sprintf(
                'Done. Import was successful. Look at the log file `%s` to see details.',
                $this->getLogFilePath()
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
                    $this->getLogFilePath()
                ));
            }

            // Mark batch as successful even an error has occurred
            return 0;
        }
    }

    /**
     * Creates the importer context.
     *
     * @param InputInterface $input
     *
     * @return ImporterContext
     */
    protected function createGeneratorContext(InputInterface $input)
    {
        $context = new ImporterContext();

        // set an input path
        if ($input->getOption('input-path')) {
            $context->setInputPath($input->getOption('input-path'));
        } else {
            $context->setInputPath($this->getImporterDefaultOutputPath());
        }

        // try to get batch.json from the input path
        if (file_exists($context->getBatchFilePath())) {
            $batchFilePath = $context->getBatchFilePath();

            $serializer = $this->getContainer()->get('serializer');
            $data       = file_get_contents($batchFilePath);

            $context->setBatchConfig($serializer->deserialize($data, BatchConfig::class, 'json'));
        }

        if ($input->getOption('brand')) {
            $context->setBrand($input->getOption('brand'));
        }

        return $context;
    }
}
