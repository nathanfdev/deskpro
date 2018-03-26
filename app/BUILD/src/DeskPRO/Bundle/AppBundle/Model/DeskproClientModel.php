<?php

namespace DeskPRO\Bundle\AppBundle\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class DeskproClientModel.
 */
class DeskproClientModel
{
    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $deskproClientEnabled;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $secret;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $host;

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $port;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $secure;

    /**
     * @return bool
     */
    public function isDeskproClientEnabled()
    {
        return $this->deskproClientEnabled;
    }

    /**
     * @param bool $deskproClientEnabled
     *
     * @return $this
     */
    public function setDeskproClientEnabled($deskproClientEnabled)
    {
        $this->deskproClientEnabled = $deskproClientEnabled;

        return $this;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     *
     * @return $this
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     *
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param int $port
     *
     * @return $this
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSecure()
    {
        return $this->secure;
    }

    /**
     * @param bool $secure
     *
     * @return $this
     */
    public function setSecure($secure)
    {
        $this->secure = (bool) $secure;

        return $this;
    }
}
