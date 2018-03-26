<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets;

use JMS\Serializer\Annotation as JMS;

/**
 * Class TicketCustomFieldInfoSettings.
 */
class TicketCustomFieldInfoSettings
{
    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $hasAny = false;

    /**
     * @return bool
     */
    public function isHasAny()
    {
        return $this->hasAny;
    }

    /**
     * @param bool $hasAny
     */
    public function setHasAny($hasAny)
    {
        $this->hasAny = $hasAny;
    }
}
