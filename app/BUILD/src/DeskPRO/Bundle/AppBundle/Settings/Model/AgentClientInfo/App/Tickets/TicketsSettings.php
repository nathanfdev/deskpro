<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets;

use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\AbstractAppSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\Fields\TicketGroupFieldSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\Fields\TicketOrderFieldSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\Permissions\TicketPermissionSettings;
use JMS\Serializer\Annotation as JMS;

/**
 * Class TicketsSettings.
 */
class TicketsSettings extends AbstractAppSettings
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $refCode;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $archiving;

    /**
     * @var TicketFieldInfoSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\TicketFieldsInfoSettings")
     */
    private $fieldInfo;

    /**
     * @var TicketBillingSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\TicketBillingSettings")
     */
    private $billing;

    /**
     * @var TicketTimelogSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\TicketTimelogSettings")
     */
    private $timelog;

    /**
     * @var TicketGroupFieldSettings[]
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\Fields\TicketGroupFieldSettings>")
     */
    private $groupFields = [];

    /**
     * @var TicketOrderFieldSettings[]
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\Fields\TicketOrderFieldSettings>")
     */
    private $orderFields = [];

    /**
     * @var TicketPermissionSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\Permissions\TicketPermissionSettings")
     */
    private $permissions;

    /**
     * @var TicketStatus[]
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Entity\TicketStatus>")
     */
    private $ticketStatuses = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->fieldInfo   = new TicketFieldsInfoSettings();
        $this->billing     = new TicketBillingSettings();
        $this->timelog     = new TicketTimelogSettings();
        $this->permissions = new TicketPermissionSettings();
    }

    /**
     * @return string
     */
    public function getRefCode()
    {
        return $this->refCode;
    }

    /**
     * @param string $refCode
     *
     * @return $this
     */
    public function setRefCode($refCode)
    {
        $this->refCode = $refCode;

        return $this;
    }

    /**
     * @return TicketStatus
     */
    public function getTicketStatuses()
    {
        return $this->ticketStatuses;
    }

    /**
     * @param TicketStatus[] $ticketStatuses
     */
    public function setTicketStatuses($ticketStatuses)
    {
        $this->ticketStatuses = $ticketStatuses;
    }

    /**
     * @return bool
     */
    public function isArchiving()
    {
        return $this->archiving;
    }

    /**
     * @param bool $archiving
     *
     * @return $this
     */
    public function setArchiving($archiving)
    {
        $this->archiving = $archiving;

        return $this;
    }

    /**
     * @return TicketFieldsInfoSettings
     */
    public function getFieldInfo()
    {
        return $this->fieldInfo;
    }

    /**
     * @return TicketBillingSettings
     */
    public function getBilling()
    {
        return $this->billing;
    }

    /**
     * @return TicketTimelogSettings
     */
    public function getTimelog()
    {
        return $this->timelog;
    }

    /**
     * @param TicketGroupFieldSettings $groupFieldSettings
     *
     * @return $this
     */
    public function addGroupByField(TicketGroupFieldSettings $groupFieldSettings)
    {
        $this->groupFields[] = $groupFieldSettings;

        return $this;
    }

    /**
     * @param TicketOrderFieldSettings $orderFieldSettings
     *
     * @return $this
     */
    public function addOrderByField(TicketOrderFieldSettings $orderFieldSettings)
    {
        $this->orderFields[] = $orderFieldSettings;

        return $this;
    }

    /**
     * @return TicketPermissionSettings
     */
    public function getPermissions()
    {
        return $this->permissions;
    }
}
