<?php

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class KbSubscription extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @var \Application\DeskPRO\Entity\ArticleCategory
     */
    protected $category;

    /**
     * @var \Application\DeskPRO\Entity\Article
     */
    protected $article;

    /**
     * @var bool
     */
    protected $root_category;

    /**
     * Used with root_category when user subscribed to all brand root category.
     * Ideally we can remove root_category and just check brand but to support backward compatibility leave it
     *
     * @var \Application\DeskPRO\Entity\Brand
     */
    protected $rootCategoryBrand;

    /**
     * @param ArticleCategory $category
     */
    public function setCategory(ArticleCategory $category = null)
    {
        if ($category) {
            $this->setModelField('article', null);
        }

        $this->setModelField('category', $category);
    }

    /**
     * @param Article $article
     */
    public function setArticle(Article $article = null)
    {
        if ($article) {
            $this->setModelField('category', null);
        }

        $this->setModelField('article', $article);
    }

    /**
     * @return bool
     */
    public function isRootCategory()
    {
        return $this->root_category;
    }

    /**
     * @deprecated use setRootCategoryBrand instead
     *
     * @param bool $root_category
     */
    public function setRootCategory($root_category)
    {
        $this->setModelField('root_category', $root_category);
    }

    /**
     * @param \Application\DeskPRO\Entity\Brand $brand
     */
    public function setRootCategoryBrand(Brand $brand = null)
    {
        $this->setModelField('rootCategoryBrand', $brand);
        $this->setRootCategory($brand !== null);
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\KbSubscription';
        $metadata->setPrimaryTable(
            [
                'name'    => 'kb_subscriptions',
                'indexes' => [
                    'root_category_idx' => ['columns' => ['root_category']],
                ],
            ]
        );
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);

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
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'person',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'person_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'article',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Article',
                'mappedBy'     => null,
                'inversedBy'   => 'comment',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'article_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'category',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\ArticleCategory',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'category_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );

        $metadata->mapField(
            [
                'fieldName'  => 'root_category',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'root_category',
            ]
        );

        $metadata->mapManyToOne(
            [
                'fieldName'    => 'rootCategoryBrand',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Brand',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'root_category_brand_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
    }
}
