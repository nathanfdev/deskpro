<?php

namespace DeskPRO\Bundle\MessengerBundle\Settings\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class MessengerOptionsTickets.
 */
class MessengerOptionsTickets
{
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
     * A text which will be shown on a button inside tickets block.
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("buttonText")
     *
     * @var string
     */
    private $buttonText = 'Start a new conversation';

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
}
