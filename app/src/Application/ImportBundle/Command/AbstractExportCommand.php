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
use Application\ImportBundle\Generator\Exporter\GeneratorExporterInterface;
use Application\ImportBundle\Generator\GeneratorInterface;
use Monolog\Handler\StreamHandler;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Orb\Util\OptionsArray;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * Base export command
 *
 * Class AbstractExportCommand
 * @package Application\ImportBundle\Command
 */
abstract class AbstractExportCommand extends ContainerAwareCommand
{
    /**
     * Creates a new generator config instance
     *
     * @param InputInterface $input
     * @return GeneratorConfig
     */
    protected function createGeneratorConfig(InputInterface $input)
    {
        $config = new GeneratorConfig();
        $config
            ->addRecordType(GeneratorInterface::RECORD_TYPE_PEOPLE)
            ->addRecordType(GeneratorInterface::RECORD_TYPE_MESSAGES);

        $this->setParamsByDeskProConfig($config);
        $this->setParamsByInputInterface($config, $input);

        return $config;
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
            ->setMode($import_config->get('mode', GeneratorConfig::MODE_TEST))
            ->setMarkDone($import_config->get('mark_done', true));
    }

    /**
     * Use CLI to set up generator config params
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
            throw new \Exception('Source type argument is not defined');
        }

        if ($input->hasOption('output-path')) {
            $config->setOutputPath(rtrim($input->getOption('output-path'), "\\/") . "/");
        }

        if ($input->hasOption('input-path')) {
            $config->setInputPath($input->getOption('input-path'));
        } else {
            if ($config->getExporterType() === GeneratorExporterInterface::GENERATOR_TYPE_CSV) {
                throw new \Exception('You must supply an "input-path" argument while using CSV exporter');
            }
        }

        if ($input->hasOption('log-path')) {
            $config->setLogPath($input->getOption('log-path'));
        }
        if ($input->hasOption('mode')) {
            $config->setMode($input->getOption('mode'));
        }
        if ($input->hasOption('live')) {
            $config->setMode(GeneratorConfig::MODE_LIVE);
        }
        if ($input->hasOption('mark-done')) {
            $config->setMarkDone($input->getOption('mark-done'));
        }
        if ($input->hasOption('verbose')) {
            $config->setVerbose($input->getOption('verbose'));
        }
    }

    /**
     * Create a logger
     *
     * @param GeneratorConfig $config
     * @param ConsoleHandler  $consoleHandler
     *
     * @return LoggerInterface
     */
    protected function createLogger(GeneratorConfig $config, ConsoleHandler $consoleHandler)
    {
        $logger = new Logger('exporter');
        if ($config->getLogPath()) {
            $logger->pushHandler(new StreamHandler($config->getLogPath()));
        }
        if ($config->isVerbose()) {
            $logger->pushHandler($consoleHandler);
        }

        return $logger;
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
