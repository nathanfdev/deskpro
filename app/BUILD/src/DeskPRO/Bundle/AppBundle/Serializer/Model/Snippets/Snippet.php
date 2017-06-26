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

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Snippets;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\AppBundle\Entity\Snippet as SnippetEntity;
use DeskPRO\Bundle\AppBundle\Entity\SnippetLabel;
use DeskPRO\Bundle\AppBundle\Entity\SnippetTranslation;
use JMS\Serializer\Annotation as JMS;

class Snippet
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
     * This snippet title.
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
     * The types of the snippet.
     *
     * @JMS\Type("array<string>")
     *
     * @var string[]
     */
    private $types;

    /**
     * Shortcut for this snippet.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $shortcutCode;

    /**
     * The labels associated with the snippet.
     *
     * @JMS\Type("array<label<DeskPRO\Bundle\AppBundle\Entity\SnippetLabel>>")
     *
     * @var SnippetLabel[]
     */
    private $labels;

    /**
     * The content translations.
     *
     * @JMS\Type("collection<entity<DeskPRO\Bundle\AppBundle\Entity\SnippetTranslation>>")
     *
     * @var SnippetTranslation[]
     */
    private $translations;

    /**
     * Is this just a draft?
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $isDraft;

    /**
     * Is this just a draft?
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $isOwnershipGlobal;

    /**
     * Is this just a draft?
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $isVisibleGlobal;

    /**
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\AgentTeam>>")
     *
     * @var AgentTeam[]
     */
    private $ownershipTeams;

    /**
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\Department>>")
     *
     * @var Department[]
     */
    private $visibleDepartments;

    public function __construct(SnippetEntity $snippet)
    {
        $this->id                 = $snippet->getId();
        $this->title              = $snippet->getTitle();
        $this->person             = $snippet->getPerson();
        $this->types              = $snippet->getTypes();
        $this->shortcutCode       = $snippet->getShortcutCode();
        $this->isDraft            = $snippet->isDraft();
        $this->translations       = $snippet->getTranslations();
        $this->isOwnershipGlobal  = $snippet->isOwnershipGlobal();
        $this->isVisibleGlobal    = $snippet->isVisibleGlobal();
        $this->ownershipTeams     = $snippet->getOwnershipTeams();
        $this->visibleDepartments = $snippet->getVisibleDepartments();
        $this->labels             = $snippet->getLabels();
    }
}
