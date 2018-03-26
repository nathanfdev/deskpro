<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\EntityRepository\ArticleCategory as ArticleCategoryRepository;
use DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration\PortalLinkRoute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * @PortalLinkRoute("portal_kb_browse", route_param_map={"slug":"slug"})
 * @PortalLinkRoute("portal_kb_article_category_toggle_subscription", route_param_map={"slug":"slug"}, type="toggle_subscription")
 */
class ArticleCategory extends CategoryAbstract
{
    /**
     * @var ArticleCategory
     */
    protected $parent;

    /**
     * @var ArrayCollection|ArticleCategory[]
     */
    protected $children;

    /**
     * Articles belongs this category.
     *
     * @var ArrayCollection|Article[]
     */
    protected $articles;

    /**
     * Usergroups that has access to this category.
     *
     * @var ArrayCollection|Usergroup[]
     */
    protected $usergroups;

    /**
     * Brand linked to the category.
     *
     * @var Brand
     */
    protected $brand;

    /**
     * If this is true, then all categories and articles under this one
     * are considered agent KB articles and wont be displayed in
     * the user interface.
     *
     * @var bool
     */
    protected $is_agent = false;

    /**
     * If this is true, then all the articles and categories under this category
     * is treated as a book (aka manual).
     *
     * @var bool
     */
    protected $is_book = false;

    /**
     * The template suffix to use when rendering the category, and articles within
     * the category.
     *
     * Eg UserBundle:Articles:article.html.twig
     * With suffix 'download' becomes
     * UserBundle:Articles:article-download.html.twig
     *
     * @var string
     */
    protected $template_suffix = '';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->articles   = new ArrayCollection();
        $this->usergroups = new ArrayCollection();
        $this->children   = new ArrayCollection();
    }

    /**
     * @return bool
     */
    public function isAgent()
    {
        return $this->is_agent;
    }

    /**
     * @param bool $is_agent
     *
     * @return $this
     */
    public function setIsAgent($is_agent)
    {
        $this->setModelField('is_agent', (bool) $is_agent);

        return $this;
    }

    /**
     * @return bool
     */
    public function isBook()
    {
        return $this->is_book;
    }

    /**
     * @param bool $is_book
     *
     * @return $this
     */
    public function setIsBook($is_book)
    {
        $this->setModelField('is_book', (bool) $is_book);

        return $this;
    }

    /**
     * @return ArrayCollection|Usergroup[]
     */
    public function getUserGroups()
    {
        return $this->usergroups;
    }

    /**
     * @param Usergroup $usergroup
     */
    public function addUsergroup(Usergroup $usergroup)
    {
        if (!$this->usergroups->contains($usergroup)) {
            $this->usergroups->add($usergroup);
        }
    }

    /**
     * @return Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param Brand $brand
     *
     * @return $this
     */
    public function setBrand($brand)
    {
        $this->setModelField('brand', $brand);

        return $this;
    }

    /**
     * @param ArticleCategory $category
     */
    public function addChild(ArticleCategory $category)
    {
        if (!$this->children->contains($category)) {
            $category->setParent($this);
            $this->children->add($category);
        }
    }

    /**
     * @return mixed
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * @return string
     */
    public function getTemplateSuffix()
    {
        return $this->template_suffix;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = ArticleCategoryRepository::class;
        $metadata->setPrimaryTable(['name' => 'article_categories']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(
            [
                'fieldName'  => 'is_agent',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_agent',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_book',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_book',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'template_suffix',
                'type'       => 'string',
                'length'     => 100,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'template_suffix',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'id',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'id',
                'id'         => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'title',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'title',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'slug',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'slug',
                'unique'     => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'display_order',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'display_order',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'depth',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'depth',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'root',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'root',
            ]
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'parent',
                'targetEntity' => self::class,
                'mappedBy'     => null,
                'inversedBy'   => 'children',
                'joinColumns'  => [
                    [
                        'name'                 => 'parent_id',
                        'referencedColumnName' => 'id',
                        'onDelete'             => 'set null',
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'children',
                'targetEntity' => self::class,
                'mappedBy'     => 'parent',
                'orderBy'      => ['display_order' => 'ASC'],
            ]
        );
        $metadata->mapManyToMany(
            [
                'fieldName'    => 'usergroups',
                'targetEntity' => Usergroup::class,
                'cascade'      => ['persist', 'merge'],
                'joinTable'    => [
                    'name'        => 'article_category2usergroup',
                    'schema'      => null,
                    'joinColumns' => [
                        [
                            'name'                 => 'category_id',
                            'referencedColumnName' => 'id',
                            'nullable'             => true,
                            'onDelete'             => 'cascade',
                            'columnDefinition'     => null,
                        ],
                    ],
                    'inverseJoinColumns' => [
                        [
                            'name'                 => 'usergroup_id',
                            'referencedColumnName' => 'id',
                            'nullable'             => true,
                            'onDelete'             => 'cascade',
                            'columnDefinition'     => null,
                        ],
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToMany(
            [
                'fieldName'    => 'articles',
                'targetEntity' => Article::class,
                'mappedBy'     => 'categories',
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'brand',
                'targetEntity' => Brand::class,
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    [
                        'name'                 => 'brand_id',
                        'referencedColumnName' => 'id',
                        'onDelete'             => 'set null',
                    ],
                ],
                'dpApi' => true,
            ]
        );
    }
}
