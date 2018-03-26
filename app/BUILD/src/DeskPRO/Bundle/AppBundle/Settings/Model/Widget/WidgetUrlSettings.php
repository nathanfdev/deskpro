<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Widget;

use JMS\Serializer\Annotation as JMS;

/**
 * Class WidgetUrlSettings.
 */
class WidgetUrlSettings
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $widgetLoader;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $widgetBundle;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $helpdesk;

    /**
     * @return string
     */
    public function getWidgetLoader()
    {
        return $this->widgetLoader;
    }

    /**
     * @param string $widgetLoader
     *
     * @return $this
     */
    public function setWidgetLoader($widgetLoader)
    {
        $this->widgetLoader = $widgetLoader;

        return $this;
    }

    /**
     * @return string
     */
    public function getWidgetBundle()
    {
        return $this->widgetBundle;
    }

    /**
     * @param string $widgetBundle
     *
     * @return $this
     */
    public function setWidgetBundle($widgetBundle)
    {
        $this->widgetBundle = $widgetBundle;

        return $this;
    }

    /**
     * @return string
     */
    public function getHelpdesk()
    {
        return $this->helpdesk;
    }

    /**
     * @param string $helpdesk
     *
     * @return $this
     */
    public function setHelpdesk($helpdesk)
    {
        $this->helpdesk = $helpdesk;

        return $this;
    }
}
