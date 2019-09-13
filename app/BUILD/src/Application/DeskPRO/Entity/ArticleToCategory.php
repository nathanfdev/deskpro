<?php

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Class ArticleToCategory
 *
 * @package Application\DeskPRO\Entity
 */
class ArticleToCategory extends DomainObject
{
    /**
     * @var Article
     */
    private $article;

    /**
     * @var ArticleCategory
     */
    private $category;

    /**
     * @var int
     */
    private $display_order = 0;

    /**
     * @param Article $article
     * @param ArticleCategory $category
     * @param int $displayOrder
     * @return ArticleToCategory
     */
    public static function create(Article $article, ArticleCategory $category, $displayOrder = 0)
    {
        return (new self)
            ->setArticle($article)
            ->setCategory($category)
            ->setDisplayOrder($displayOrder)
        ;
    }

    /**
     * @return Article
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * @param Article $article
     * @return ArticleToCategory
     */
    public function setArticle(Article $article)
    {
        $this->setModelField('article', $article);

        return $this;
    }

    /**
     * @return ArticleCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param ArticleCategory $category
     * @return ArticleToCategory
     */
    public function setCategory(ArticleCategory $category)
    {
        $this->setModelField('category', $category);

        return $this;
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->display_order;
    }

    /**
     * @param int $display_order
     * @return ArticleToCategory
     */
    public function setDisplayOrder($display_order)
    {
        $this->setModelField('display_order', $display_order);

        return $this;
    }

    /**
     * @param ClassMetadata $metadata
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setPrimaryTable([
            'name' => 'article_to_categories',
        ]);

        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);

        $metadata->mapField([
            'fieldName' => 'display_order',
            'type' => 'integer',
            'precision' => 0,
            'scale' => 0,
            'nullable' => false,
            'columnName' => 'display_order',
        ]);

        $metadata->mapManyToOne([
            'id' => true,
            'fieldName' => 'article',
            'targetEntity' => Article::class,
            'inversedBy' => 'categories',
            'joinColumns' => [
                [
                    'name' => 'article_id',
                    'referencedColumnName' => 'id',
                    'nullable' => false,
                    'onDelete' => 'CASCADE',
                    'columnDefinition' => null,
                ],
            ],
        ]);

        $metadata->mapManyToOne([
            'id' => true,
            'fieldName' => 'category',
            'targetEntity' => ArticleCategory::class,
            'inversedBy' => 'articles',
            'joinColumns' => [
                [
                    'name' => 'category_id',
                    'referencedColumnName' => 'id',
                    'nullable' => false,
                    'onDelete' => 'CASCADE',
                    'columnDefinition' => null,
                ],
            ],
        ]);
    }
}
