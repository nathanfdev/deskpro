<?php

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
