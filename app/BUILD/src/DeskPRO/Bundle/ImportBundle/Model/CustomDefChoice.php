<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CustomDefChoice.
 */
class CustomDefChoice
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @var CustomDefChoice[]
     *
     * @JMS\Type("array<DeskPRO\Bundle\ImportBundle\Model\CustomDefChoice>")
     *
     * @Assert\Valid()
     */
    private $choices = [];

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return CustomDefChoice[]
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @param CustomDefChoice[] $choices
     */
    public function setChoices($choices)
    {
        $this->choices = $choices;
    }

    /**
     * @param CustomDefChoice $choice
     *
     * @return $this
     */
    public function addChoice(CustomDefChoice $choice)
    {
        $this->choices[] = $choice;

        return $this;
    }
}
