<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets;

use DeskPRO\Bundle\AppBundle\Settings\Model\EnabledOptionTrait;
use JMS\Serializer\Annotation as JMS;

/**
 * Class TicketFieldInfoSettings.
 */
class TicketFieldInfoSettings
{
    use EnabledOptionTrait;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $defaultId;

    /**
     * @return int
     */
    public function getDefaultId()
    {
        return $this->defaultId;
    }

    /**
     * @param int $defaultId
     */
    public function setDefaultId($defaultId)
    {
        $this->defaultId = $defaultId;
    }
}
