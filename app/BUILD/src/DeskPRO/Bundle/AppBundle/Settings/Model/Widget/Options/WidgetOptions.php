<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options;

use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\WidgetBrandSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\GlobalSettings\WidgetGlobalSettings;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class WidgetOptions.
 */
class WidgetOptions
{
    /**
     * @var WidgetGlobalSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\GlobalSettings\WidgetGlobalSettings")
     * @Assert\Valid()
     */
    private $global;

    /**
     * @var WidgetBrandSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\WidgetBrandSettings")
     * @Assert\Valid()
     */
    private $brand;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->global = new WidgetGlobalSettings();
        $this->brand  = new WidgetBrandSettings();
    }

    /**
     * @return WidgetGlobalSettings
     */
    public function getGlobal()
    {
        return $this->global;
    }

    /**
     * @param WidgetGlobalSettings $global
     *
     * @return $this
     */
    public function setGlobal(WidgetGlobalSettings $global)
    {
        $this->global = $global;

        return $this;
    }

    /**
     * @return WidgetBrandSettings
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param WidgetBrandSettings $brand
     *
     * @return $this
     */
    public function setBrand(WidgetBrandSettings $brand)
    {
        $this->brand = $brand;

        return $this;
    }
}
