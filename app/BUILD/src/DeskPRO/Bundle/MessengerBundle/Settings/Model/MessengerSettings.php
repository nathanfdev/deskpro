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
     * @var MessengerChat
     */
    private $chat;

    /**
     * @var MessengerTickets
     */
    private $tickets;

    /**
     * @var MessengerOptions
     */
    private $messenger;

    /**
     * @var MessengerStyles
     */
    private $styles;

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
    public function setEmbed(MessengerEmbed $embed)
    {
        $this->embed = $embed;

        return $this;
    }

    /**
     * @return MessengerChat
     */
    public function getChat()
    {
        return $this->chat;
    }

    /**
     * @param MessengerChat $chat
     *
     * @return $this
     */
    public function setChat(MessengerChat $chat)
    {
        $this->chat = $chat;

        return $this;
    }

    /**
     * @return MessengerTickets
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * @param MessengerTickets $tickets
     *
     * @return $this
     */
    public function setTickets(MessengerTickets $tickets)
    {
        $this->tickets = $tickets;

        return $this;
    }

    /**
     * @return MessengerOptions
     */
    public function getMessenger()
    {
        return $this->messenger;
    }

    /**
     * @param MessengerOptions $messenger
     *
     * @return $this
     */
    public function setMessenger(MessengerOptions $messenger)
    {
        $this->messenger = $messenger;

        return $this;
    }

    /**
     * @return MessengerStyles
     */
    public function getStyles()
    {
        return $this->styles;
    }

    /**
     * @param MessengerStyles $styles
     *
     * @return $this
     */
    public function setStyles(MessengerStyles $styles)
    {
        $this->styles = $styles;

        return $this;
    }
}
