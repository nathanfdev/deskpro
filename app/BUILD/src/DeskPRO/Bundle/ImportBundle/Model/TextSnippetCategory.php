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
 * Class SnippetCategory.
 */
class TextSnippetCategory implements PrimaryImportModelInterface
{
    use PrimaryImportModelTrait;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $person;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Choice(choices={"tickets", "chat"})
     *
     * @JMS\Type("string")
     */
    private $typename;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $isGlobal = false;

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
    public function getTypename()
    {
        return $this->typename;
    }

    /**
     * @param string $typename
     *
     * @return $this
     */
    public function setTypename($typename)
    {
        $this->typename = $typename;

        return $this;
    }

    /**
     * @return bool
     */
    public function isGlobal()
    {
        return $this->isGlobal;
    }

    /**
     * @param bool $isGlobal
     *
     * @return $this
     */
    public function setIsGlobal($isGlobal)
    {
        $this->isGlobal = $isGlobal;

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
}
