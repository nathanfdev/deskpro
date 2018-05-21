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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DefaultDataCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setDefinition([
            new InputArgument('action', InputArgument::REQUIRED, 'info, install, upgrade, sync or reset'),
            new InputArgument('classname', InputArgument::OPTIONAL, 'Specify the specific classname to run'),
        ])
            ->addOption('flag', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_REQUIRED, 'Set properties in name:value')
            ->setName('dp:default-data');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action    = strtolower($input->getArgument('action'));
        $classname = $input->getArgument('classname') ?: null;

        $logger = new Logger('defaultdata');
        $logger->pushHandler(new ConsoleHandler($output));

        $opts = [
            'via' => self::class,
        ];

        if ($input->getOption('flag')) {
            foreach ($input->getOption('flag') as $f) {
                $f = explode(':', $f, 2);
                if (!isset($f[1])) {
                    $f[1] = true;
                }

                if ($f[1] === '1' || $f[1] === '0' || $f[1] === 1 || $f[1] === 0) {
                    $f[1] = (bool) $f[1];
                }

                $opts[$f[0]] = $f[1];
            }
        }

        $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
        $dataProcessor = new DefaultDataProcessor($this->getContainer());
        $dataProcessor->setExtraOptions($opts);
        $dataProcessor->setLogger($logger);

        switch ($action) {
            case 'install':
                $dataProcessor->runInstall($classname);
                break;
            case 'upgrade':
                $dataProcessor->runSync($classname);
                break;
            case 'sync':
                $dataProcessor->runSync($classname);
                break;
            case 'reset':
                $dataProcessor->runReset($classname);
                break;
            case 'info':
                $array = [];
                foreach ($dataProcessor->getDataClasses() as $classname) {
                    $array[] = [Util::getBaseClassname($classname), $dataProcessor->isInstalled($classname) ? 'Yes' : 'No'];
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
