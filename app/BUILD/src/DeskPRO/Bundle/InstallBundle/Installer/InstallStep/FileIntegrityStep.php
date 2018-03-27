<?php

namespace DeskPRO\Bundle\InstallBundle\Installer\InstallStep;

use DeskPRO\Bundle\InstallBundle\FileIntegrity\Checker\IntegrityChecker;
use DeskPRO\Bundle\InstallBundle\FileIntegrity\Checker\IntegrityCheckResult;
use DeskPRO\Bundle\InstallBundle\FileIntegrity\FileHasher;
use DeskPRO\Bundle\InstallBundle\FileIntegrity\ProjectFileSet;

/**
 * Checks filesystem for integrity errors.
 */
class FileIntegrityStep extends AbstractStep
{
    public function run()
    {
        $this->writeBigTitle('File Integrity Check');

        $this->writeln(
            'We will now perform file integrity checks to ensure the files you have match '
            .'the ones that DeskPRO thinks should exist. This helps prevent issues to do with '
            .'invalid uploads or file copies, as well as security issues.'
        );
        $this->writeln('');

        if ($this->getContext()->getSession()->getSource() === 'dev'
            || $this->getContext()->getSession()->getSource() === 'buildserver'
        ) {
            $this->writeln('Dev mode. Skipping integrity check.');
            $this->getSession()->enableFlag('file_integrity_ok');

            return;
        }

        $dpEnv = $this->getContext()->getDpEnv();

        $map_path = $dpEnv->getAppBaseKernelCacheDir().DIRECTORY_SEPARATOR.'integrity_file_map.dat';
        if (!file_exists($map_path)) {
            $this->writeln('<error>The file integrity database could not be found.</error>');
            $this->writeln("The file was expected here: $map_path");
            $this->writeln('');
            $this->writeln('Please re-download the DeskPRO files and try again.');
            $this->markAsFailed();

            return;
        }

        $map = json_decode(file_get_contents($map_path), true);
        if (!$map || count($map) < 9000) {
            $this->writeln('<error>The file integrity database seems invalid.</error>');
            $this->writeln("The file was read from here: $map_path");
            $this->writeln('');
            $this->writeln('Please re-download the DeskPRO files and try again.');
            $this->writeln('Alternatively, if you want to SKIP the file integrity check, re-run this command like this: bin/install --skip file_integrity');
            $this->markAsFailed();

            return;
        }

        $set     = new ProjectFileSet($dpEnv);
        $hasher  = new FileHasher();
        $checker = new IntegrityChecker($set, $hasher);

        $progress = $this->createProgressBar(count($map));
        $progress->setFormat('%current%/%max% [%bar%] %percent:3s%%');

        $progress->start();
        $progress->setRedrawFrequency(500);
        $result = $checker->checkAgainstMap($map, function (IntegrityCheckResult $result) use ($progress) {
            $progress->advance();
        });
        $progress->finish();

        $this->writeln('');

        if ($result->countBad()) {
            $f = $this->getFormatterHelper();

            $badInfo = $result->getBadInfo();
            if (count($badInfo) > 100) {
                $count_missing = 0;
                $count_invalid = 0;
                foreach ($badInfo as $info) {
                    if ($info['error'] === IntegrityCheckResult::MISSING) {
                        ++$count_missing;
                    } else {
                        ++$count_invalid;
                    }
                }

                $this->writeln('<error>We found problems</error>');
                $this->writeln(sprintf('We found <error>%d</error> missing files and <error>%d</error> changed/invalid files', $count_missing, $count_invalid));
                $this->writeln('');
                $this->writeln('Since there are so many file errors, this probably means there was a problem copying or uploading DeskPRO files to your server.');
                $this->writeln('');
            } else {
                $this->writeln('<error>We found problems with the following files:</error>');

                foreach ($badInfo as $info) {
                    if ($info['error'] === IntegrityCheckResult::MISSING) {
                        $key = 'MISSING';
                    } else {
                        $key = 'INVALID';
                    }

                    $this->writeln($f->formatSection($key, $info['path']));
                }
                $this->writeln('');
            }

            $this->writeln('Please re-download DeskPRO and try again.');
            $this->markAsFailed();
        } else {
            $this->writeln('<info>All file checks look good.</info>');
            $this->getSession()->enableFlag('file_integrity_ok');
        }
    }

    public function isComplete()
    {
        return $this->getSession()->hasFlag('file_integrity_ok');
    }
}
