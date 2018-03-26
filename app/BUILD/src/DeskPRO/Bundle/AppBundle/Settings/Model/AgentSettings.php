<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model;

use DeskPRO\Bundle\AppBundle\Settings\Model\Tickets\TicketsSettings;

/**
 * Class AgentSettings.
 */
class AgentSettings
{
    /**
     * @var TicketsSettings
     */
    private $tickets;

    /**
     * @var IMSettings
     */
    private $im;

    /**
     * @return TicketsSettings
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * @param TicketsSettings $tickets
     */
    public function setTickets(TicketsSettings $tickets)
    {
        $this->tickets = $tickets;
    }

    /**
     * @return IMSettings
     */
    public function getIM()
    {
        return $this->im;
    }

    /**
     * @param IMSettings $im
     *
     * @return $this
     */
    public function setIM(IMSettings $im)
    {
        $this->im = $im;

        return $this;
    }
}
