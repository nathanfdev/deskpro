<?php

namespace DeskPRO\Bundle\MessengerBundle\Settings\Model;

class MessengerOptions
{
    /**
     * @var bool
     */
    private $autoStart = false;

    /**
     * @var string
     */
    private $title = 'Get In Touch';

    /**
     * @var string
     */
    private $subtext = '';

    /**
     * @var MessengerOptionsChat
     */
    private $chat;

    /**
     * @var MessengerOptionsTickets
     */
    private $tickets;

    /**
     * @return bool
     */
    public function isAutoStart()
    {
        return $this->autoStart;
    }

    /**
     * @param bool $autoStart
     *
     * @return $this
     */
    public function setAutoStart($autoStart)
    {
        $this->autoStart = (bool) $autoStart;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubtext()
    {
        return $this->subtext;
    }

    /**
     * @param string $subtext
     *
     * @return $this
     */
    public function setSubtext($subtext)
    {
        $this->subtext = $subtext;

        return $this;
    }

    /**
     * @return MessengerOptionsChat
     */
    public function getChat()
    {
        return $this->chat;
    }

    /**
     * @param MessengerOptionsChat $chat
     *
     * @return $this
     */
    public function setChat(MessengerOptionsChat $chat)
    {
        $this->chat = $chat;

        return $this;
    }

    /**
     * @return MessengerOptionsTickets
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * @param MessengerOptionsTickets $tickets
     *
     * @return $this
     */
    public function setTickets(MessengerOptionsTickets $tickets)
    {
        $this->tickets = $tickets;

        return $this;
    }
}
