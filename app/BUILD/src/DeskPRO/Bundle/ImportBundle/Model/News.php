<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Exporting news entity.
 *
 * Class News
 */
class News extends AbstractContentModel implements PersonAwareInterface, LabelAwareModelInterface
{
    use LabelAwareTrait;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $person;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $category;

    /**
     * {@inheritdoc}
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * {@inheritdoc}
     */
    public function setPerson($person)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * News category.
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set news category.
     *
     * @param string $category
     *
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = (string) $category;

        return $this;
    }
}
