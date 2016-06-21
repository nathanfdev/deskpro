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

namespace DeskPRO\Bundle\UpgradeBundle\Distro;

use Alchemy\Zippy\Zippy;
use DeskPRO\Bundle\UpgradeBundle\Instance\InstanceReader;
use DeskPRO\Component\Filesystem\TmpDir;
use DpRun\BuildScanner;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class DistroInstaller implements LoggerAwareInterface
{
    /**
     * @var Zippy
     */
    private $zippy;

    /**
     * @var \DeskPRO\Bundle\UpgradeBundle\Instance\InstanceReader
     */
    private $instanceStatus;

    /**
     * @var string
     */
    private $tmpDir;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * DistroInstaller constructor.
     *
     * @param Zippy          $zippy
     * @param InstanceReader $instanceStatus
     * @param null           $tmpDir
     */
    public function __construct(Zippy $zippy, InstanceReader $instanceStatus, $tmpDir = null)
    {
        $this->zippy          = $zippy;
        $this->instanceStatus = $instanceStatus;
        $this->tmpDir         = $tmpDir ?: sys_get_temp_dir();

        $this->setLogger(new NullLogger());
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Detect issues that will cause the installer to fail.
     *
     * @return array
     */
    public function detectProblems()
    {
        $problems = [];

        if (!is_writable($this->tmpDir)) {
            $problems['tmp_dir_not_writable'] = sprintf('The temporary directory is not writable: %s', $this->tmpDir);
        }

        if (!is_writable($this->instanceStatus->getAppBasePath())) {
            $problems['app_dir_not_writable'] = sprintf('The app directory is not writable: %s',
                $this->instanceStatus->getAppBasePath());
        }

        if (!is_writable($this->instanceStatus->getKernelCacheBasePath())) {
            $problems['kernel_cache_dir_not_writable'] = sprintf('The app directory is not writable: %s',
                $this->instanceStatus->getKernelCacheBasePath());
        }

        if (!is_writable($this->instanceStatus->getAssetsBasePath())) {
            $problems['assets_dir_not_writable'] = sprintf('The www assets directory is not writable: %s',
                $this->instanceStatus->getAssetsBasePath());
        }

        return $problems;
    }

    /**
     * @param string $zipPath
     * @param string $asBuild
     *
     * @throws \Exception
     * @throws IOException
     */
    public function installFromZip($zipPath, $asBuild = null)
    {
        $ts = microtime(true);
        $this->logger->debug(sprintf('Installing files -- begin at %s', date('Y-m-d H:i:s')));

        try {
            $this->doInstallFromZip($zipPath, $asBuild);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            $this->logger->debug(sprintf('Installing files -- finished at %s (%.3fs)', date('Y-m-d H:i:s'), microtime(true) - $ts));
        }
    }

    /**
     * @param string $zipPath
     * @param string $asBuild
     *
     * @throws IOException
     */
    private function doInstallFromZip($zipPath, $asBuild = null)
    {
        $fs = new Filesystem();

        $this->logger->debug(sprintf('Temp base dir: %s', $this->tmpDir));
        $this->logger->debug(sprintf('Zip file: %s', $zipPath));

        $scratchDir = TmpDir::makeTmpDir($this->tmpDir);
        $zip        = $this->zippy->open($zipPath);

        $this->logger->debug(sprintf('Extracting to: %s', $scratchDir));
        $zip->extract($scratchDir);

        $buildScanner = new BuildScanner("$scratchDir/app");
        $buildId      = $buildScanner->getLatestBuildDir();

        $this->logger->debug(sprintf('Build scanner detected: %s', implode(', ', $buildScanner->getAvailableBuilds())));
        $this->logger->debug(sprintf('Latest build is: %s', $buildScanner->getLatestBuildDir()));

        if (!$asBuild) {
            $asBuild = $buildId;
            $this->logger->debug(sprintf('$asBuild specified: %s', $asBuild));
        }

        // Copy the zip to sys/Resources dir
        $copyZipPath = "$scratchDir/app/$buildId/sys/Resources/deskpro.zip";
        copy($zipPath, $copyZipPath);

        $this->logger->debug(sprintf('Zip copied as a record to: %s', $copyZipPath));

        $moves = [
            "$scratchDir/app/$buildId"              => $this->instanceStatus->getAppPath($asBuild),
            "$scratchDir/var/kernel_cache/$buildId" => $this->instanceStatus->getKernelCachePath($asBuild),
            "$scratchDir/www/assets/$buildId"       => $this->instanceStatus->getAssetsPath($asBuild),

            // Move run dir into kernel_cache, we might enable it in just a moment
            "$scratchDir/app/run" => $this->instanceStatus->getKernelCachePath($asBuild).'/dp_run',
        ];

        foreach ($moves as $from => $to) {
            $this->logger->debug(sprintf('Rename: %s => %s', $from, $to));
            $fs->rename($from, $to);
        }
    }

    /**
     * Given a build that has already been installed, this installs the 'run' directory as well.
     *
     * @param string $buildId
     *
     * @throws IOException
     * @throws \InvalidArgumentException When the build doesnt exist
     * @throws \Exception
     */
    public function enableRunFromBuild($buildId)
    {
        $fs = new Filesystem();

        if (!$this->instanceStatus->hasBuild($buildId)) {
            $this->logger->error("$buildId dir does not exist");
            throw new \InvalidArgumentException("Cannot enable run for $buildId: Build is not installed");
        }

        $newRunPath = $this->instanceStatus->getKernelCachePath($buildId).'/dp_run';

        // The cached dp_run dir doesnt exist for whatever reason, we need to re-extract the full zip
        if (!is_dir($newRunPath)) {
            $this->logger->info('The default run path from kernel cache doesnt exist, we will need to re-extract');

            try {
                $scratchDir = TmpDir::makeTmpDir($this->tmpDir);
                $this->logger->info("Extracting into temp scratch dir: $scratchDir");

                $zip = $this->zippy->open($this->instanceStatus->getAppPath($buildId).'/sys/Resources/deskpro.zip');
                $zip->extract($scratchDir);
            } catch (\Exception $e) {
                $this->logger->error('Failed to extract: '.$e->getMessage());
                throw $e;
            }

            $newRunPath = "$scratchDir/app/run";
        }

        $runPath    = $this->instanceStatus->getAppBasePath().'/run';
        $oldRunPath = $runPath.'.'.uniqid('');

        $this->logger->info('Existing run dir will be moved to: '.$oldRunPath);

        $moves = [
            $runPath    => $oldRunPath,
            $newRunPath => $runPath,
        ];

        foreach ($moves as $from => $to) {
            try {
                $this->logger->info("Move: $from => $to");
                $fs->rename($from, $to);
            } catch (\Exception $e) {
                $this->logger->error('Failed fs move: '.$e->getMessage());
                throw $e;
            }
        }

        // Remove the old one
        try {
            $fs->remove($oldRunPath);
        } catch (\Exception $e) {
            $this->logger->warning('There was a problem removing the old build files: '.$e->getMessage());
            // We just ignore an exception here.
            // It is unlikely to occur, and it's not a
            // big issue if it does.
        }
    }
}
