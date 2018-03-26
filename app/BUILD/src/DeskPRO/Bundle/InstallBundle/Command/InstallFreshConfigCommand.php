<?php

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
