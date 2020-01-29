<?php

namespace DeskPRO\Bundle\MessengerBundle\Settings\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class MessengerEmbed.
 */
class MessengerEmbed
{
    /**
     * Should messenger be shown on the Portal.
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("showOnPortal")
     *
     * @var bool
     */
    private $showOnPortal = true;

    /**
     * A comma separated list of authorized domains.
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("authorizeDomains")
     *
     * @var string
     */
    private $authorizeDomains = '';

    /**
     * A jwt secret key
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("jwtSecret")
     */
    private $jwtSecret = '';

    /**
     * @return bool
     */
    public function isShowOnPortal()
    {
        return $this->showOnPortal;
    }

    /**
     * @param bool $showOnPortal
     *
     * @return $this
     */
    public function setShowOnPortal($showOnPortal)
    {
        $this->showOnPortal = (bool) $showOnPortal;

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthorizeDomains()
    {
        return $this->authorizeDomains;
    }

    /**
     * @param string $authorizeDomains
     *
     * @return $this
     */
    public function setAuthorizeDomains($authorizeDomains)
    {
        $this->authorizeDomains = (string) $authorizeDomains;

        return $this;
    }

    /**
     * @return string
     */
    public function getJwtSecret()
    {
        return $this->jwtSecret;
    }

    /**
     * @param string $jwtSecret
     *
     * @return $this
     */
    public function setJwtSecret($jwtSecret)
    {
        $this->jwtSecret = $jwtSecret;

        return $this;
    }
}
