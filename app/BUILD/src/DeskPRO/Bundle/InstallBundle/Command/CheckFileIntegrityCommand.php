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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\InstallBundle\Command;

use DeskPRO\Bundle\InstallBundle\FileIntegrity\Checker\IntegrityChecker;
use DeskPRO\Bundle\InstallBundle\FileIntegrity\Checker\IntegrityCheckResult;
use DeskPRO\Bundle\InstallBundle\FileIntegrity\FileHasher;
use DeskPRO\Bundle\InstallBundle\FileIntegrity\ProjectFileSet;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckFileIntegrityCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('install:check:integrity')
            ->setDescription('Checks files on the filesystem against the precomputed hashes shipped in the DeskPRO distro. This will detect corrupt or missing files.')
            ->addOption('list-all', null, InputOption::VALUE_NONE, 'List all errors even when there are many')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        $map_path = $DP_ENV->getAppBaseKernelCacheDir().DIRECTORY_SEPARATOR.'integrity_file_map.dat';

        if (!file_exists($map_path)) {
            $output->writeln('<error>The file integrity database could not be found.</error>');
            $output->writeln("The file was expected here: $map_path");
            $output->writeln('');
            $output->writeln('Please re-download the DeskPRO files and try again.');

            return;
        }

        $map = json_decode(file_get_contents($map_path), true);
        if (!$map || count($map) < 9000) {
            $output->writeln('<error>The file integrity database seems invalid.</error>');
            $output->writeln("The file was read from here: $map_path");
            $output->writeln('');
            $output->writeln('Please re-download the DeskPRO files and try again.');

            return;
        }

        $set     = new ProjectFileSet($DP_ENV);
        $hasher  = new FileHasher();
        $checker = new IntegrityChecker($set, $hasher);

        $progress = new ProgressBar($output, count($map));
        $progress->setFormat('%current%/%max% [%bar%] %percent:3s%%');

        $progress->start();
        $progress->setRedrawFrequency(500);
        $result = $checker->checkAgainstMap($map, function (IntegrityCheckResult $result) use ($progress) {
            $progress->advance();
        });
        $progress->finish();

        $output->writeln('');

        if ($result->countBad()) {
            $badInfo = $result->getBadInfo();
            if (count($badInfo) > 100 && !$input->getOption('list-all')) {
                $count_missing = 0;
                $count_invalid = 0;
                foreach ($badInfo as $info) {
                    if ($info['error'] === IntegrityCheckResult::MISSING) {
                        ++$count_missing;
                    } else {
                        ++$count_invalid;
                    }
                }

                $output->writeln('<error>We found problems</error>');
                $output->writeln(sprintf('We found <error>%d</error> missing files and <error>%d</error> changed/invalid files', $count_missing, $count_invalid));
                $output->writeln('');
                $output->writeln('Since there are so many file errors, this probably means there was a problem copying or uploading DeskPRO files to your server.');
                $output->writeln('Re-run this command with the --list-all to list all files..');
                $output->writeln('');
            } else {
                $output->writeln('<error>We found problems with the following files:</error>');

                foreach ($badInfo as $info) {
                    if ($info['error'] === IntegrityCheckResult::MISSING) {
                        $key = 'MISSING';
                    } else {
                        $key = 'INVALID';
                    }

                    $output->writeln("[{$key}]  {$info['path']}");
                }
                $output->writeln('');
            }
        } else {
            $output->writeln('<info>All file checks look good.</info>');
        }
    }
}
