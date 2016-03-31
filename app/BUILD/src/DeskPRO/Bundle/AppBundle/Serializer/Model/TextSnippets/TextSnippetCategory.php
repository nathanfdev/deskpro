<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
