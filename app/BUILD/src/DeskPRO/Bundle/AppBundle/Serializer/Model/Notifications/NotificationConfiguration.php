<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Notifications;

use JMS\Serializer\Annotation as JMS;

/**
 * Class NotificationConfiguration.
 */
class NotificationConfiguration
{
    /**
     * Clients array.
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Serializer\Model\Notifications\NotificationClient>")
     *
     * @var array
     */
    protected $clients;

    /**
     * NotificationConfiguration constructor.
     *
     * @param array $clients
     */
    public function __construct(array $clients)
    {
        $this->clients = $clients;
    }

    /**
     * @return array
     */
    public function getClients()
    {
        return $this->clients;
    }
}
