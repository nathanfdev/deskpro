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

namespace DeskPRO\Bundle\ImportBundle\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Snippet.
 */
class TextSnippet implements PrimaryImportModelInterface
{
    use PrimaryImportModelTrait;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $person;

    /**
     * Text snippet category it belongs to.
     *
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @JMS\Type("string")
     */
    private $category;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @JMS\Type("string")
     */
    private $shortcutCode;

    /**
     * @var Translation[]
     *
     * @Assert\Count(min=1)
     * @Assert\Valid()
     *
     * @JMS\Type("array<DeskPRO\Bundle\ImportBundle\Model\Translation>")
     */
    private $titleTranslations = [];

    /**
     * @var Translation[]
     *
     * @Assert\Count(min=1)
     * @Assert\Valid()
     *
     * @JMS\Type("array<DeskPRO\Bundle\ImportBundle\Model\Translation>")
     */
    private $snippetTranslations = [];

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $isDraft = false;

    /**
     * @return string
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param string $person
     *
     * @return $this
     */
    public function setPerson($person)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     *
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return string
     */
    public function getShortcutCode()
    {
        return $this->shortcutCode;
    }

    /**
     * @param string $shortcutCode
     *
     * @return $this
     */
    public function setShortcutCode($shortcutCode)
    {
        $this->shortcutCode = $shortcutCode;

        return $this;
    }

    /**
     * @return Translation[]
     */
    public function getTitleTranslations()
    {
        return $this->titleTranslations;
    }

    /**
     * @param Translation[] $titleTranslations
     *
     * @return $this
     */
    public function setTitleTranslations(array $titleTranslations)
    {
        $this->titleTranslations = $titleTranslations;

        return $this;
    }

    /**
     * @param Translation $translation
     *
     * @return $this
     */
    public function addTitleTranslation(Translation $translation)
    {
        $this->titleTranslations[] = $translation;

        return $this;
    }

    /**
     * @return Translation[]
     */
    public function getSnippetTranslations()
    {
        return $this->snippetTranslations;
    }

    /**
     * @param Translation[] $snippetTranslations
     *
     * @return $this
     */
    public function setSnippetTranslations(array $snippetTranslations)
    {
        $this->snippetTranslations = $snippetTranslations;

        return $this;
    }

    /**
     * @param Translation $translation
     *
     * @return $this
     */
    public function addSnippetTranslation(Translation $translation)
    {
        $this->snippetTranslations[] = $translation;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDraft()
    {
        return $this->isDraft;
    }

    /**
     * @param bool $isDraft
     *
     * @return $this
     */
    public function setIsDraft($isDraft)
    {
        $this->isDraft = $isDraft;

        return $this;
    }
}
