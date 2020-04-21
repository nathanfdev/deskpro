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
     * @JMS\Type("map<map<DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerTranslation>>")
     */
    private $translations;

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
     * @JMS\Type("DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerProactive")
     * @Assert\Valid()
     *
     * @var MessengerProactive
     */
    private $proactive;

    /**
     * Few style options.
     *
     * @JMS\Type("DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerWidget")
     * @Assert\Valid()
     *
     * @var MessengerWidget
     */
    private $widget;

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
     * @return mixed
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param mixed $translations
     *
     * @return $this
     */
    public function setTranslations($translations)
    {
        $this->translations = $translations;

        return $this;
    }

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
     * @return MessengerProactive
     */
    public function getProactive()
    {
        return $this->proactive;
    }

    /**
     * @param MessengerProactive $proactive
     *
     * @return $this
     */
    public function setProactive(MessengerProactive $proactive)
    {
        $this->proactive = $proactive;

        return $this;
    }

    /**
     * @return MessengerWidget
     */
    public function getWidget()
    {
        return $this->widget;
    }

    /**
     * @param MessengerWidget $widget
     *
     * @return $this
     */
    public function setWidget(MessengerWidget $widget)
    {
        $this->widget = $widget;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMaxFileSize()
    {
        return $this->maxFileSize;
    }

    /**
     * @param mixed $maxFileSize
     *
     * @return $this
     */
    public function setMaxFileSize($maxFileSize)
    {
        $this->maxFileSize = $maxFileSize;

        return $this;
    }
}
