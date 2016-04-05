<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings;

use JMS\Serializer\Annotation as JMS;

/**
 * Class WidgetBrandSettings.
 */
class WidgetBrandSettings
{
    /**
     * @var WidgetBrandCommonSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\WidgetBrandCommonSettings")
     */
    private $widget;

    /**
     * @var ButtonSettings\WidgetBrandButtonSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\ButtonSettings\WidgetBrandButtonSettings")
     */
    private $button;

    /**
     * @var ChatSettings\WidgetBrandChatSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\ChatSettings\WidgetBrandChatSettings")
     */
    private $chat;

    /**
     * @var WidgetBrandTicketSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\WidgetBrandTicketSettings")
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
