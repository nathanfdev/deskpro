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
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Orb\Util\OptionsArray;
use Psr\Log\LoggerInterface;
use Exception;

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
        $this->addArgument('script', InputArgument::REQUIRED, 'The target script to use');
        $this->addOption(
            'input-path',
            null,
            InputOption::VALUE_REQUIRED,
            'The path to the directory where the exporting files are present'
        );
        $this->addOption(
            'dry-run',
            null,
            InputOption::VALUE_NONE,
            'A writer does not flush data'
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
     * @throws Exception
     */
    protected function createGeneratorConfig(InputInterface $input, array $supported_types)
    {
        $config = new GeneratorConfig();

        $this->setParamsByDeskProConfig($config);
        $this->setParamsByInputInterface($config, $input);

        foreach ($supported_types as $type) {
            $config->addEntityType($type);
        }

        return $config;
    }

    /**
     * Returns a collection of supported entity types that should be exported in this order
     *
     * @return array
     */
    protected function exportEntityTypesQueue()
    {
        return array(
//            Entity\EntityInterface::TYPE_TICKET,
//            Entity\EntityInterface::TYPE_PERSON,
//            Entity\EntityInterface::TYPE_ARTICLE,
            Entity\EntityInterface::TYPE_DOWNLOAD,
//            Entity\EntityInterface::TYPE_FEEDBACK,
//            Entity\EntityInterface::TYPE_NEWS,
        );
    }

    /**
     * Returns a collection of supported entity types that should be imported in this order
     *
     * @return array
     */
    protected function importEntityTypesQueue()
    {
        return array(
//            Entity\EntityInterface::TYPE_PERSON,
//            Entity\EntityInterface::TYPE_TICKET,
//            Entity\EntityInterface::TYPE_ARTICLE,
            Entity\EntityInterface::TYPE_DOWNLOAD,
//            Entity\EntityInterface::TYPE_FEEDBACK,
//            Entity\EntityInterface::TYPE_NEWS,
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
            ->setLogPath($import_config->get('log_path', dp_get_log_dir() . '/export.log'))
            ->setMarkDone($import_config->get('mark_done', true));
    }

    /**
     * Use cli to set up generator config params
     *
     * @param GeneratorConfig $config
     * @param InputInterface  $input
     *
     * @throws Exception
     */
    protected function setParamsByInputInterface(GeneratorConfig $config, InputInterface $input)
    {
        if ($input->hasArgument('script')) {
            $config->setExporterType($input->getArgument('script'));
        } else {
            throw new Exception('Source type argument is not defined');
        }

        if ($input->hasOption('output-path') && $input->getOption('output-path')) {
            $config->setOutputPath(rtrim($input->getOption('output-path'), "\\/") . "/");
        }

        if ($input->hasOption('input-path')) {
            if ($input->getOption('input-path')) {
                $config->setInputPath($input->getOption('input-path'));
            } else {
                switch ($config->getExporterType()) {
                    case ExporterInterface::TYPE_CSV:
                        throw new Exception('You must supply an "input-path" argument while using CSV exporter');
                    case ExporterInterface::TYPE_JSON:
                        throw new Exception('You must supply an "input-path" argument while using JSON exporter');
                }
            }
        }

        if ($input->hasOption('log-path')) {
            $config->setLogPath($input->getOption('log-path'));
        }
        if ($input->hasOption('mark-done')) {
            $config->setMarkDone($input->getOption('mark-done'));
        }
        if ($input->hasOption('verbose')) {
            $config->setVerbose($input->getOption('verbose'));
        }
        if ($input->hasOption('dry-run')) {
            $config->setDryRun($input->getOption('dry-run'));
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
            $logger->pushHandler(new StreamHandler($config->getLogPath()));
        }
        if ($config->isVerbose()) {
            $console_handler = new ConsoleHandler($output);
            $logger->pushHandler($console_handler);
        }

        return $logger;
    }

    /**
     * Create a generator
     *
     * @param GeneratorConfig $config
     * @param OutputInterface $output
     * @param LoggerInterface $logger
     *
     * @return Generator\Generator
     */
    protected function createGenerator(GeneratorConfig $config, OutputInterface $output, LoggerInterface $logger)
    {
        /** @var Generator\Generator $generator */
        $generator = $this->getContainer()->get('deskpro.import.generator');
        $generator
            ->setConfig($config)
            ->setLogger($logger);

        if ($config->isVerbose() === false) {
            $progress_bar = new ProgressBar($output, $generator->getTotalRecordsCount());
            $progress_bar->start();

            $generator->setProgressBarHelper($progress_bar);
        }

        return $generator;
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
