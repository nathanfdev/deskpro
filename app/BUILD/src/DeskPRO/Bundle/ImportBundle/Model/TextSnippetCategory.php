<?php

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
