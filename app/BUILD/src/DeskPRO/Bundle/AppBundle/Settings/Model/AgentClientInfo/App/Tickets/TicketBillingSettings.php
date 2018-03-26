<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets;

use DeskPRO\Bundle\AppBundle\Settings\Model\EnabledOptionTrait;
use JMS\Serializer\Annotation as JMS;

/**
 * Class TicketBillingSettings.
 */
class TicketBillingSettings
{
    use EnabledOptionTrait;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $currencyName;

    /**
     * @return string
     */
    public function getCurrencyName()
    {
        return $this->currencyName;
    }

    /**
     * @param string $currencyName
     */
    public function setCurrencyName($currencyName)
    {
        $this->currencyName = $currencyName;
    }
}
