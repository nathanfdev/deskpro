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
