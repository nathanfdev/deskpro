<?php

namespace DeskPRO\Bundle\MessengerBundle\Settings\Model;

use Application\DeskPRO\Entity\Blob;
use JMS\Serializer\Annotation as JMS;

/**
 * Class MessengerSettingsWidget.
 */
class MessengerWidget
{
    const POSITION_RIGHT = 'right';
    const POSITION_LEFT  = 'left';

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
    private $backgroundColor = '#f7f7f7';

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
     * @var Blob
     * @JMS\Type("entity<Application\Deskpro\Entity\Blob>")
     */
    private $icon;

    /**
     * Widget position
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $position = self::POSITION_RIGHT;

    /**
     * Widget position
     *
     * @JMS\Type("boolean")
     *
     * @var string
     */
    private $copyfree = false;

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

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $position
     *
     * @return $this
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return Blob
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param Blob $icon
     *
     * @return $this
     */
    public function setIcon($icon = null)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return string
     */
    public function getCopyfree()
    {
        return $this->copyfree;
    }

    /**
     * @param string $copyfree
     *
     * @return $this
     */
    public function setCopyfree($copyfree)
    {
        $this->copyfree = $copyfree;

        return $this;
    }
}
