<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\AgentAlerts;

use JMS\Serializer\Annotation as JMS;

/**
 * Class AgentAlertData.
 */
class AgentAlertData
{
    /**
     * Ticket identity.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $ticket;

    /**
     * Additional data about notification.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\AgentAlerts\NotifyData")
     *
     * @var NotifyData
     */
    private $notification;

    /**
     * Who performed this alert. Identity.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $performer;

    /**
     * AgentAlertData constructor.
     *
     * @param int        $ticket
     * @param NotifyData $notification
     * @param int        $performer
     */
    public function __construct($ticket, NotifyData $notification, $performer)
    {
        $this->ticket       = $ticket;
        $this->notification = $notification;
        $this->performer    = $performer;
    }
}
