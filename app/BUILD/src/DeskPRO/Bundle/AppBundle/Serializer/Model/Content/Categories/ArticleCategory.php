<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Categories;

use Application\DeskPRO\Entity\ArticleCategory as ArticleCategoryEntity;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Usergroup;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ArticleCategory.
 */
class ArticleCategory extends CategoryAbstract
{
    /**
     * @JMS\Groups("articles_categories")
     * @JMS\Type("entity<Application\DeskPRO\Entity\ArticleCategory>")
     *
     * @var \Application\DeskPRO\Entity\ArticleCategory
     */
    protected $parent;

    /**
     * @JMS\Groups("articles_categories")
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\ArticleCategory>>")
     *
     * @var \Application\DeskPRO\Entity\ArticleCategory[]
     */
    protected $children;

    /**
     * Usergroups that has access to this category.
     *
     * @JMS\Groups("articles_categories")
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\Usergroup>>")
     *
     * @var Usergroup[]
     */
    protected $usergroups;

    /**
     * Brand linked to the category.
     *
     * @JMS\Groups("articles_categories")
     * @JMS\Type("entity<Application\DeskPRO\Entity\Brand>")
     *
     * @var Brand
     */
    protected $brand;

    /**
     * If this is true, then all categories and articles under this one
     * are considered agent KB articles and wont be displayed in
     * the user interface.
     *
     * @JMS\Groups("articles_categories")
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $isAgent = false;

    /**
     * If this is true, then all the articles and categories under this category
     * is treated as a book (aka manual).
     *
     * @JMS\Groups("articles_categories")
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $isBook = false;

    /**
     * The template suffix to use when rendering the category, and articles within
     * the category.
     *
     * @JMS\Groups("articles_categories")
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $templateSuffix = '';

    /**
     * Constructor.
     *
     * @param ArticleCategoryEntity $entity
     */
    public function __construct(ArticleCategoryEntity $entity)
    {
        parent::__construct($entity);

        $this->parent         = $entity->getParent();
        $this->children       = $entity->getChildren();
        $this->usergroups     = $entity->getUserGroups();
        $this->brand          = $entity->getBrand();
        $this->isAgent        = $entity->isAgent();
        $this->isBook         = $entity->isBook();
        $this->templateSuffix = $entity->getTemplateSuffix();
    }
}
