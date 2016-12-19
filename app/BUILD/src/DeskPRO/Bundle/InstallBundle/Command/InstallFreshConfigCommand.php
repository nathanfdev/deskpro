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

namespace DeskPRO\Bundle\InstallBundle\Command;

use DeskPRO\Component\Util\FilesystemUtils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class InstallFreshConfigCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('install:fresh-config')
            ->setDescription('Creates a fresh set of config files and installs them into config/')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        $sourceDir = FilesystemUtils::concatPath($DP_ENV->getAppDir(), 'config_new');
        $targetDir = FilesystemUtils::concatPath($DP_ENV->getDpRoot(), 'config');

        if (is_dir($targetDir)) {
            if (!FilesystemUtils::isDirEmpty($targetDir)) {
                $output->writeln('<error>Config directory already contains files: '.$targetDir.'</error>');
                $output->writeln('Remove the config directory before attempting to write pristine config files.');

                return 1;
            }
            if (!is_writable($targetDir)) {
                $output->writeln('<error>Config directory cannot be written to: '.$targetDir.'</error>');

                return 1;
            }
        } else {
            if (!mkdir($targetDir)) {
                $output->writeln('<error>Could not create the config directory: '.$targetDir.'</error>');

                return 1;
            }
        }

        $fs = new Filesystem();
        $fs->mirror($sourceDir, $targetDir);

        $output->writeln('<info>Wrote pristine config files to: '.$targetDir.'</info>');
        $output->writeln('You will need to edit these files to input real values for your server.');
    }
}
