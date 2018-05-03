<?php

namespace DeskPRO\Bundle\UpdateBundle\Instance;

use DeskPRO\Bundle\UpdateBundle\Distro\Manifest\DistroReleaseCollection;
use DeskPRO\Bundle\UpdateBundle\Distro\Manifest\UnofficialDistroRelease;
use DpRun\BuildScanner;

class InstanceReader
{
    /**
     * @var string
     */
    private $currentBuildId;

    /**
     * @var string
     */
    private $appPath;

    /**
     * @var string
     */
    private $wwwPath;

    /**
     * @var string
     */
    private $kernelCachePath;

    /**
     * @var BuildScanner
     */
    private $buildScanner;

    /**
     * InstanceStatus constructor.
     *
     * @param string $currentBuildId
     * @param string $appPath
     * @param string $wwwPath
     * @param string $kernelCachePath
     */
    public function __construct($currentBuildId, $appPath, $wwwPath, $kernelCachePath)
    {
        $this->currentBuildId  = $currentBuildId;
        $this->appPath         = $this->getRealAppPath($appPath);
        $this->wwwPath         = $this->getRealAppPath($wwwPath);
        $this->kernelCachePath = $this->getRealAppPath($kernelCachePath);
        $this->buildScanner    = new BuildScanner($appPath);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function getRealAppPath($path)
    {
        if (!is_dir($path)) {
            throw new \InvalidArgumentException("Invalid path: $path");
        }

        return $path;
    }

    /**
     * @param string $forBuildId
     *
     * @return string
     */
    public function getAppPath($forBuildId)
    {
        return $this->appPath.DIRECTORY_SEPARATOR.$forBuildId;
    }

    /**
     * @return string
     */
    public function getAppBasePath()
    {
        return $this->appPath;
    }

    /**
     * @param string $forBuildId
     *
     * @return string
     */
    public function getAssetsPath($forBuildId)
    {
        return $this->wwwPath.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.$forBuildId;
    }

    /**
     * @return string
     */
    public function getAssetsBasePath()
    {
        return $this->wwwPath.DIRECTORY_SEPARATOR.'assets';
    }

    /**
     * @param string $forBuildId
     *
     * @return string
     */
    public function getKernelCachePath($forBuildId)
    {
        return $this->kernelCachePath.DIRECTORY_SEPARATOR.$forBuildId;
    }

    /**
     * @return string
     */
    public function getKernelCacheBasePath()
    {
        return $this->kernelCachePath;
    }

    /**
     * Check if a certain build is installed.
     *
     * @param $buildId
     *
     * @return bool
     */
    public function hasBuild($buildId)
    {
        return is_dir($this->getAppPath($buildId));
    }

    /**
     * @return mixed
     */
    public function getBuilds()
    {
        return $this->buildScanner->getAvailableBuilds();
    }

    /**
     * @param DistroReleaseCollection $releases
     *
     * @return InstanceStatus
     */
    public function getInstanceStatus(DistroReleaseCollection $releases)
    {
        $currentRelease = $releases->getById($this->currentBuildId);
        if (!$currentRelease) {
            $buildTimeFile = $this->getAppBasePath().'/'.$this->currentBuildId.'/sys/config/build-time.txt';
            if (is_file($buildTimeFile)) {
                $buildTime = new \DateTime('@'.trim(file_get_contents($buildTimeFile)));
            } else {
                $buildTime = new \DateTime();
            }

            $currentRelease = new UnofficialDistroRelease([
                'id'   => $this->currentBuildId,
                'date' => $buildTime,
            ]);
        }

        // It is possible that $releases is empty (e.g. failed to load a manifest)
        $latestRelease = $releases->getLatest();
        if (!$latestRelease) {
            $latestRelease = $currentRelease;
        }

        $numBetween = -1;
        foreach ($releases->getReleases() as $r) {
            if ($r->getId() === $currentRelease->getId()) {
                $numBetween = 0;
            } elseif ($numBetween >= 0) {
                ++$numBetween;
            }

            if ($r->getId() === $latestRelease->getId()) {
                break;
            }
        }

        $status = new InstanceStatus($currentRelease, $latestRelease, max(0, $numBetween));

        return $status;
    }
}
