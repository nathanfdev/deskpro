<?php

namespace DeskPRO\Bundle\UpdateBundle\Command;

use DeskPRO\Bundle\UpdateBundle\Instance\BuildInstance;
use DpRun\BuildScanner;
use Monolog\Logger;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ActivateBuildCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dp:update:activate-build')
            ->setDescription('Activate an installed build')
            ->addOption('session-id', null, InputOption::VALUE_REQUIRED, '(internal)')
            ->addArgument('buildId', InputArgument::REQUIRED, 'The build ID or the string "latest" to auto-detect the latest build.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        $logger = $this->getContainer()->get('monolog.logger.updater.general');
        $logger->info('********** dp:update:activate-build **********');

        $buildId = $input->getArgument('buildId');
        $logger->debug('Activating build: '.$buildId);

        if ($buildId === 'latest') {
            $scanner = new BuildScanner($DP_ENV->getBuildDirRoot());
            $buildId = $scanner->getLatestBuildDir();

            $logger->debug('Detected latest build as: '.$buildId);
        }

        if (!$buildId) {
            $logger->debug('No build ID');
            $output->writeln('<error>No build ID specified</error>');

            return 1;
        }

        try {
            $build = new BuildInstance(
                $buildId,
                $DP_ENV->getBuildDirRoot().DIRECTORY_SEPARATOR.$buildId,
                $DP_ENV->getWwwRoot().DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.$buildId,
                $DP_ENV->getKernelCacheDirRoot().DIRECTORY_SEPARATOR.$buildId
            );
        } catch (\Exception $e) {
            $logger->error('Invalid build ID: '.$e->getMessage());
            $output->writeln('<error>Invalid build</error>');
            $output->writeln($e->getMessage());

            return 1;
        }

        $activator = $this->getContainer()->get('dp.updater.activate.build_activator');

        if ($output->getVerbosity() !== OutputInterface::VERBOSITY_QUIET && $logger instanceof Logger) {
            $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
            $logger->pushHandler(new ConsoleHandler($output));
        }

        try {
            $activator->activateBuild($build);
        } catch (\Exception $e) {
            $logger->error('Failed to activate build: '.$e->getMessage());
            $output->writeln('<error>Failed to activate build</error>');
            $output->writeln($e->getMessage());

            return 1;
        }

        $output->writeln('<info>Activated build '.$build->getBuildId().'</info>');

        return 0;
    }
}
