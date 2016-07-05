<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\UpgradeBundle\Command;

use DeskPRO\Bundle\UpgradeBundle\Instance\BuildInstance;
use DpRun\BuildScanner;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ActivateBuildCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dp:upgrade:activate-build')
            ->setDescription('Activate an installed build')
            ->addArgument('buildId', InputArgument::REQUIRED, 'The build ID or the string "latest" to auto-detect the latest build.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        $logger = $this->getContainer()->get('monolog.logger.upgrader.general');
        $logger->info('********** dp:upgrade:activate-build **********');

        $buildId = $input->getArgument('buildId');
        $logger->debug('Activating build: '.$buildId);

        if ($buildId === 'latest') {
            $scanner = new BuildScanner($DP_ENV->getBuildDirRoot());
            $buildId = dirname($scanner->getLatestBuildDir());

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

        $activator = $this->getContainer()->get('dp.upgrader.activate.build_activator');

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
