<?php

namespace DeskPRO\Bundle\MessengerBundle\Settings\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class MessengerProactive.
 */
class MessengerProactive
{
    const STYLE_AVATAR_TEXT_BUTTON = 'avatar-text-button';
    const STYLE_AVATAR_TEXT_INPUT  = 'avatar-text-input';
    const STYLE_AVATAR_BUTTON      = 'avatar-button';
    const STYLE_TEXT_BUTTON        = 'text-button';
    const STYLE_TEXT_INPUT         = 'text-input';
    const STYLE_AVATAR_WIDGET      = 'avatar-widget';

    /**
     * Indicates whenever messenger window should be risen automatically.
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("autoStart")
     *
     * @var bool
     */
    private $autoStart = false;

    /**
     * Indicates the timeout (in seconds) when the messenger window should be risen automatically.
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("autoStartTimeout")
     *
     * @var int
     */
    private $autoStartTimeout = 0;

    /**
     * Indicates the style when the messenger window is risen automatically.
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("autoStartStyle")
     *
     * @var string
     */
    private $autoStartStyle = self::STYLE_AVATAR_TEXT_BUTTON;

    /**
     * Advanced proactive options.
     *
     * @JMS\Type("DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerProactiveOptions")
     * @Assert\Valid()
     *
     * @var MessengerProactiveOptions
     */
    private $options;

    /**
     * @return bool
     */
    public function isAutoStart()
    {
        return $this->autoStart;
    }

    /**
     * @param bool $autoStart
     *
     * @return $this
     */
    public function setAutoStart($autoStart)
    {
        $this->autoStart = (bool) $autoStart;

        return $this;
    }

    /**
     * @return bool
     */
    public function getAutoStartTimeout()
    {
        return $this->autoStartTimeout;
    }

    /**
     * @param int $timeout
     *
     * @return $this
     */
    public function setAutoStartTimeout($timeout)
    {
        $this->autoStartTimeout = (int) $timeout;

        return $this;
    }

    /**
     * @return string
     */
    public function getAutoStartStyle()
    {
        return $this->autoStartStyle;
    }

    /**
     * @param string $style
     *
     * @return $this
     */
    public function setAutoStartStyle($style)
    {
        $this->autoStartStyle = $style;

        return $this;
    }

    /**
     * @return MessengerProactiveOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param MessengerProactiveOptions $options
     *
     * @return $this
     */
    public function setOptions(MessengerProactiveOptions $options)
    {
        $this->options = $options;

        return $this;
    }
}
