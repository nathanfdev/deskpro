<?php

namespace DeskPRO\Bundle\MessengerBundle\Settings\Model;

use Application\DeskPRO\Entity\Language;
use JMS\Serializer\Annotation as JMS;

/**
 * Class MessengerTranslation.
 */
class MessengerTranslation
{
    /**
     * @JMS\Type("Application\DeskPRO\Entity\Language")
     *
     * @var Language
     */
    private $language;

    /**
     * @var string
     * @JMS\Type("string")
     */
    private $text;

    /**
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param Language $language
     *
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     *
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }
}
