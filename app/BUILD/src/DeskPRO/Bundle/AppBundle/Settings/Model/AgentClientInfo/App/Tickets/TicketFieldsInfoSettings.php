<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets;

use JMS\Serializer\Annotation as JMS;

/**
 * Class TicketFieldsInfoSettings.
 */
class TicketFieldsInfoSettings
{
    /**
     * @var TicketFieldInfoSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\TicketFieldInfoSettings")
     */
    private $product;

    /**
     * @var TicketFieldInfoSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\TicketFieldInfoSettings")
     */
    private $category;

    /**
     * @var TicketFieldInfoSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\TicketFieldInfoSettings")
     */
    private $workflow;

    /**
     * @var TicketFieldInfoSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\TicketFieldInfoSettings")
     */
    private $priority;

    /**
     * @var TicketCustomFieldInfoSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\TicketCustomFieldInfoSettings")
     */
    private $custom;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->product  = new TicketFieldInfoSettings();
        $this->category = new TicketFieldInfoSettings();
        $this->workflow = new TicketFieldInfoSettings();
        $this->priority = new TicketFieldInfoSettings();
        $this->custom   = new TicketCustomFieldInfoSettings();
    }

    /**
     * @return TicketFieldInfoSettings
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return TicketFieldInfoSettings
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return TicketFieldInfoSettings
     */
    public function getWorkflow()
    {
        return $this->workflow;
    }

    /**
     * @return TicketFieldInfoSettings
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @return TicketCustomFieldInfoSettings
     */
    public function getCustom()
    {
        return $this->custom;
    }
}
