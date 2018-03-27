<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\GlobalSettings;

use JMS\Serializer\Annotation as JMS;

/**
 * Class WidgetGlobalCompanySettings.
 */
class WidgetGlobalCompanySettings
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $name;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $logo;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param string $logo
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
    }
}
