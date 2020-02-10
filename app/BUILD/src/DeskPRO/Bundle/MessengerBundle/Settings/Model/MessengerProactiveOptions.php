<?php

namespace DeskPRO\Bundle\MessengerBundle\Settings\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class MessengerProactiveOptions.
 */
class MessengerProactiveOptions
{
    /**
     * A title.
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("greetingTitle")
     *
     * @var string
     */
    private $greetingTitle = 'Get in Touch';

    /**
     * A title.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $title = 'Start a conversation';

    /**
     * A short description summoned to help a user.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $description = 'Start a chat with one of our agents';

    /**
     * A text which will be shown on a button inside chat block.
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("buttonText")
     *
     * @var string
     */
    private $buttonText = 'Start a new conversation';

    /**
     * A placeholder which will be shown on an input inside chat block.
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("inputPlaceholder")
     *
     * @var string
     */
    private $inputPlaceholder = 'Type your message here';

    /**
     * @return string
     */
    public function getGreetingTitle()
    {
        return $this->greetingTitle;
    }

    /**
     * @param string $greetingTitle
     *
     * @return $this
     */
    public function setGreetingTitle($greetingTitle)
    {
        $this->greetingTitle = $greetingTitle;

        return $this;
    }

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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getButtonText()
    {
        return $this->buttonText;
    }

    /**
     * @param string $buttonText
     *
     * @return $this
     */
    public function setButtonText($buttonText)
    {
        $this->buttonText = $buttonText;

        return $this;
    }

    /**
     * @return string
     */
    public function getInputPlaceholder()
    {
        return $this->inputPlaceholder;
    }

    /**
     * @param string $inputPlaceholder
     *
     * @return $this
     */
    public function setInputPlaceholder($inputPlaceholder)
    {
        $this->inputPlaceholder = $inputPlaceholder;

        return $this;
    }
}
