<?php

/**
 * DeskPRO.
 *
 * @category Entities
 *
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * History of slugs.
 */
class ArticleSlugHistory extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id;

    /**
     * The content.
     *
     * @var Article
     */
    protected $article;

    /**
     * The slug.
     *
     * @var string
     */
    protected $slug;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @param Article $article  the content
     * @param string  $old_slug the slug to put in history
     */
    public function __construct(Article $article, $old_slug)
    {
        $this->setContent($article);
        $this->setSlug($old_slug);
        $this->setModelField('date_created', new \DateTime());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->article;
    }

    /**
     * @param Article $article
     */
    public function setContent(Article $article)
    {
        $this->setModelField('article', $article);
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->setModelField('slug', $slug);
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ArticleSlugHistory';
        $metadata->setPrimaryTable(['name' => 'articles_slug_history']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(
            [
                'fieldName'  => 'date_created',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'date_created',
            ]
        );
        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'slug',
            'type'       => 'string',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'slug',
            'unique'     => true,
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'article',
            'targetEntity' => 'Application\DeskPRO\Entity\Article',
            'cascade'      => [
                'remove',
                'persist',
                'merge',
            ],
            'inversedBy'  => 'slug_history',
            'joinColumns' => [
                [
                    'name'                 => 'article_id',
                    'referencedColumnName' => 'id',
                    'onDelete'             => 'cascade',
                    'nullable'             => false,
                ],
            ],
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
