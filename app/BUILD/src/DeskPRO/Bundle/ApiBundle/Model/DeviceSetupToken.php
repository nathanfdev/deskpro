<?php

namespace DeskPRO\Bundle\ApiBundle\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class DeviceSetupToken.
 */
class DeviceSetupToken
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $setupToken;

    /**
     * Constructor.
     *
     * @param string $url
     */
    public function __construct($url)
    {
        $this->setupToken = 'dp_device_setup:'.$url;
    }
}
