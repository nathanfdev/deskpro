<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class DiscoverSettings.
 */
class DiscoverSettings
{
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
     * @return string
     */
    public function getAppsOauthProxyUrl()
    {
        return $this->appsOauthProxyUrl;
    }

    /**
     * @param string $appsOauthProxyUrl
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
     * @return DiscoverSettings
     */
    public function setAppsHttpProxyUrl($appsHttpProxyUrl)
    {
        $this->appsHttpProxyUrl = $appsHttpProxyUrl;
        return $this;
    }
}
