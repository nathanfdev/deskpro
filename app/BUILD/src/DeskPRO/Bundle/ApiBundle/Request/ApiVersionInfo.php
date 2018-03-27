<?php

namespace DeskPRO\Bundle\ApiBundle\Request;

/**
 * Class ApiVersionInfo.
 */
class ApiVersionInfo
{
    /**
     * @var string
     */
    private $defaultVersion = 'latest';

    /**
     * @var array
     */
    private $versions = [];

    /**
     * Constructor.
     *
     * @param string $defaultVersion
     * @param array  $versions
     */
    public function __construct($defaultVersion, array $versions)
    {
        $this->defaultVersion = $defaultVersion;
        $this->versions       = $versions;
    }

    /**
     * @return string
     */
    public function getDefaultVersion()
    {
        return $this->defaultVersion === 'latest' ? max($this->versions) : $this->defaultVersion;
    }

    /**
     * @return array
     */
    public function getVersions()
    {
        return $this->versions;
    }

    /**
     * @param string $checkVersion
     *
     * @return string
     */
    public function getClosestVersion($checkVersion)
    {
        $filtered = $this->getLowerVersions($checkVersion);

        return count($filtered) ? max($filtered) : min($this->versions);
    }

    /**
     * @param $checkVersion
     *
     * @return array
     */
    public function getLowerVersions($checkVersion)
    {
        return array_filter($this->versions, function ($version) use ($checkVersion) {
            return (int) $checkVersion >= (int) $version;
        });
    }
}
