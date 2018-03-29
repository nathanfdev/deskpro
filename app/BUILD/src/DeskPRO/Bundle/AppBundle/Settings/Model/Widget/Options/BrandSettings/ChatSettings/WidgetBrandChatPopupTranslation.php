<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\ChatSettings;

use DeskPRO\Bundle\AppBundle\Settings\Model\AbstractTranslationModel;
use JMS\Serializer\Annotation as JMS;

/**
 * Class WidgetBrandChatPopupTranslation.
 */
class WidgetBrandChatPopupTranslation extends AbstractTranslationModel
{
    /**
     * Translation title.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $title;

    /**
     * Translation message.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $message;

    /**
     * Translation title.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $heading;

    /**
     * Translation message.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $subheading;

    /**
     * Message at start button.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $startButton;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getHeading()
    {
        return $this->heading;
    }

    /**
     * @param string $heading
     *
     * @return $this
     */
    public function setHeading($heading)
    {
        $this->heading = $heading;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubheading()
    {
        return $this->subheading;
    }

    /**
     * @param string $subheading
     *
     * @return $this
     */
    public function setSubheading($subheading)
    {
        $this->subheading = $subheading;

        return $this;
    }

    /**
     * @return string
     */
    public function getStartButton()
    {
        return $this->startButton;
    }

    /**
     * @param string $startButton
     *
     * @return $this
     */
    public function setStartButton($startButton)
    {
        $this->startButton = $startButton;

        return $this;
    }
}
