<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\AgentAlerts;

use Application\DeskPRO\Entity\AgentAlert as AgentAlertEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * Class AgentAlert.
 */
class AgentAlert
{
    /**
     * Unique identity of alert.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $uuid;

    /**
     * Alert type.
     * Available values are:.
     *
     * 'notifications.tickets.new_ticket',
     * 'notifications.tickets.updated',
     * 'notifications.tickets.new_message.user_reply',
     * 'notifications.tickets.new_message.agent_note',
     * 'notifications.tickets.new_message.agent_reply'
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $type;

    /**
     * Alert data.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\AgentAlerts\AgentAlertData")
     *
     * @var AgentAlertData
     */
    private $data;

    /**
     * When this alert was created.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * Is it dismissed?
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $isDismissed;

    /**
     * AgentAlerts constructor.
     *
     * @param AgentAlertEntity $alert
     * @param string           $type
     * @param AgentAlertData   $data
     */
    public function __construct(AgentAlertEntity $alert, $type, AgentAlertData $data)
    {
        $this->uuid        = $alert->getId();
        $this->dateCreated = $alert->getDateCreated();
        $this->isDismissed = $alert->isDismissed();
        $this->type        = $type;
        $this->data        = $data;
    }
}
