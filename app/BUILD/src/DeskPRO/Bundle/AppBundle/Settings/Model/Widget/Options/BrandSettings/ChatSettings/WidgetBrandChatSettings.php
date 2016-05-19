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

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\ChatSettings;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class WidgetBrandChatSettings.
 */
class WidgetBrandChatSettings
{
    const BEGIN_MODE_CONVERSATION = 'conversation';
    const BEGIN_MODE_FORM         = 'form';

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $requestUserInfo = true;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $proactive = true;

    /**
     * @var WidgetBrandChatPopupSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\ChatSettings\WidgetBrandChatPopupSettings")
     * @Assert\Valid()
     */
    private $popup;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $beginMode = self::BEGIN_MODE_FORM;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     *
     * @JMS\Type("integer")
     * @Assert\GreaterThanOrEqual(30)
     */
    private $waitingTimeout = 30;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->popup = new WidgetBrandChatPopupSettings();
    }

    /**
     * @return bool
     */
    public function isRequestUserInfo()
    {
        return $this->requestUserInfo;
    }

    /**
     * @param bool $requestUserInfo
     *
     * @return $this
     */
    public function setRequestUserInfo($requestUserInfo)
    {
        $this->requestUserInfo = $requestUserInfo;

        return $this;
    }

    /**
     * @return bool
     */
    public function isProactive()
    {
        return $this->proactive;
    }

    /**
     * @param bool $proactive
     *
     * @return $this
     */
    public function setProactive($proactive)
    {
        $this->proactive = $proactive;

        return $this;
    }

    /**
     * @return WidgetBrandChatPopupSettings
     */
    public function getPopup()
    {
        return $this->popup;
    }

    /**
     * @param WidgetBrandChatPopupSettings $popup
     *
     * @return $this
     */
    public function setPopup(WidgetBrandChatPopupSettings $popup)
    {
        $this->popup = $popup;

        return $this;
    }

    /**
     * @return string
     */
    public function getBeginMode()
    {
        return $this->beginMode;
    }

    /**
     * @param string $beginMode
     *
     * @return $this
     */
    public function setBeginMode($beginMode)
    {
        $this->beginMode = $beginMode;

        return $this;
    }

    /**
     * @return int
     */
    public function getWaitingTimeout()
    {
        return $this->waitingTimeout;
    }

    /**
     * @param int $waitingTimeout
     *
     * @return $this
     */
    public function setWaitingTimeout($waitingTimeout)
    {
        $this->waitingTimeout = $waitingTimeout;

        return $this;
    }
}
