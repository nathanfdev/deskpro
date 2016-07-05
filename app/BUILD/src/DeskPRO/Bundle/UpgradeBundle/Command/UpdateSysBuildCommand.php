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
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateSysBuildCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dp:upgrade:update-sys-build')
            ->setDescription('Activates new system bootstrapping files from a new build')
            ->addOption('buildId', null, InputOption::VALUE_REQUIRED, 'The build to activate. If not provided, the current build will be used.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('monolog.logger.upgrader.general');
        $logger->info('********** dp:upgrade:update-sys-build **********');

        $buildId = $input->getOption('buildId');
        if ($buildId) {
            $logger->info(sprintf('Activating specified build: %s', $buildId));
        } else {
            $logger->info('No build specified, will use active build');

            $buildId = $this->getContainer()->get('deskpro.app_env')->getAppName();
            $logger->info(sprintf('Activating current build: %s', $buildId));
        }

        $appEnv = $this->getContainer()->get('deskpro.app_env');

        $build = new BuildInstance(
            $buildId,
            $appEnv->getBuildDirRoot().DIRECTORY_SEPARATOR.$buildId,
            $appEnv->getWwwRoot().DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.$buildId,
            $appEnv->getAppBaseKernelCacheDir().DIRECTORY_SEPARATOR.$buildId
        );

        $output->writeln('Activating sys files for build: '.$buildId);
        $distroInstall = $this->getContainer()->get('dp.upgrader.activate.run_activator');
        $distroInstall->activateRunDir($build);

        $output->writeln('<info>Done</info>');
        $logger->info('Finished activating new sys build');

        return 0;
    }
}
