<?php

namespace DeskPRO\Bundle\MessengerBundle\Settings\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class MessengerStyles.
 */
class MessengerStyles
{
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
     * Primary colour of messenger.
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("primaryColor")
     *
     * @var string
     */
    private $primaryColor = '#3d88f3';

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
}
