<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\UpdateBundle\Instance;

class BuildInstance
{
    /**
     * @var string
     */
    private $buildId;

    /**
     * @var string
     */
    private $appPath;

    /**
     * @var string
     */
    private $webPath;

    /**
     * @var string
     */
    private $kernelCachePath;

    /**
     * BuildInstance constructor.
     *
     * @param string $buildId
     * @param string $appPath
     * @param string $webPath
     * @param string $kernelCachePath
     */
    public function __construct($buildId, $appPath, $webPath, $kernelCachePath)
    {
        $this->buildId         = $buildId;
        $this->appPath         = realpath($appPath) ?: trim($appPath, '/\\');
        $this->webPath         = realpath($webPath) ?: trim($webPath, '/\\');
        $this->kernelCachePath = realpath($kernelCachePath) ?: trim($kernelCachePath, '/\\');

        if (!is_dir($this->appPath)) {
            throw new \InvalidArgumentException("Invalid appPath: {$this->appPath}");
        }
        if (!is_dir($this->webPath)) {
            throw new \InvalidArgumentException("Invalid webPath: {$this->webPath}");
        }
        if (!is_dir($this->kernelCachePath)) {
            throw new \InvalidArgumentException("Invalid kernelCachePath: {$this->kernelCachePath}");
        }
    }

    /**
     * @return string
     */
    public function getBuildId()
    {
        return $this->buildId;
    }

    /**
     * @return string
     */
    public function getAppPath()
    {
        return $this->appPath;
    }

    /**
     * @return string
     */
    public function getWebPath()
    {
        return $this->webPath;
    }

    /**
     * @return string
     */
    public function getKernelCachePath()
    {
        return $this->kernelCachePath;
    }
}
