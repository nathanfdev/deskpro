<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use Application\DeskPRO\Entity\Language as LanguageEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * Class CustomDefTranslation.
 */
class CustomDefTranslation
{
    /**
     * Language.
     *
     * @var int
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Language>")
     */
    private $language;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $title;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $description;

    /**
     * Constructor.
     *
     * @param string         $title
     * @param string         $description
     * @param LanguageEntity $language
     */
    public function __construct($title, $description, LanguageEntity $language)
    {
        $this->title       = $title;
        $this->description = $description;
        $this->language    = $language;
    }
}
