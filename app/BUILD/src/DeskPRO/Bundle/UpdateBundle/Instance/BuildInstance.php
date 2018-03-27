<?php

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
