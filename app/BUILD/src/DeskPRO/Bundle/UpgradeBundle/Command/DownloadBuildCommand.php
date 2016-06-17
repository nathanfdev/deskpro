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

use DeskPRO\Bundle\UpgradeBundle\Console\Helper\DistroExceptionHelper;
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
            ->addOption('update-existing', null, InputOption::VALUE_NONE, 'Update the build if it exists. The default behaviour is to return an error status. See also --skip-existing.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('monolog.logger.upgrader.general');
        $logger->info('********** dp:distro:download-build **********');

        $sysTmpDir = $this->getContainer()->get('deskpro.app_env')->getUserTmpDir();

        if (!is_writable($sysTmpDir)) {
            $output->writeln('<error>The temp directory is not writable: '.$targetZip.'</error>');

            return 1;
        }

        $zipOption = $input->getArgument('zip');

        $zipFile  = null;
        $zipUrl   = null;
        $checksum = $input->getOption('sha256') ?: null;
        $release  = null;

        #----------------------------------------
        # Get the ZIP option
        #----------------------------------------

        if (preg_match('/^https?:/i', $zipOption)) {
            $zipUrl = $zipOption;
            $logger->info('zipUrl: '.$zipUrl);
        } elseif ($zipOption === 'latest' || $zipOption[0] === '@') {
            $logger->info('build: '.$zipOption);

            try {
                $release = $this->getBuildFromManifest($zipOption);
            } catch (\Exception $e) {
                $exHelper = new DistroExceptionHelper($output);
                $exHelper->renderManifestFailure($e, $this->getContainer()->get('dp.upgrader.distro.manifest_loader'));

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

        #----------------------------------------
        # Get the zip file
        #----------------------------------------

        $output->write("Downloading to $targetZip ...");

        try {
            $dl = $this->getContainer()->get('dp.upgrader.distro.downloader');

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

        #----------------------------------------
        # Install it
        #----------------------------------------

        $distroInstall = $this->getContainer()->get('dp.upgrader.distro.installer');
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

        $distroInstall->installFromZip($targetZip, $asBuild);

        $output->writeln('Done');

        $output->writeln('<info>Build was installed successfully.</info>');
    }

    /**
     * @param string $buildId
     *
     * @return \DeskPRO\Bundle\UpgradeBundle\Distro\Manifest\DistroRelease
     */
    private function getBuildFromManifest($buildId)
    {
        $distroLoader = $this->getContainer()->get('dp.upgrader.distro.manifest_loader');
        $releases     = $distroLoader->loadReleases();

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
