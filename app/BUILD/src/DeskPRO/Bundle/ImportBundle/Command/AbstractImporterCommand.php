<?php

namespace DeskPRO\Bundle\ImportBundle\Command;

use Application\DeskPRO\Entity\Job;
use Doctrine\DBAL\Exception\ConnectionException;
use Monolog\Processor\MemoryUsageProcessor;
use Symfony\Bridge\Monolog\Formatter\ConsoleFormatter;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractImporterCommand.
 */
abstract class AbstractImporterCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addOption(
                'job',
                'j',
                InputOption::VALUE_REQUIRED,
                'Specify job id for import via the admin interface'
            )
            ->addOption(
                'memory-usage',
                'm',
                InputOption::VALUE_NONE,
                'Shows memory usage'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkDbCredentials();
        $this->setLoggerHandlers($input, $output);
        $this->setBasePath($input);
        $this->setListeners($input);

        try {
            $this->doExecute($input, $output);
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    abstract protected function doExecute(InputInterface $input, OutputInterface $output);

    /**
     * @throws \Exception
     */
    protected function checkDbCredentials()
    {
        try {
            $this->getContainer()->get('doctrine.orm.entity_manager')->getConnection()->connect();
        } catch (ConnectionException $e) {
            throw new \Exception('Unable to connect to DeskPRO database. Check DeskPRO connection credentials.');
        }
    }

    /**
     * Configure the logger.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function setLoggerHandlers(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('dp.importer_logger');
        $logger->pushHandler($this->getContainer()->get('dp.importer.logger.storage_handler'));

        $job = $this->getJob($input);
        if ($job) {
            $handler = $this->getContainer()->get('dp.importer.logger.job_progress');
            $handler->setJobId($job->getId());
            $logger->pushHandler($handler);
        }
        if ($input->hasOption('verbose')) {
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
    }

    /**
     * @param InputInterface $input
     */
    protected function setBasePath(InputInterface $input)
    {
        $storageAdapter = $this->getContainer()->get('dp.importer.storage_adapter');
        if ($input->hasOption('input-path') && $input->getOption('input-path')) {
            $storageAdapter->setBasePath($input->getOption('input-path'));
        } else {
            // set base bath from job id option
            $job = $this->getJob($input);
            if ($job) {
                $storageAdapter->setBasePath($storageAdapter->getBasePath().'-'.$job->getId());
            }
        }
    }

    /**
     * @param InputInterface $input
     */
    protected function setListeners(InputInterface $input)
    {
        $job = $this->getJob($input);
        if ($job) {
            $this->getContainer()->get('dp.import.listener.job_progress')->setJob($job->getId());
        }
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
     * @return string
     */
    protected function getLogFilePath()
    {
        return $this->getContainer()->get('deskpro.app_env')->getUserLogsDir().'/importer.log';
    }

    /**
     * @param InputInterface $input
     *
     * @throws \RuntimeException
     *
     * @return Job|null
     */
    protected function getJob(InputInterface $input)
    {
        $jobId = $input->getOption('job');
        if ($jobId) {
            // get script config from a job data
            $job = $this->getContainer()->getEm()->getRepository(Job::class)->find($jobId);
            if (!$job) {
                throw new \RuntimeException("Job $jobId not found.");
            }

            return $job;
        }

        return;
    }
}
