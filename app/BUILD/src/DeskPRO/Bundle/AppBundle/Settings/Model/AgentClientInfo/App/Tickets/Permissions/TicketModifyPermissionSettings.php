<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\Permissions;

use JMS\Serializer\Annotation as JMS;

/**
 * Class TicketModifyPermissionSettings.
 */
class TicketModifyPermissionSettings
{
    /**
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\Permissions\TicketModifyPermissionGroupSettings")
     *
     * @var TicketModifyPermissionGroupSettings
     */
    private $own;

    /**
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\Permissions\TicketModifyPermissionGroupSettings")
     *
     * @var TicketModifyPermissionGroupSettings
     */
    private $following;

    /**
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\Permissions\TicketModifyPermissionGroupSettings")
     *
     * @var TicketModifyPermissionGroupSettings
     */
    private $unassing;

    /**
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\Permissions\TicketModifyPermissionGroupSettings")
     *
     * @var TicketModifyPermissionGroupSettings
     */
    private $others;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->own       = new TicketModifyPermissionGroupSettings();
        $this->following = new TicketModifyPermissionGroupSettings();
        $this->unassing  = new TicketModifyPermissionGroupSettings();
        $this->others    = new TicketModifyPermissionGroupSettings();
    }

    /**
     * @return TicketModifyPermissionGroupSettings
     */
    public function getOwn()
    {
        return $this->own;
    }

    /**
     * @return TicketModifyPermissionGroupSettings
     */
    public function getFollowing()
    {
        return $this->following;
    }

    /**
     * @return TicketModifyPermissionGroupSettings
     */
    public function getUnassing()
    {
        return $this->unassing;
    }

    /**
     * @return TicketModifyPermissionGroupSettings
     */
    public function getOthers()
    {
        return $this->others;
    }
}
