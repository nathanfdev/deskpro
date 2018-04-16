<?php

namespace DeskPRO\Bundle\ImportBundle\Command;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\EntityRepository;
use DeskPRO\Bundle\ImportBundle\Event\ProgressEvent;
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
            ->setName('dp:import:apply')
            ->setHelp('Saves the imported data to the DeskPRO helpdesk.')
            ->addOption(
                'input-path',
                null,
                InputOption::VALUE_REQUIRED,
                'The path to the directory where the exporting files are present'
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
            ->addOption(
                'skip-re-index',
                null,
                InputOption::VALUE_NONE,
                'Skip re-index of Elasticsearch and ticket search tables.'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute(InputInterface $input, OutputInterface $output)
    {
        $GLOBALS['DP_IS_IMPORTING'] = true;
        $GLOBALS['DP_NOSQL_LOG']    = true;

        @ini_set('memory_limit', -1);
        @set_time_limit(0);

        $container = $this->getContainer();
        $container->getEm()->getConnection()->getConfiguration()->setSQLLogger(null);

        if ($input->getOption('batch')) {
            $exitCode = $this->executeBatchRun($input, $output);
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
        $arguments[] = '-vvv';

        $container  = $this->getContainer();
        $appEnv     = $container->get('deskpro.app_env');
        $importer   = $container->get('dp.importer');
        $dispatcher = $container->get('dp.importer.event_dispatcher');
        $cmd        = sprintf('%s %s', $appEnv->getConfig('paths.php_path'), implode(' ', $arguments));

        $currentModelClass = null;

        do {
            // dispatch begin of import for each model type
            $batchConfig = $importer->readBatchConfig();
            $pointer     = $importer->getBatchPointer($batchConfig);

            if ($pointer && (!$currentModelClass || $currentModelClass !== $pointer->getModelClass())) {
                $dispatcher->dispatch(ProgressEvent::PRE_APPLY, new ProgressEvent($pointer->getModelClass()));
                $currentModelClass = $pointer->getModelClass();
            }

            // process batch import
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

            // dispatch end of import for each model type
            $batchConfig = $importer->readBatchConfig();
            $pointer     = $importer->getBatchPointer($batchConfig);

            if (!$pointer || $currentModelClass !== $pointer->getModelClass()) {
                $dispatcher->dispatch(ProgressEvent::POST_APPLY, new ProgressEvent($currentModelClass));
            }

            // check if we should continue the import process
            $rerun = $importer->hasRemaining($batchConfig);
            if ($rerun) {
                $output->writeln('<info>Running next batch</info>');
            }
        } while ($rerun);

        if (!$input->getOption('skip-re-index')) {
            if ($container->getSetting('elastica.enabled')) {
                $output->writeln('<info>Update elasctic search.</info>');

                $command = $this->getApplication()->find('dp:elastica:populate');
                $input   = new ArrayInput(['']);
                $output  = new NullOutput();
                $command->run($input, $output);
            }

            // finishing import, update search tables
            $output->writeln('<info>Update ticket search tables.</info>');

            /** @var EntityRepository\Ticket $ticketRepository */
            $ticketRepository = $container->getEm()->getRepository(Ticket::class);
            $ticketRepository->fillSearchTable();
        }

        $dispatcher->dispatch(ProgressEvent::FINISH, new ProgressEvent());
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

        $container  = $this->getContainer();
        $dispatcher = $container->get('dp.importer.event_dispatcher');
        $logger     = $container->get('dp.importer_logger');
        $importer   = $container->get('dp.importer');

        try {
            $batchConfig = $importer->readBatchConfig();
            $pointer     = $importer->getBatchPointer($batchConfig);
            if (!$pointer) {
                return 0;
            }

            $dispatcher->dispatch(ProgressEvent::PRE_BATCH_APPLY, new ProgressEvent($pointer->getModelClass()));
            $data = $importer->getImportData($pointer);
            $importer->writeData($data, $input->getOption('brand'));
            $dispatcher->dispatch(ProgressEvent::POST_BATCH_APPLY, new ProgressEvent($pointer->getModelClass(), [
                'count' => count($data),
            ]));

            $output->writeln('');
            $output->writeln('Done. Import was successful.');

            return 0;
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e, true);
            $output->writeln($e->getMessage());

            if (isset($logger)) {
                $logger->critical($e);
            }

            // mark batch as successful even an error has occurred
            return 0;
        } finally {
            if (isset($batchConfig) && isset($pointer)) {
                // update batch config even there was an error to skip broken batches
                $importer->updateBatchConfig($batchConfig, $pointer);
            }

            $container->get('dp.importer.logger.job_progress')->flushLog();
            $container->get('dp.importer.logger.storage_handler')->flushLog();
        }
    }
}
