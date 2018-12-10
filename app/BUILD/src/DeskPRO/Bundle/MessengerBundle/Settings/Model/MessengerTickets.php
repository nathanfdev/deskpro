<?php

namespace DeskPRO\Bundle\MessengerBundle\Settings\Model;

/**
 * Class MessengerTickets.
 */
class MessengerTickets
{
    /**
     * @var bool
     */
    private $enabled = false;

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     *
     * @return $this
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool) $enabled;

        return $this;
    }
}
