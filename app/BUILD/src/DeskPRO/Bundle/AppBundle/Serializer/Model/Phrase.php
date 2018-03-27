<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use Application\DeskPRO\Entity\Language as LanguageEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Phrase.
 */
class Phrase
{
    /**
     * The language this phrase belongs to.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Language>")
     *
     * @var Language
     */
    protected $language;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $value;

    /**
     * Constructor.
     *
     * @param LanguageEntity $language
     * @param string         $value
     */
    public function __construct(LanguageEntity $language, $value)
    {
        $this->language = $language;
        $this->value    = $value;
    }
}
