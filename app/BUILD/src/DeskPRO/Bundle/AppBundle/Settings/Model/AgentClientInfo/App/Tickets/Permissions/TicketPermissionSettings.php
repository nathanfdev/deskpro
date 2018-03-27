<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\Permissions;

use JMS\Serializer\Annotation as JMS;

/**
 * Class TicketPermissionSettings.
 */
class TicketPermissionSettings
{
    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $create;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $replyMass;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $modifySetArchived;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $createLabels;

    /**
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\Permissions\TicketModifyPermissionSettings")
     *
     * @var TicketModifyPermissionSettings
     */
    private $modify;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->modify = new TicketModifyPermissionSettings();
    }

    /**
     * @return bool
     */
    public function isCreate()
    {
        return $this->create;
    }

    /**
     * @param bool $create
     *
     * @return $this
     */
    public function setCreate($create)
    {
        $this->create = $create;

        return $this;
    }

    /**
     * @return bool
     */
    public function isReplyMass()
    {
        return $this->replyMass;
    }

    /**
     * @param bool $replyMass
     *
     * @return $this
     */
    public function setReplyMass($replyMass)
    {
        $this->replyMass = $replyMass;

        return $this;
    }

    /**
     * @return bool
     */
    public function isModifySetArchived()
    {
        return $this->modifySetArchived;
    }

    /**
     * @param bool $modifySetArchived
     *
     * @return $this
     */
    public function setModifySetArchived($modifySetArchived)
    {
        $this->modifySetArchived = $modifySetArchived;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCreateLabels()
    {
        return $this->createLabels;
    }

    /**
     * @param bool $createLabels
     *
     * @return $this
     */
    public function setCreateLabels($createLabels)
    {
        $this->createLabels = $createLabels;

        return $this;
    }

    /**
     * @return TicketModifyPermissionSettings
     */
    public function getModify()
    {
        return $this->modify;
    }
}
