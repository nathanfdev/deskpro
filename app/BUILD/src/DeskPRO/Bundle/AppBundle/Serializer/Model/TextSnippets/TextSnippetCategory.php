<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\TextSnippets;

use Application\DeskPRO\Entity\TextSnippetCategory as TextSnippetCategoryEntity;
use JMS\Serializer\Annotation as JMS;

class TextSnippetCategory
{
    /**
     * The unique snippet category ID.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * Snippet category title (it's localized).
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $title;

    /**
     * Person - owner of this snippet category.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    private $person;

    /**
     * Is this snippet category global?
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $isGlobal;

    /**
     * TextSnippet constructor.
     *
     * @param TextSnippetCategoryEntity $snippet
     * @param string                    $title
     */
    public function __construct(TextSnippetCategoryEntity $snippet, $title)
    {
        $this->id       = $snippet->getId();
        $this->title    = $title;
        $this->person   = $snippet->getPerson();
        $this->isGlobal = $snippet->getIsGlobal();
    }
}
