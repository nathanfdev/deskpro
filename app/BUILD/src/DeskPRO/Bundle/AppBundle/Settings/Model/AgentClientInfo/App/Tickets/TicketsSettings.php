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

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets;

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
