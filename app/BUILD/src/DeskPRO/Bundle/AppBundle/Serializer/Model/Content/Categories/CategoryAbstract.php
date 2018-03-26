<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Categories;

use Application\DeskPRO\Entity\CategoryAbstract as CategoryAbstractEntity;
use Application\DeskPRO\Entity\Phrase;
use JMS\Serializer\Annotation as JMS;

/**
 * Class CategoryAbstract.
 */
abstract class CategoryAbstract
{
    /**
     * The unique id of the category.
     *
     * @var int
     *
     * @JMS\Groups("list")
     * @JMS\Type("integer")
     */
    protected $id;

    /**
     * Category`s title.
     *
     * @JMS\Type("string")
     * @JMS\Groups("list")
     *
     * @var string
     */
    protected $title;

    /**
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Serializer\Model\Phrase>")
     *
     * @var Phrase[]
     */
    protected $titleTranslations;

    /**
     * Category`s slug.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $slug;

    /**
     * Display order.
     *
     * @JMS\Type("integer")
     * @JMS\Groups("product")
     *
     * @var int
     */
    protected $displayOrder = 0;

    /**
     * Constructor.
     *
     * @param CategoryAbstractEntity $entity
     */
    public function __construct(CategoryAbstractEntity $entity)
    {
        $this->id           = $entity->getId();
        $this->title        = $entity->getTitle();
        $this->slug         = $entity->getSlug();
        $this->displayOrder = $entity->getDisplayOrder();
    }

    /**
     * @param \Application\DeskPRO\Entity\Phrase[] $titleTranslations
     *
     * @return $this
     */
    public function setTitleTranslations(array $titleTranslations)
    {
        $this->titleTranslations = $titleTranslations;

        return $this;
    }
}
