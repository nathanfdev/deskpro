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
