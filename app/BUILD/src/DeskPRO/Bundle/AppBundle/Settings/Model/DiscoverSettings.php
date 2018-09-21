<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class DiscoverSettings.
 */
class DiscoverSettings
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"discover"})
     */
    private $helpdeskUuid;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     * @JMS\Groups({"discover"})
     */
    private $isDeskpro = true;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     * @JMS\Groups({"discover"})
     */
    private $isCloud = false;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"discover"})
     */
    private $helpdeskUrl;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"discover"})
     */
    private $baseApiUrl;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"discover"})
     */
    private $build;

    /**
     * @var int
     * @JMS\Type("integer")
     * @JMS\Groups({"discover"})
     */
    private $buildId;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"discover"})
     */
    private $buildName;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"discover"})
     */
    private $appsOauthProxyUrl = '';

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"discover"})
     */
    private $appsHttpProxyUrl = '';

    /**
     * @return string
     */
    public function getHelpdeskUuid()
    {
        return $this->helpdeskUuid;
    }

    /**
     * @param string $helpdeskUuid
     *
     * @return DiscoverSettings
     */
    public function setHelpdeskUuid($helpdeskUuid)
    {
        $this->helpdeskUuid = $helpdeskUuid;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeskpro()
    {
        return $this->isDeskpro;
    }

    /**
     * @param bool $isDeskpro
     *
     * @return $this
     */
    public function setIsDeskpro($isDeskpro)
    {
        $this->isDeskpro = $isDeskpro;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCloud()
    {
        return $this->isCloud;
    }

    /**
     * @param bool $isCloud
     *
     * @return $this
     */
    public function setIsCloud($isCloud)
    {
        $this->isCloud = $isCloud;

        return $this;
    }

    /**
     * @return string
     */
    public function getHelpdeskUrl()
    {
        return $this->helpdeskUrl;
    }

    /**
     * @param string $helpdeskUrl
     *
     * @return $this
     */
    public function setHelpdeskUrl($helpdeskUrl)
    {
        $this->helpdeskUrl = $helpdeskUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getBaseApiUrl()
    {
        return $this->baseApiUrl;
    }

    /**
     * @param string $baseApiUrl
     *
     * @return $this
     */
    public function setBaseApiUrl($baseApiUrl)
    {
        $this->baseApiUrl = $baseApiUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getBuild()
    {
        return $this->build;
    }

    /**
     * @param string $build
     *
     * @return $this
     */
    public function setBuild($build)
    {
        $this->build = $build;

        return $this;
    }

    /**
     * @return int
     */
    public function getBuildId()
    {
        return $this->buildId;
    }

    /**
     * @param int $buildId
     *
     * @return DiscoverSettings
     */
    public function setBuildId($buildId)
    {
        $this->buildId = $buildId;

        return $this;
    }

    /**
     * @return string
     */
    public function getBuildName()
    {
        return $this->buildName;
    }

    /**
     * @param string $buildName
     *
     * @return DiscoverSettings
     */
    public function setBuildName($buildName)
    {
        $this->buildName = $buildName;

        return $this;
    }

    /**
     * @return string
     */
    public function getAppsOauthProxyUrl()
    {
        return $this->appsOauthProxyUrl;
    }

    /**
     * @param string $appsOauthProxyUrl
     *
     * @return DiscoverSettings
     */
    public function setAppsOauthProxyUrl($appsOauthProxyUrl)
    {
        $this->appsOauthProxyUrl = $appsOauthProxyUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getAppsHttpProxyUrl()
    {
        return $this->appsHttpProxyUrl;
    }

    /**
     * @param string $appsHttpProxyUrl
     *
     * @return DiscoverSettings
     */
    public function setAppsHttpProxyUrl($appsHttpProxyUrl)
    {
        $this->appsHttpProxyUrl = $appsHttpProxyUrl;

        return $this;
    }
}
