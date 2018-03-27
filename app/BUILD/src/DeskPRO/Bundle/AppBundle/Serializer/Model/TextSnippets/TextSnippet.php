<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\TextSnippets;

use Application\DeskPRO\Entity\TextSnippet as TextSnippetEntity;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\InlineCustomSideload;
use JMS\Serializer\Annotation as JMS;

/**
 * Class TextSnippet.
 */
class TextSnippet
{
    /**
     * The unique snippet ID.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * Snippet title (it's localized).
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $title;

    /**
     * Person - owner of this snippet.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    private $person;

    /**
     * Category this snippet belongs to.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\TextSnippetCategory>")
     *
     * @var \Application\DeskPRO\Entity\TextSnippetCategory
     */
    private $category;

    /**
     * Shortcut for this snippet.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $shortcutCode;

    /**
     * Is this just a draft?
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $isDraft;

    /**
     * @JMS\Type("raw")
     *
     * @var InlineCustomSideload
     */
    private $textSnippetContent;

    /**
     * TextSnippet constructor.
     *
     * @param TextSnippetEntity $snippet
     * @param string            $title
     */
    public function __construct(TextSnippetEntity $snippet, $title)
    {
        $this->id           = $snippet->getId();
        $this->title        = $title;
        $this->person       = $snippet->getPerson();
        $this->category     = $snippet->getCategory();
        $this->shortcutCode = $snippet->getShortcutCode();
        $this->isDraft      = $snippet->isDraft();
    }

    /**
     * @return InlineCustomSideload
     */
    public function getTextSnippetContent()
    {
        return $this->textSnippetContent;
    }

    /**
     * @param InlineCustomSideload $textSnippetContent
     */
    public function setTextSnippetContent($textSnippetContent)
    {
        $this->textSnippetContent = $textSnippetContent;
    }
}
