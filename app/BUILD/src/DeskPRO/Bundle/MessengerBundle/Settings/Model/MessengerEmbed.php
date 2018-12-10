<?php

namespace DeskPRO\Bundle\MessengerBundle\Settings\Model;

class MessengerEmbed
{
    /**
     * @var bool
     */
    private $showOnPortal = false;

    /**
     * @var string
     */
    private $authorizeDomains = '';

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
}
