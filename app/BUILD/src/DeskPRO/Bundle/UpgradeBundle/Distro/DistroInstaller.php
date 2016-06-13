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
use DeskPRO\Bundle\UpgradeBundle\Instance\InstanceStatus;
use DeskPRO\Component\Exception\Filesystem\FileWriteException;
use DeskPRO\Component\Filesystem\TmpDir;
use DeskPRO\Component\Util\ExceptionUtils;
use DpRun\BuildScanner;

class DistroInstaller
{
    /**
     * @var Zippy
     */
    private $zippy;

    /**
     * @var \DeskPRO\Bundle\UpgradeBundle\Instance\InstanceStatus
     */
    private $instanceStatus;

    /**
     * @var string
     */
    private $tmpDir;

    /**
     * DistroInstaller constructor.
     *
     * @param Zippy          $zippy
     * @param InstanceStatus $instanceStatus
     * @param null           $tmpDir
     */
    public function __construct(Zippy $zippy, InstanceStatus $instanceStatus, $tmpDir = null)
    {
        $this->zippy          = $zippy;
        $this->instanceStatus = $instanceStatus;
        $this->tmpDir         = $tmpDir ?: sys_get_temp_dir();
    }

    /**
     * @param string $zipPath
     */
    public function installFromZip($zipPath)
    {
        $scratchDir = TmpDir::makeTmpDir($this->tmpDir);
        $zip        = $this->zippy->open($zipPath);

        $zip->extract($scratchDir);

        $buildScanner = new BuildScanner("$scratchDir/app");
        $buildId      = $buildScanner->getLatestBuildDir();

        // Copy the zip to the public dir
        // which serves as a record and easy way to fetch it if needed
        copy($zipPath, "$scratchDir/www/assets/$buildId/pub/deskpro.zip");

        $moves = [
            "$scratchDir/app/$buildId"              => $this->instanceStatus->getAppPath($buildId),
            "$scratchDir/var/kernel_cache/$buildId" => $this->instanceStatus->getKernelCachePath($buildId),
            "$scratchDir/www/assets/$buildId"       => $this->instanceStatus->getWwwPath($buildId),

            // Move run dir into kernel_cache, we might enable it in just a moment
            "$scratchDir/app/run" => $this->instanceStatus->getKernelCachePath($buildId).'/dp_run',
        ];

        foreach ($moves as $from => $to) {
            if (file_exists($to)) {
                throw new FileWriteException("Target build path already exists: $to");
            }

            $err = null;
            if (!ExceptionUtils::detectSuppressedError(function () use ($from, $to) {
                return @rename($from, $to);
            }, $err)) {
                if (!$err) {
                    $err = ['type' => E_WARNING, 'message' => 'File operation failed'];
                }
            }

            if ($err) {
                throw new FileWriteException(
                    sprintf('Failed to install %s directory: %s', basename($from), sprintf($err['message'])),
                    $err['type'],
                    null,
                    $err['message']
                );
            }
        }
    }

    /**
     * Given a build that has already been installed, this installs the 'run' directory as well.
     *
     * @param string $buildId
     */
    public function enableRunFromBuild($buildId)
    {
        if (!$this->instanceStatus->hasBuild($buildId)) {
            throw new \InvalidArgumentException("Cannot enable run for $buildId: Build is not installed");
        }

        $newRunPath = $this->instanceStatus->getKernelCachePath($buildId).'/dp_run';

        // The cached dp_run dir doesnt exist for whatever reason, we need to re-extract the full zip
        if (!is_dir($newRunPath)) {
            $scratchDir = TmpDir::makeTmpDir($this->tmpDir);
            $zip        = $this->zippy->open($this->instanceStatus->getWwwPath($buildId).'/pub/deskpro.zip');
            $zip->extract($scratchDir);

            $newRunPath = "$scratchDir/app/run";
        }

        $runPath = $this->instanceStatus->getAppBasePath().'/run';

        $moves = [
            $runPath    => $runPath.'.'.uniqid(''),
            $newRunPath => $runPath,
        ];

        foreach ($moves as $from => $to) {
            $err = null;
            if (!ExceptionUtils::detectSuppressedError(function () use ($from, $to) {
                return @rename($from, $to);
            }, $err)) {
                if (!$err) {
                    $err = ['type' => E_WARNING, 'message' => 'File operation failed'];
                }
            }

            if ($err) {
                throw new FileWriteException(
                    sprintf('Failed to move %s directory: %s', basename($to), sprintf($err['message'])),
                    $err['type'],
                    null,
                    $err['message']
                );
            }
        }
    }
}
