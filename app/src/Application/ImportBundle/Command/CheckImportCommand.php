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

use Application\ImportBundle\ImporterFactory;
use Monolog\Logger;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CheckImportCommand
 * @package Application\ImportBundle\Command
 */
class CheckImportCommand extends AbstractExportCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('dp:check:import');
        $this->setHelp("This goes through a dry-run of the import process. You will only see output if there are errors. Use -v to see verbose output.");
        $this->addOption('data-path', null, InputOption::VALUE_REQUIRED, 'The path to the data directory containing your JSON files');
        $this->addOption('log-path', null, InputOption::VALUE_REQUIRED, 'A base path to write log data to. Defaults to a file in the default log directory.');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $factory = new ImporterFactory($this->getContainer(), $input);

        try {
            $config       = $factory->createImporterConfig();
            $config->mode = 'test';
        } catch (\InvalidArgumentException $e) {
            $output->writeln("<error>Config Error</error>");
            $output->writeln("Message: " . $e->getMessage());
            $output->writeln("");
            $output->writeln("Run this command with --help to see options. You can also define configuration in your config.php file under the 'import' section.");
            $output->writeln("");

            return 1;
        }

        $config->log_path = null;

        $logger = new Logger('importer', array(new ConsoleHandler($output)));

        $importer = $factory->createImporter($config, $logger);
        $importer->processImports();

        echo "\n";

        return 0;
    }
}
