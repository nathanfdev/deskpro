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

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\GlobalSettings;

use JMS\Serializer\Annotation as JMS;

/**
 * Class WidgetGlobalSettings.
 */
class WidgetGlobalSettings
{
    /**
     * Company settings.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\GlobalSettings\WidgetGlobalCompanySettings")
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
