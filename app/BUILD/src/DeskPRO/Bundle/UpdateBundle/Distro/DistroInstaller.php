<?php

namespace DeskPRO\Bundle\UpdateBundle\Distro;

use Alchemy\Zippy\Zippy;
use DeskPRO\Bundle\UpdateBundle\Instance\InstanceReader;
use DeskPRO\Bundle\UpdateBundle\Logger\LogKeyEvent;
use DeskPRO\Component\Filesystem\TmpDir;
use DeskPRO\Component\Util\Timer;
use DeskPRO\Component\Util\TypeUtils;
use DpRun\BuildScanner;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class DistroInstaller implements LoggerAwareInterface
{
    const SKIP_EXIST    = 'skip_existing';
    const REPLACE_EXIST = 'replace_existing';
    const FAIL_EXIST    = 'fail_existing';

    /**
     * @var Zippy
     */
    private $zippy;

    /**
     * @var \DeskPRO\Bundle\UpdateBundle\Instance\InstanceReader
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

        if (!empty($problems)) {
            $this->logger->info(
                'Detected problems',
                ['keyEvent' => LogKeyEvent::create('DistroInstaller.reqCheck.error', ['problems' => $problems])]
            );
        } else {
            $this->logger->info(
                'Detected problems',
                ['keyEvent' => LogKeyEvent::create('DistroInstaller.reqCheck.success')]
            );
        }

        return $problems;
    }

    /**
     * @param string $zipPath
     * @param string $asBuild
     * @param string $existHandling
     *
     * @throws \Exception
     * @throws IOException
     */
    public function installFromZip($zipPath, $asBuild = null, $existHandling = self::FAIL_EXIST)
    {
        $t = Timer::start();
        $this->logger->debug(
            "Installing files from $zipPath",
            ['keyEvent' => LogKeyEvent::create('DistroInstaller.start')]
        );

        try {
            $this->doInstallFromZip($zipPath, $asBuild, $existHandling);
            $this->logger->debug(
                'Done installing files',
                ['keyEvent' => LogKeyEvent::create('DistroInstaller.success')]
            );
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('[%s:%s] %s', TypeUtils::getBaseTypeName($e), $e->getCode(), $e->getMessage()),
                ['keyEvent' => LogKeyEvent::createForException('DistroInstaller.error', $e)]
            );
            throw $e;
        } finally {
            $this->logger->debug('Finished installFromZip in '.$t->formatTotalTime());
        }
    }

    /**
     * @param string $zipPath
     * @param string $asBuild
     * @param string $existHandling
     *
     * @throws IOException
     */
    private function doInstallFromZip($zipPath, $asBuild = null, $existHandling = self::FAIL_EXIST)
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

        $backupScratchDir = null;

        foreach ($moves as $from => $to) {
            $this->logger->debug(sprintf('Rename: %s => %s', $from, $to));
            $doRename = true;

            if (file_exists($to)) {
                $this->logger->debug(sprintf('Target %s already exists', $to));
                switch ($existHandling) {
                    case self::SKIP_EXIST:
                        $this->logger->info(sprintf('Skipping %s because of skip_existing mode', $from));
                        $doRename = false;
                        break;
                    case self::REPLACE_EXIST:
                        if (!$backupScratchDir) {
                            $backupScratchDir = TmpDir::makeTmpDir($this->tmpDir);
                        }
                        $backupTo = $scratchDir.DIRECTORY_SEPARATOR.md5($to);
                        $this->logger->info(sprintf('Moving existing %s to backup dir %s', $to, $backupTo));
                        $this->doRename($fs, $to, $backupTo);
                        break;
                }
            }

            if ($doRename) {
                $this->doRename($fs, $from, $to);
            }
        }
    }

    /**
     * Tries a rename 3 times with sleeps between them. This is to possibly fix issues on Windows
     * where the filesystem isnt ready to move yet and causes failures.
     *
     * @param Filesystem $fs
     * @param string     $from
     * @param string     $to
     *
     * @throws \Exception
     */
    private function doRename(Filesystem $fs, $from, $to)
    {
        $hasMoved = false;
        $attempt  = 0;

        do {
            try {
                $fs->rename($from, $to);
                $hasMoved = true;
            } catch (\Exception $e) {
                $this->logger->error(sprintf('[Attempt %d] Failed to move %s --> %s: %s %s', $attempt, $from, $to, $e->getCode(), $e->getMessage()));
                if ($attempt === 3) {
                    throw $e;
                }
            }
            if ($attempt) {
                sleep($attempt);
            }
        } while (!$hasMoved && ++$attempt <= 3);
    }
}
