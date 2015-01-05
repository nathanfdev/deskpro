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

/**
 * @package Importer
 */

namespace Application\ImportBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Monolog\Logger;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Application\ImportBundle\Generator\GeneratorFactory;

/**
 * Class CheckExportCommand
 * @package Application\ImportBundle\Command
 */
class CheckExportCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('dp:export:check');
        $this->setHelp('Performs a dry run of the export process');
        $this->addArgument('script', InputArgument::REQUIRED, 'The target script to use');
        $this->addOption('output-path', null, InputOption::VALUE_REQUIRED, 'The path to the directory where the files should be exported');
        $this->addOption('input-path', null, InputOption::VALUE_REQUIRED, 'The path to the directory where the CSV files are present');
    }

    /**
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    public function getContainer()
    {
        return parent::getContainer();
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $out_handler = new ConsoleHandler($output);
        $logger = new Logger('exporter', array($out_handler));

        if (strtolower($input->getArgument('script')) === 'csv' && !$input->getOption('input-path')) {
            $logger->err('You must supply an "input-path" argument while using CSV exporter');
        }

        /** @var ProgressHelper $progress_bar */
        $progress_bar = $this->getHelperSet()->get('progress');

        /** @var GeneratorFactory $factory */
        $factory = $this->getContainer()->get('deskpro.import.generator.factory');

        $generator_config = $factory->createGeneratorConfig($input);
        $generator_config->setProgressBarHelper($progress_bar);
        $generator_config->output	= $output;

        $generator = $factory->createGenerator($generator_config, $logger);

        $generator->generateJson();

        echo "\n";
        echo "Done";
        echo "\n";
    }
}
