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
use DeskPRO\Bundle\UpdateBundle\BuildTasks\BuildFactory;
use DeskPRO\Bundle\UpdateBundle\BuildTasks\BuildRunner;
use Monolog\Handler\StreamHandler;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunBuildCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dp:update:tasks:run-build')->setAliases(['dp:update-db:run-build'])
            ->setDescription('Run a specific build')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force running the build even if it has already been run')
            ->addArgument('buildId', InputOption::VALUE_REQUIRED, 'The ID of the build to run')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        global $DP_ENV;

        $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);

        $logger = new Logger('upgrade');
        $logger->pushHandler(new ConsoleHandler($output));
        $logger->pushHandler(new StreamHandler($DP_ENV->getUserLogsDir().DIRECTORY_SEPARATOR.'upgrade.log'));

        $buildStatus    = $this->getContainer()->get('dp.build_tasks.build_status');
        $manifestReader = $this->getContainer()->get('dp.build_tasks.manifest_reader');
        $buildFactory   = new BuildFactory($manifestReader, $this->getContainer(), $logger);
        $buildRunner    = new BuildRunner($buildFactory, $manifestReader, $logger);

        $buildId   = $input->getArgument('buildId');
        $buildInfo = $manifestReader->findBuild($buildId);

        if (!$buildInfo) {
            $logger->error("No such build: {$buildId}");

            return 1;
        }

        if ($buildStatus->hasBuildRun($buildId)) {
            if ($input->getOption('force')) {
                $logger->warn("Build {$buildId} has already been run, but --force was used so it will be run again.");
            } else {
                $logger->error("Build {$buildId} has already beeen run. Use --force if you really want to re-run it.");

                return 1;
            }
        }

        try {
            $buildRunner->runBuild($buildId);
        } catch (\Exception $e) {
            $logger->error("Build {$buildId} failed");

            return 1;
        }

        $buildStatus->markBuildHasRun($buildId);
    }
}
