<?php

namespace DeskPRO\Bundle\MessengerBundle\Settings\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class MessengerOptions.
 */
class MessengerOptions
{
    const STYLE_AVATAR_TEXT_BUTTON = 'avatar-text-button';
    const STYLE_AVATAR_TEXT_INPUT  = 'avatar-text-input';
    const STYLE_AVATAR_BUTTON      = 'avatar-button';
    const STYLE_TEXT_BUTTON        = 'text-button';
    const STYLE_TEXT_INPUT         = 'text-input';
    const STYLE_AVATAR_WIDGET      = 'avatar-widget';

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
     * Indicates the timeout (in seconds) when the messenger window should be risen automatically.
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("autoStartTimeout")
     *
     * @var int
     */
    private $autoStartTimeout = 0;

    /**
     * Indicates the style when the messenger window is risen automatically.
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("autoStartStyle")
     *
     * @var string
     */
    private $autoStartStyle = self::STYLE_AVATAR_TEXT_BUTTON;

    /**
     * This is Deskpro global setting need to be serialized with all other settings.
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("maxFileSize")
     *
     * @var
     */
    private $maxFileSize;

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
     * @return bool
     */
    public function getAutoStartTimeout()
    {
        return $this->autoStartTimeout;
    }

    /**
     * @param int $timeout
     *
     * @return $this
     */
    public function setAutoStartTimeout($timeout)
    {
        $this->autoStartTimeout = (int) $timeout;

        return $this;
    }

    /**
     * @return string
     */
    public function getAutoStartStyle()
    {
        return $this->autoStartStyle;
    }

    /**
     * @param string $style
     *
     * @return $this
     */
    public function setAutoStartStyle($style)
    {
        $this->autoStartStyle = $style;

        return $this;
    }

    /**
     * @param int $fileSize
     *
     * @return $this;
     */
    public function setMaxFileSize($fileSize)
    {
        $this->maxFileSize = (int) $fileSize;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxFileSize()
    {
        return $this->maxFileSize;
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
