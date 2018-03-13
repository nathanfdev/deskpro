<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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
