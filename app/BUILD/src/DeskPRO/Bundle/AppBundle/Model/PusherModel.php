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

namespace DeskPRO\Bundle\AppBundle\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class PusherModel.
 */
class PusherModel
{
    const PUSHER_CLASTER_US_WEST_1      = 'mt1';
    const PUSHER_CLASTER_EU_WEST_1      = 'eu';
    const PUSHER_CLASTER_AP_SOUTHEAST_1 = 'ap1';
    const PUSHER_CLASTER_AP_SOUTH_1     = 'ap2';

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $pusherEnabled;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $id;

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
    protected $key;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $cluster;

    /**
     * @return bool
     */
    public function isPusherEnabled()
    {
        return $this->pusherEnabled;
    }

    /**
     * @param bool $pusherEnabled
     *
     * @return $this
     */
    public function setPusherEnabled($pusherEnabled)
    {
        $this->pusherEnabled = $pusherEnabled;

        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

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
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     *
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return string
     */
    public function getCluster()
    {
        return $this->cluster;
    }

    /**
     * @param string $cluster
     *
     * @return $this
     */
    public function setCluster($cluster)
    {
        $this->cluster = $cluster;

        return $this;
    }
}
