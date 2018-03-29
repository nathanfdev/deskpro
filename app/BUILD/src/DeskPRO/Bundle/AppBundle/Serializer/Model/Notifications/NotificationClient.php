<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Notifications;

use JMS\Serializer\Annotation as JMS;

/**
 * Class NotificationClient.
 */
class NotificationClient
{
    /**
     * Client type.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $type;

    /**
     * Client options.
     *
     * @JMS\Type("array")
     *
     * @var array
     */
    protected $options;

    public function __construct($type, array $options)
    {
        $this->type    = $type;
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
