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
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class DistroInstaller
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
    }

    /**
     * @param string $zipPath
     *
     * @throws IOException
     */
    public function installFromZip($zipPath)
    {
        $fs = new Filesystem();

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
            $fs->rename($from, $to);
        }
    }

    /**
     * Given a build that has already been installed, this installs the 'run' directory as well.
     *
     * @param string $buildId
     *
     * @throws IOException
     */
    public function enableRunFromBuild($buildId)
    {
        $fs = new Filesystem();

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

        $runPath    = $this->instanceStatus->getAppBasePath().'/run';
        $oldRunPath = $runPath.'.'.uniqid('');

        $moves = [
            $runPath    => $oldRunPath,
            $newRunPath => $runPath,
        ];

        foreach ($moves as $from => $to) {
            $fs->rename($from, $to);
        }

        // Remove the old one
        try {
            $fs->remove($oldRunPath);
        } catch (\Exception $e) {
            // We just ignore an exception here.
            // It is unlikely to occur, and it's not a
            // big issue if it does.
        }
    }
}
