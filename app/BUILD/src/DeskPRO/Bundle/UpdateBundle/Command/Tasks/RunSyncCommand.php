<?php

namespace DeskPRO\Bundle\UpdateBundle\Command\Tasks;

use Application\DeskPRO\Monolog\Logger;
use Application\InstallBundle\Upgrade\Build\PostBuild;
use Application\InstallBundle\Upgrade\Build\PostBuildAlways;
use DeskPRO\Bundle\UpdateBundle\BuildTasks\BuildFactory;
use Monolog\Handler\StreamHandler;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunSyncCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dp:update:tasks:run-sync')->setAliases(['dp:update-db:sync'])
            ->setDescription('Run data sync routines. This is typically done after running all other update tasks.')
            ->addOption('fast', 't', InputOption::VALUE_NONE, 'Skip the sync routines that can take a long time. Only do this if the manifest says all builds dont require a sync, otherwise you can end up with stale data such as old templates.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        global $DP_ENV;

        $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);

        $logger = new Logger('upgrade');
        $logger->pushHandler(new ConsoleHandler($output));
        $logger->pushHandler(new StreamHandler($DP_ENV->getUserLogsDir().DIRECTORY_SEPARATOR.'upgrade.log'));

        $manifestReader = $this->getContainer()->get('dp.build_tasks.manifest_reader');
        $buildFactory   = new BuildFactory($manifestReader, $this->getContainer(), $logger);

        if (!$input->getOption('fast')) {
            $logger->info('Running PostBuild');
            $postBuild = $buildFactory->makeBuildClass(PostBuild::class);
            $postBuild->run();
            $logger->info('.. Done PostBuild');
        } else {
            $logger->info('Skipping PostBuild because of --fast flag');
        }

        $logger->info('Running PostBuildAlways');
        $postBuildALways = $buildFactory->makeBuildClass(PostBuildAlways::class);
        $postBuildALways->run();
        $logger->info('.. Done PostBuildAlways');

        return 0;
    }
}
