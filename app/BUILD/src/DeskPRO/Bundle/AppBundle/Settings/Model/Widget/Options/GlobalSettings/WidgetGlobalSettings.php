<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\GlobalSettings;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class WidgetGlobalSettings.
 */
class WidgetGlobalSettings
{
    /**
     * Company settings.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\GlobalSettings\WidgetGlobalCompanySettings")
     * @Assert\Valid()
     *
     * @var WidgetGlobalCompanySettings
     */
    private $company;

    /**
     * Global chat settings.
     *
     * @var WidgetGlobalChatSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\GlobalSettings\WidgetGlobalChatSettings")
     * @Assert\Valid()
     */
    private $chat;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->company = new WidgetGlobalCompanySettings();
        $this->chat    = new WidgetGlobalChatSettings();
    }

    /**
     * @return WidgetGlobalCompanySettings
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param WidgetGlobalCompanySettings $company
     *
     * @return $this
     */
    public function setCompany(WidgetGlobalCompanySettings $company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return WidgetGlobalChatSettings
     */
    public function getChat()
    {
        return $this->chat;
    }

    /**
     * @param WidgetGlobalChatSettings $chat
     *
     * @return $this
     */
    public function setChat(WidgetGlobalChatSettings $chat)
    {
        $this->chat = $chat;

        return $this;
    }
}
