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

use Application\ImportBundle\Generator\GeneratorConfig;
use Application\ImportBundle\Generator\GeneratorException;
use Application\ImportBundle\Generator\Validator\ValidatorException;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Monolog\Logger;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Application\ImportBundle\Generator\Generator;

/**
 * Export command
 * Read and parse an external data and save it locally
 *
 * Class ExportCommand
 * @package Application\ImportBundle\Command
 */
class ExportCommand extends AbstractExportCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('dp:export:run');
        $this->setHelp('The actual export process');
        $this->addOption(
            'output-path',
            null,
            InputOption::VALUE_REQUIRED,
            'The path to the directory where the files should be exported'
        );

        parent::configure();
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->createGeneratorConfig($input);
        $logger = $this->createLogger($config, new ConsoleHandler($output));

        /** @var ProgressHelper $progress_bar */
        $progress_bar = $this->getHelperSet()->get('progress');
        /** @var Generator $generator */
        $generator = $this->getContainer()->get('deskpro.import.generator');
        $generator
            ->setConfig($config)
            ->setLogger($logger);

        if (!$config->isVerbose()) {
            $generator->setProgressBarHelper($progress_bar);
        }

        $progress_bar->start($output, $generator->getTotalRecordsCount());

        try {
            $generator->generate();
            $output->writeln('Done.');

        } catch (GeneratorException $e) {
            $output->writeln('');
            foreach ($e->getExceptions() as $exception) {
                /** @var ValidatorException $exception */
                $logger->critical($exception);
            }
            if ($config->isVerbose() === false) {
                $output->writeln(sprintf(
                    "An error has occurred while exporting. Look at the log file `%s` to see details.",
                    $config->getLogPath()
                ));
            }
        }
    }
}
