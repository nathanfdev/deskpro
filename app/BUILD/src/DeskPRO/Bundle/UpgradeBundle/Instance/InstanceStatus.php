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

namespace DeskPRO\Bundle\UpgradeBundle\Instance;

use DpRun\BuildScanner;

class InstanceStatus
{
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
     * @param string $appPath
     * @param string $wwwPath
     * @param string $kernelCachePath
     */
    public function __construct($appPath, $wwwPath, $kernelCachePath)
    {
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
    public function getWwwPath($forBuildId)
    {
        return $this->wwwPath.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.$forBuildId;
    }

    /**
     * @return string
     */
    public function getWwwBasePath()
    {
        return $this->wwwPath;
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
}
