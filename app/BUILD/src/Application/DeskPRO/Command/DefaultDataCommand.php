<?php

/**
 * DeskPRO.
 *
 * @category Commands
 */

namespace Application\DeskPRO\Command;

use Application\InstallBundle\Data\DefaultDataProcessor;
use Monolog\Logger;
use Orb\Util\Strings;
use Orb\Util\Util;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DefaultDataCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setDefinition([
            new InputArgument('action', InputArgument::REQUIRED, 'info, install, upgrade, sync or reset'),
            new InputArgument('classname', InputArgument::OPTIONAL, 'Specify the specific classname to run'),
        ])->setName('dp:default-data');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action    = strtolower($input->getArgument('action'));
        $classname = $input->getArgument('classname') ?: null;

        $logger          = new Logger('defaultdata');
        $console_handler = new ConsoleHandler($output);
        $logger->pushHandler($console_handler);

        $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
        $data_proc = new DefaultDataProcessor($this->getContainer());
        $data_proc->setLogger($logger);

        switch ($action) {
            case 'install':
                $data_proc->runInstall($classname);
                break;
            case 'upgrade':
                $data_proc->runSync($classname);
                break;
            case 'sync':
                $data_proc->runSync($classname);
                break;
            case 'reset':
                $data_proc->runReset($classname);
                break;
            case 'info':
                $array = [];
                foreach ($data_proc->getDataClasses() as $classname) {
                    $array[] = [Util::getBaseClassname($classname), $data_proc->isInstalled($classname) ? 'Yes' : 'No'];
                }

                echo Strings::asciiTable($array, ['Data Class', 'Is Installed']);
                break;
            default:
                $output->writeln('<error>Invalid action. Please use: info, install, upgrade, sync or reset</error>');

                return 1;
                break;
        }

        echo "\n";

        return 0;
    }
}
