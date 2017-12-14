<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Widget;

use DeskPRO\Bundle\AppBundle\Settings\Model\AbstractBrandAwareSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\WidgetOptions;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class WidgetSettings.
 */
class WidgetSettings extends AbstractBrandAwareSettings
{
    /**
     * A bunch of configuration described in urls.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\Widget\WidgetUrlSettings")
     * @Assert\Valid()
     *
     * @var WidgetUrlSettings
     */
    private $url;

    /**
     * Widget settings itself.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\WidgetOptions")
     * @Assert\Valid()
     *
     * @var WidgetOptions
     */
    private $settings;

    /**
     * Widget settings itself.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\Widget\JwtSettings")
     * @Assert\Valid()
     *
     * @var JwtSettings
     */
    private $jwtSettings;

    /**
     * If widget enabled on portal.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $enabledOnPortal = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->url         = new WidgetUrlSettings();
        $this->settings    = new WidgetOptions();
        $this->jwtSettings = new JwtSettings();
    }

    /**
     * @return WidgetUrlSettings
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param WidgetUrlSettings $url
     *
     * @return $this
     */
    public function setUrl(WidgetUrlSettings $url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return WidgetOptions
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param WidgetOptions $settings
     *
     * @return $this
     */
    public function setSettings(WidgetOptions $settings)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * @return JwtSettings
     */
    public function getJwtSettings()
    {
        return $this->jwtSettings;
    }

    /**
     * @param JwtSettings $jwtSettings
     *
     * @return $this
     */
    public function setJwtSettings(JwtSettings $jwtSettings)
    {
        $this->jwtSettings = $jwtSettings;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabledOnPortal()
    {
        return $this->enabledOnPortal;
    }

    /**
     * @param bool $enabledOnPortal
     *
     * @return $this
     */
    public function setEnabledOnPortal($enabledOnPortal)
    {
        $this->enabledOnPortal = $enabledOnPortal;

        return $this;
    }
}
