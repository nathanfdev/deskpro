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
 * DeskPRO
 *
 * @package DeskPRO
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
        $this->setDefinition(array(
            new InputArgument('action', InputArgument::REQUIRED, 'info, install, upgrade, sync or reset'),
            new InputArgument('classname', InputArgument::OPTIONAL, 'Specify the specific classname to run'),
        ))->setName('dp:default-data');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action    = strtolower($input->getArgument('action'));
        $classname = $input->getArgument('classname') ?: null;

        $logger          = new Logger('defaultdata');
        $console_handler = new ConsoleHandler($output);
        $logger->pushHandler($console_handler);

        $output->setVerbosity(4);
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
                $array = array();
                foreach ($data_proc->getDataClasses() as $classname) {
                    $array[] = array(Util::getBaseClassname($classname), $data_proc->isInstalled($classname) ? "Yes" : "No");
                }

                echo Strings::asciiTable($array, array("Data Class", "Is Installed"));
                break;
            default:
                $output->writeln("<error>Invalid action. Please use: info, install, upgrade, sync or reset</error>");

                return 1;
                break;
        }

        echo "\n";

        return 0;
    }
}
