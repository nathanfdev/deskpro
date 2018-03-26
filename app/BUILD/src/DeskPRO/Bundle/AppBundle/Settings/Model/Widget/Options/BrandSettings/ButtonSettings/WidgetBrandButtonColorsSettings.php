<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\ButtonSettings;

use JMS\Serializer\Annotation as JMS;

/**
 * Class WidgetBrandButtonColorsSettings.
 */
class WidgetBrandButtonColorsSettings
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $background = '#62ad8c';

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $text = '#ffffff';

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $border = '#4e9576';

    /**
     * @return string
     */
    public function getBorder()
    {
        return $this->border;
    }

    /**
     * @param string $border
     */
    public function setBorder($border)
    {
        $this->border = $border;
    }

    /**
     * @return string
     */
    public function getBackground()
    {
        return $this->background;
    }

    /**
     * @param string $background
     */
    public function setBackground($background)
    {
        $this->background = $background;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }
}
