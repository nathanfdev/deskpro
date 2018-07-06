<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings;

use DeskPRO\Bundle\AppBundle\Settings\Model\AbstractBrandAwareSettings;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class WidgetBrandSettings.
 */
class WidgetBrandSettings extends AbstractBrandAwareSettings
{
    /**
     * @var WidgetBrandCommonSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\WidgetBrandCommonSettings")
     * @Assert\Valid()
     */
    private $widget;

    /**
     * @var ButtonSettings\WidgetBrandButtonSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\ButtonSettings\WidgetBrandButtonSettings")
     * @Assert\Valid()
     */
    private $button;

    /**
     * @var ChatSettings\WidgetBrandChatSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\ChatSettings\WidgetBrandChatSettings")
     * @Assert\Valid()
     */
    private $chat;

    /**
     * @var WidgetBrandTicketSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\WidgetBrandTicketSettings")
     * @Assert\Valid()
     */
    private $ticket;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->widget = new WidgetBrandCommonSettings();
        $this->button = new ButtonSettings\WidgetBrandButtonSettings();
        $this->chat   = new ChatSettings\WidgetBrandChatSettings();
        $this->ticket = new WidgetBrandTicketSettings();
    }

    /**
     * @return WidgetBrandCommonSettings
     */
    public function getWidget()
    {
        return $this->widget;
    }

    /**
     * @param WidgetBrandCommonSettings $widget
     *
     * @return $this
     */
    public function setWidget(WidgetBrandCommonSettings $widget)
    {
        $this->widget = $widget;

        return $this;
    }

    /**
     * @return ButtonSettings\WidgetBrandButtonSettings
     */
    public function getButton()
    {
        return $this->button;
    }

    /**
     * @param ButtonSettings\WidgetBrandButtonSettings $button
     *
     * @return $this
     */
    public function setButton(ButtonSettings\WidgetBrandButtonSettings $button)
    {
        $this->button = $button;

        return $this;
    }

    /**
     * @return ChatSettings\WidgetBrandChatSettings
     */
    public function getChat()
    {
        return $this->chat;
    }

    /**
     * @param ChatSettings\WidgetBrandChatSettings $chat
     *
     * @return $this
     */
    public function setChat(ChatSettings\WidgetBrandChatSettings $chat)
    {
        $this->chat = $chat;

        return $this;
    }

    /**
     * @return WidgetBrandTicketSettings
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @param WidgetBrandTicketSettings $ticket
     *
     * @return $this
     */
    public function setTicket(WidgetBrandTicketSettings $ticket)
    {
        $this->ticket = $ticket;

        return $this;
    }
}
