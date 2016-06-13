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

namespace AppBundle\Command\DpApp;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class InstallDistroCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dp:distro:install-build')
            ->setDescription('Installs a new build')
            ->addArgument('zip', InputArgument::REQUIRED, 'The path or URL to a DeskPRO ZIP file')
            ->addOption('select', null, InputOption::VALUE_REQUIRED, 'If the ZIP contains multiple builds, select this specific one instead of whichever is determined to be newest')
            ->addOption('as', null, InputOption::VALUE_REQUIRED, 'Save the build as a different build ID')
            ->addOption('override-build-time', null, InputOption::VALUE_REQUIRED, 'Override the build time of the build to the value you specify.')
            ->addOption('skip-existing', null, InputOption::VALUE_NONE, 'Do nothing if the build exists (returns a success code). See also --update-existing.')
            ->addOption('update-existing', null, InputOption::VALUE_NONE, 'Update the build if it exists. The default behaviour is to return an error status. See also --skip-existing.')
            ->addOption('update-sys', null, InputOption::VALUE_NONE, 'Update the system files (e.g. app/run etc). Also can be used to init a new project.')
            ->addOption('keep-backup', null, InputOption::VALUE_NONE, 'Keep the backup directory even when the install was successful')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dpDir = $this->getContainer()->getParameter('deskpro_app_path');

        $sysTmpDir = sys_get_temp_dir();

        $tmpDir    = $sysTmpDir.DIRECTORY_SEPARATOR.uniqid('dpbuild_');
        $tmpBackup = $sysTmpDir.DIRECTORY_SEPARATOR.uniqid('dpbak_');

        if (!mkdir($tmpDir)) {
            $output->writeln("Could not create tmp dir: $tmpDir");

            return 1;
        }

        if (!mkdir($tmpBackup)) {
            $output->writeln("Could not create tmp backup dir: $tmpBackup");

            return 1;
        }

        if (!is_dir($dpDir)) {
            $output->writeln('dpDir does not exist: '.$dpDir);

            return 1;
        }

        $zipPath = $input->getArgument('zip');
        if (preg_match('#^https?://#', $zipPath)) {
            if ($input->getOption('build-site')) {
                $zipPath = rtrim($zipPath, '/').'/deskpro.zip';

                if (!$input->getOption('as')) {
                    $m = null;
                    if (!preg_match('#^https?://site(?P<buildAs>[a-zA-Z0-9\-]+)\.#', $zipPath, $m)) {
                        $output->writeln('<error>Could not find the build name in the URL. Use --as param to override this.</error>');

                        return 1;
                    }

                    $input->setOption('as', $m['buildAs'].'.0');
                }
            }
        } else {
            if ($zipPath[0] !== '/') {
                $zipPath = getcwd().DIRECTORY_SEPARATOR.$zipPath;
            }

            if (!is_file($zipPath)) {
                $output->writeln('No such file: %s', $zipPath);

                return 1;
            }
        }

        if ($input->getOption('as')) {
            if ($this->doesBuildExist($input->getOption('as'))) {
                if ($input->getOption('skip-existing')) {
                    $output->write('<info>Build already exists, skipping.</info>');

                    return 0;
                }

                if (!$input->getOption('update-existing')) {
                    $output->write('<error>The build already exists. If you want to install anyway, use --update-existing.</error>');

                    return 1;
                }
            }
        }

        $overrideBuildTime = $input->getOption('override-build-time');
        if ($overrideBuildTime) {
            // From some other build -- @sys or @otherBuildId
            if ($overrideBuildTime[0] === '@') {
                if ($overrideBuildTime === '@sys') {
                    $overrideBuildTime = (int) @file_get_contents($dpDir.'/config/sysfiles_install_buildtime.txt') ?: 0;
                    if (!$overrideBuildTime) {
                        $output->write('<error>Looks like an invalid value specified for --override-build-time. There is no @sys build installed. This server is not initialised yet.</error>');

                        return 1;
                    }
                } else {
                    $fromBuild = substr($overrideBuildTime, 1);
                    if (!$this->doesBuildExist($fromBuild)) {
                        $output->write('<error>Looks like an invalid value specified for --override-build-time. No such build exists.</error>');

                        return 1;
                    }

                    $overrideBuildTime = (int) @file_get_contents($dpDir.'/app/'.$fromBuild.'/sys/config/build-time.txt');
                    if (!$overrideBuildTime) {
                        $output->write('<error>Looks like an invalid value specified for --override-build-time. That build has an invalid built-time.txt file.</error>');

                        return 1;
                    }
                }

                // A specific value
            } else {
                $overrideBuildTime = (int) $overrideBuildTime;
                if ($overrideBuildTime < 1464171732) {
                    $output->write('<error>Looks like an invalid value specified for --override-build-time</error>');

                    return 1;
                }
            }
        }

        // Clean up the tmp dir during shutdown
        register_shutdown_function(function () use ($tmpDir) {
            $proc = new Process('rm -rf '.escapeshellarg($tmpDir));
            $proc->setTimeout(120);
            $proc->run(function ($type, $data) {
                echo $data;
            });
        });

        #----------------------------------------
        # ZIP download and extract
        #----------------------------------------

        $script   = [];
        $script[] = '#!/bin/bash';
        $script[] = 'cd '.escapeshellarg($tmpDir);

        if (preg_match('#^https?://#', $zipPath)) {
            $script[] = 'curl -o deskpro.zip '.escapeshellarg($zipPath);
        } else {
            $script[] = 'cp '.escapeshellarg($zipPath).' deskpro.zip';
        }

        $script[] = 'unzip -q deskpro.zip';
        $script[] = 'exit 0';

        file_put_contents($tmpDir.'/get.sh', implode("\n", $script));

        $proc = new Process('/bin/bash -x get.sh', $tmpDir);
        $proc->setTimeout(600);
        $proc->run(function ($type, $data) {
            echo $data;
        });

        if (!$proc->isSuccessful()) {
            $output->writeln("\n\nFailed with status {$proc->getStatus()}. See output above.\nPath: $tmpDir");

            return 1;
        }

        unlink($tmpDir.'/get.sh');

        if (!is_dir($tmpDir.'/app/run') || !is_dir($tmpDir.'/bin') || !is_file($tmpDir.'/www/index.php')) {
            echo "The ZIP extracted but it does not look like a valid archive: $tmpDir\n";

            return 1;
        }

        #----------------------------------------
        # Find build
        #----------------------------------------

        $builds = $this->findAvailableBuilds($tmpDir.DIRECTORY_SEPARATOR.'app');

        if ($specBuild = $input->getOption('select')) {
            if (!in_array($specBuild, $builds)) {
                echo "No such build: $specBuild\n";

                return 1;
            }
            $buildId = $specBuild;
        } else {
            $buildId = array_pop($builds);
        }

        $buildTime = 0;
        if (file_exists($tmpDir.'/app/'.$buildId.'/sys/config/build-time.txt')) {
            $buildTime = file_get_contents($tmpDir.'/app/'.$buildId.'/sys/config/build-time.txt');
        }

        $buildIdTarget = $buildId;
        if ($input->getOption('as')) {
            $buildIdTarget = $input->getOption('as');
        }

        if ($this->doesBuildExist($buildIdTarget)) {
            if ($input->getOption('skip-existing')) {
                $output->write('<info>Build already exists, skipping.</info>');

                return 0;
            }

            if (!$input->getOption('update-existing')) {
                $output->write('<error>The build already exists. If you want to install anyway, use --update-existing.</error>');

                return 1;
            }
        }

        #----------------------------------------
        # Update sys
        #----------------------------------------

        $updateSys        = false;
        $currentBuildTime = @file_get_contents($dpDir.'/config/sysfiles_install_buildtime.txt') ?: 0;

        if ($input->getOption('update-sys')) {
            $updateSys = true;
        } elseif ($input->getOption('auto-update-sys')) {
            if ($currentBuildTime < $buildTime) {
                $updateSys = true;
            }
        }

        if ($updateSys || !$currentBuildTime) {
            $ret = $this->updateSys($dpDir, $tmpDir, $tmpBackup);
            if ($ret) {
                return $ret;
            }

            file_put_contents($dpDir.'/config/sysfiles_install_buildtime.txt', $buildTime);
            $currentBuildTime = $buildTime;
        }

        #----------------------------------------
        # Override build time
        #----------------------------------------

        if ($overrideBuildTime) {
            file_put_contents($tmpDir.'/app/'.$buildId.'/sys/config/build-time.php', "<?php define('DP_BUILD_TIME', {$overrideBuildTime});\n");
            file_put_contents($tmpDir.'/app/'.$buildId.'/sys/config/build-time.txt', $overrideBuildTime);
        }

        #----------------------------------------
        # Install build files
        #----------------------------------------

        foreach ([
            '/app/',
            '/www/assets/',
            '/var/kernel_cache/',
        ] as $dir) {
            $installDir = $dpDir.$dir.$buildIdTarget;
            $newDir     = $tmpDir.$dir.$buildId;
            $backupDir  = $tmpBackup.DIRECTORY_SEPARATOR.str_replace(['/', '\\'], '_', ltrim($dir.$buildIdTarget, '/\\'));

            if (is_dir($installDir)) {
                echo "Backup:\n";
                echo "  > From: $installDir\n";
                echo "  > To:   $backupDir\n";
                if (!rename($installDir, $backupDir)) {
                    echo "  FAILED\n";

                    return 1;
                }
                echo "  Done\n";
            }

            echo "Install:\n";
            echo "  > From: $newDir\n";
            echo "  > To:   $installDir\n";
            if (!rename($newDir, $installDir)) {
                echo "  FAILED\n";

                return 1;
            }
            echo "  Done\n";
        }

        // We need to delete the containers and routing from the kernel cache because
        // cloud will use its own, it differs from the normal build.
        // E.g. cloud has special APIs which have its own routing.
        // But we want to keep the other caches (e.g. twig) for performance

        /** @var \SplFileInfo[] $finder */
        $finder = Finder::create()
            ->in($dpDir.'/var/kernel_cache/'.$buildIdTarget)
            ->files()
            ->name('/(ProjectContainer.php|ProjectContainer.php.meta|UrlGenerator.php|UrlMatcher.php)$/')
        ;

        echo 'Removing non-cloud build files:';
        foreach ($finder as $file) {
            echo '  > '.$file->getBasename()."\n";
            unlink($file->getRealPath());
        }

        // Always keep a copy of the original zip assets dir (makes it easy to grab the version later)
        $cacheDir     = $dpDir.'/www/assets/'.$buildIdTarget.'/pub';
        $cacheZipFile = $cacheDir.'/deskpro.zip';

        if (file_exists($cacheZipFile)) {
            unlink($cacheZipFile);
        }

        if (!rename($tmpDir.'/deskpro.zip', $cacheZipFile)) {
            echo "Failed to keep copy of ZIP at $cacheZipFile\n";

            return 1;
        } else {
            echo "Copy of ZIP saved to $cacheZipFile\n";
        }

        if ($input->getOption('keep-backup')) {
            echo "Backup directory is kept at: $tmpBackup\n";
        } else {
            echo "Cleaning backup directory: $tmpBackup\n";

            $proc = new Process('rm -rf '.escapeshellarg($tmpBackup));
            $proc->setTimeout(120);
            $proc->run(function ($type, $data) {
                echo $data;
            });

            echo "Done\n";
        }
    }

    /**
     * @param string $buildId
     *
     * @return bool
     */
    private function doesBuildExist($buildId)
    {
        $dpDir     = $this->getContainer()->getParameter('deskpro_app_path');
        $checkPath = $dpDir.'/app/'.$buildId;

        return is_dir($checkPath);
    }

    /**
     * @param string $buildDir
     *
     * @return array
     */
    private function findAvailableBuilds($buildDir)
    {
        $iter   = new \FilesystemIterator($buildDir);
        $builds = [];

        /** @var \SplFileInfo $f */
        foreach ($iter as $f) {
            if ($f->isDir() && $f->getBasename() !== 'run') {
                $time_file = $f->getRealPath().'/sys/config/build-time.txt';
                if (file_exists($time_file)) {
                    $time          = intval(trim(file_get_contents($time_file)));
                    $builds[$time] = $f->getBasename();
                }
            }
        }

        // Fallback on dev build if it exists
        if (!$builds && is_dir($buildDir.'/BUILD')) {
            $builds[time()] = 'BUILD';
        }

        if (!$builds) {
            throw new \RuntimeException('There are no builds available in: '.$buildDir);
        }

        ksort($builds, SORT_NUMERIC);

        $builds = array_values($builds);

        return $builds;
    }
}
