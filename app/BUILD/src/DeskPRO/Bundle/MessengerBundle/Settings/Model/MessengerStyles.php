<?php

namespace DeskPRO\Bundle\MessengerBundle\Settings\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class MessengerStyles.
 */
class MessengerStyles
{
    /**
     * Primary colour of messenger.
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("primaryColor")
     *
     * @var string
     */
    private $primaryColor = '#3d88f3';

    /**
     * Background colour for messenger.
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("backgroundColor")
     *
     * @var string
     */
    private $backgroundColor = '#aaa';

    /**
     * Icon and text colour for messenger.
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("textColor")
     *
     * @var string
     */
    private $textColor = '#ffffff';

    /**
     * @return string
     */
    public function getPrimaryColor()
    {
        return $this->primaryColor;
    }

    /**
     * @param string $primaryColor
     *
     * @return $this
     */
    public function setPrimaryColor($primaryColor)
    {
        $this->primaryColor = $primaryColor;

        return $this;
    }

    /**
     * @return string
     */
    public function getBackgroundColor()
    {
        return $this->backgroundColor;
    }

    /**
     * @param string $backgroundColor
     *
     * @return $this
     */
    public function setBackgroundColor($backgroundColor)
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    /**
     * @return string
     */
    public function getTextColor()
    {
        return $this->textColor;
    }

    /**
     * @param string $textColor
     *
     * @return $this
     */
    public function setTextColor($textColor)
    {
        $this->textColor = $textColor;

        return $this;
    }
}
