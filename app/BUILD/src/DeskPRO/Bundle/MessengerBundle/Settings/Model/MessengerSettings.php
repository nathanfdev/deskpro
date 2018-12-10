<?php

namespace DeskPRO\Bundle\MessengerBundle\Settings\Model;

/**
 * Class MessengerSettings.
 */
class MessengerSettings
{
    /**
     * @var MessengerEmbed
     */
    private $embed;

    /**
     * @return MessengerEmbed
     */
    public function getEmbed()
    {
        return $this->embed;
    }

    /**
     * @param MessengerEmbed $embed
     *
     * @return $this
     */
    public function setEmbed($embed)
    {
        $this->embed = $embed;

        return $this;
    }
}
