<?php

namespace DeskPRO\Bundle\MessengerBundle\Settings\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class MessengerOptions.
 */
class MessengerOptions
{
    /**
     * Indicates whenever messenger window should be risen automatically.
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("autoStart")
     *
     * @var bool
     */
    private $autoStart = false;

    /**
     * A title.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $title = 'Get In Touch';

    /**
     * A short hint.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $subtext = '';

    /**
     * Advanced chat options.
     *
     * @JMS\Type("DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerOptionsChat")
     * @Assert\Valid()
     *
     * @var MessengerOptionsChat
     */
    private $chat;

    /**
     * Advanced chat options.
     *
     * @JMS\Type("DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerOptionsTickets")
     * @Assert\Valid()
     *
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
