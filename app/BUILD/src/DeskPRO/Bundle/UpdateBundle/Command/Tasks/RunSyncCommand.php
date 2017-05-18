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
