<?php

namespace DeskPRO\Bundle\MessengerBundle\Settings\Model;

use DeskPRO\Bundle\AppBundle\Settings\Model\AbstractBrandAwareSettings;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class MessengerSettings.
 */
class MessengerSettings extends AbstractBrandAwareSettings
{
    /**
     * Embed messenger settings.
     *
     * @JMS\Type("DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerEmbed")
     * @Assert\Valid()
     *
     * @var MessengerEmbed
     */
    private $embed;

    /**
     * Basic messenger chat window settings.
     *
     * @JMS\Type("DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerChat")
     * @Assert\Valid()
     *
     * @var MessengerChat
     */
    private $chat;

    /**
     * Basic messenger ticket window settings.
     *
     * @JMS\Type("DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerTickets")
     * @Assert\Valid()
     *
     * @var MessengerTickets
     */
    private $tickets;

    /**
     * Advanced messenger options.
     *
     * @JMS\Type("DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerOptions")
     * @Assert\Valid()
     *
     * @var MessengerOptions
     */
    private $messenger;

    /**
     * Few style options.
     *
     * @JMS\Type("DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerStyles")
     * @Assert\Valid()
     *
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
