<?php

namespace DeskPRO\Bundle\UpdateBundle\Command;

use DeskPRO\Bundle\UpdateBundle\Console\Helper\DistroExceptionHelper;
use DeskPRO\Bundle\UpdateBundle\Distro\DistroInstaller;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadBuildCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dp:distro:download-build')
            ->setDescription('Downloads a new build and installs the files, ready to be used')
            ->addArgument('zip', InputArgument::REQUIRED, 'The path or URL to a DeskPRO ZIP file, "latest" to load the latest version from the distro server, or @BUILD_ID to install a specific build.')
            ->addOption('as', null, InputOption::VALUE_REQUIRED, 'Save the build as a different build ID')
            ->addOption('sha256', null, InputOption::VALUE_REQUIRED, 'Compare the checksum of the file to this expected value after downloading. This is provided automatically when specying a build from the manifest.')
            ->addOption('skip-existing', null, InputOption::VALUE_NONE, 'Do nothing if the build exists (returns a success code). See also --update-existing.')
            ->addOption('replace-existing', null, InputOption::VALUE_NONE, 'Update the build if it exists. The default behaviour is to return an error status. See also --skip-existing.')
            ->addOption('session-id', null, InputOption::VALUE_REQUIRED, '(internal)')
            ->addOption('only-auto', null, InputOption::VALUE_NONE, 'If the zip is specified as "latest", only use "latest" with auto-update enabled')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('monolog.logger.updater.general');
        $logger->info('********** dp:distro:download-build **********');

        $sysTmpDir = $this->getContainer()->get('deskpro.app_env')->getUserTmpDir();

        if (!is_writable($sysTmpDir)) {
            $logger->error('temp dir not writable: '.$sysTmpDir);
            $output->writeln('<error>The temp directory is not writable: '.$sysTmpDir.'</error>');

            return 1;
        }

        $zipOption = $input->getArgument('zip');

        $zipFile  = null;
        $zipUrl   = null;
        $checksum = $input->getOption('sha256') ?: null;
        $release  = null;

        if ($input->getOption('skip-existing')) {
            $existMode = DistroInstaller::SKIP_EXIST;
        } elseif ($input->getOption('replace-existing')) {
            $existMode = DistroInstaller::REPLACE_EXIST;
        } else {
            $existMode = DistroInstaller::FAIL_EXIST;
        }

        //----------------------------------------
        // Get the ZIP option
        //----------------------------------------

        if (preg_match('/^https?:/i', $zipOption)) {
            $zipUrl = $zipOption;
            $logger->info('zipUrl: '.$zipUrl);
        } elseif ($zipOption === 'latest' || $zipOption[0] === '@') {
            $logger->info('build: '.$zipOption);

            try {
                $criteria = [];
                if ($input->getOption('only-auto')) {
                    $criteria = ['with_flags' => 'auto_enabled'];
                }
                $release = $this->getBuildFromManifest($zipOption, $criteria);
            } catch (\Exception $e) {
                $exHelper = new DistroExceptionHelper($output);
                $exHelper->renderManifestFailure($e, $this->getContainer()->get('dp.updater.distro.manifest_loader'));

                $output->writeln('');
                $output->writeln('');

                return 1;
            }

            $zipUrl   = $release->getZipUrl();
            $checksum = $release->getSha256();

            $logger->info('release build: '.$release->getId());
            $logger->info('release zipUrl: '.$zipUrl);
            $logger->info('release checksum: '.$checksum);
        } else {
            if (!is_file($zipOption)) {
                $output->writeln('<error>The ZIP file path you specified does not exist.</error>');
                $logger->error('Specified ZIP path does not exist: '.$zipOption);

                return 1;
            }

            $zipFile  = realpath($zipOption);
            $checksum = hash_file('sha256', $zipFile);

            $logger->info('zipFile: '.$zipUrl);
            $logger->info('generated checksum: '.$checksum);
        }

        $table = new Table($output);

        if ($release) {
            $table->addRow(['Build ID', $release->getId()]);
        }

        if ($zipFile) {
            $table->addRow(['Zip File', $zipFile]);
        } else {
            $table->addRow(['Zip URL', $zipUrl]);
        }

        if ($checksum) {
            $table->addRow(['sha256', $checksum]);
        }

        $table->setStyle('borderless');
        $table->render();

        if ($release) {
            $targetZip = $sysTmpDir.DIRECTORY_SEPARATOR.'build_'.$release->getId().'.zip';
        } elseif ($zipUrl) {
            $targetZip = $sysTmpDir.DIRECTORY_SEPARATOR.'build_'.md5($zipUrl).'.zip';
        } else {
            $targetZip = $sysTmpDir.DIRECTORY_SEPARATOR.'build_'.md5($zipFile).'.zip';
        }

        $asBuild = $input->getOption('as') ?: null;

        //----------------------------------------
        // Get the zip file
        //----------------------------------------

        $output->write("Downloading to $targetZip ...");

        try {
            $dl = $this->getContainer()->get('dp.updater.distro.downloader');

            if ($release) {
                $dl->download($release, $targetZip);
            } elseif ($zipUrl) {
                $dl->downloadUrl($zipUrl, $targetZip, $checksum);
            } else {
                $dl->downloadLocalFile($zipFile, $targetZip, $checksum);
            }
        } catch (\Exception $e) {
            $output->writeln('Failed');

            $exHelper = new DistroExceptionHelper($output);
            $exHelper->renderDownloadFailure($e, $zipUrl ?: $zipFile);

            return 1;
        }

        $output->writeln('OK');

        //----------------------------------------
        // Install it
        //----------------------------------------

        $distroInstall = $this->getContainer()->get('dp.updater.distro.installer');
        $output->write('Checking filesystem permissions ... ');

        $problems = $distroInstall->detectProblems();
        if ($problems) {
            $logger->error('Detected filesystem permission errors');

            $output->writeln('Errors:');

            foreach ($problems as $id => $str) {
                $logger->error("$id: $str");
                $output->writeln("<error>  * $str</error>");
            }
            $output->writeln('');

            return 1;
        } else {
            $output->writeln('OK');
            $logger->info('Filesystem checks OK');
        }

        $output->writeln('Extracting and installing files');

        $distroInstall->installFromZip($targetZip, $asBuild, $existMode);

        $output->writeln('Done');

        $output->writeln('<info>Build was installed successfully.</info>');

        return 0;
    }

    /**
     * @param string $buildId
     * @param array  $criteria
     *
     * @return \DeskPRO\Bundle\UpdateBundle\Distro\Manifest\DistroRelease
     */
    private function getBuildFromManifest($buildId, array $criteria = [])
    {
        $distroLoader = $this->getContainer()->get('dp.updater.distro.manifest_loader');
        $releases     = $distroLoader->loadReleases($criteria);

        if ($releases->count() === 0) {
            throw new \OutOfRangeException('The distribution manifest is empty. Did it fail to load?');
        }

        $buildId = ltrim($buildId, '@');

        if ($buildId === 'latest') {
            $r = $releases->getLatest();
        } else {
            $r = $releases->getById($buildId);
        }

        if (!$r) {
            throw new \OutOfRangeException('The distribution manifest does not have any release for: '.$buildId);
        }

        return $r;
    }
}
