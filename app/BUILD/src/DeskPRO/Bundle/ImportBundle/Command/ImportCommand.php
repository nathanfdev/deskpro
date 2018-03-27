<?php

namespace DeskPRO\Bundle\ImportBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ImportCommand.
 */
class ImportCommand extends AbstractImporterCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('dp:import')
            ->setHelp('Gets data from the external source.')
            ->addArgument('file', InputArgument::OPTIONAL, 'Exporter script name')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute(InputInterface $input, OutputInterface $output)
    {
        $container      = $this->getContainer();
        $sourceResolver = $container->get('dp.importer.source_script_resolver');

        // resolve importer config
        $job = $this->getJob($input);
        if ($job) {
            // get script config from a job data
            $jobData = $job->getData();
            if (!isset($jobData['type']) || !isset($jobData['options']) || !is_array($jobData['options'])) {
                throw new \RuntimeException('Unable to process the provided job.');
            }

            $filename = $jobData['type'];
            $config   = $jobData['options'];
        } else {
            $filename = $input->getArgument('file');

            // resolve default filepath to get script options from config.php
            $baseConfigFilePath    = $container->get('deskpro.app_env')->getDpRoot().'/config/importer/'.$filename.'.php';
            $modulesConfigFilePath = $sourceResolver->getConfigPath($filename);

            if (file_exists($baseConfigFilePath)) {
                require_once $baseConfigFilePath;
            } elseif (file_exists($modulesConfigFilePath)) {
                require_once $modulesConfigFilePath;
            } else {
                throw new \RuntimeException(sprintf(
                    "Unable to locate the config file. Checked paths: \n%s",
                    implode("\n", [$baseConfigFilePath, $modulesConfigFilePath])
                ));
            }

            if (!isset($CONFIG)) {
                throw new \RuntimeException('Unable to read config file.');
            }

            $config = $CONFIG;
        }

        // run import
        $sourceScript = $sourceResolver->getSourceScript($filename, $config);
        $sourceScript->runImport();

        $container->get('dp.importer.source.helper.progress')->finishImport();
        $container->get('dp.importer.logger.job_progress')->flushLog();
        $container->get('dp.importer.logger.storage_handler')->flushLog();
    }
}
