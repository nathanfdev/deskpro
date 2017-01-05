<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
