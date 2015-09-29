<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\ImportBundle\Generator\Writer\DeskPRO\Importer;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Entity;

/**
 * DeskPRO article importer.
 *
 * Class Article
 */
final class Article extends AbstractImporter
{
    /**
     * @var BlobAdapterInterface
     */
    private $blob_adapter;

    /**
     * Constructor.
     *
     * @param Mapper\Collection    $mappers
     * @param BlobAdapterInterface $blob_adapter
     */
    public function __construct(Mapper\Collection $mappers, BlobAdapterInterface $blob_adapter)
    {
        parent::__construct($mappers);
        $this->blob_adapter = $blob_adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_ARTICLE;
    }

    /**
     * {@inheritdoc}
     *
     * todo add referred objects
     * $record['total_rating'] = $kbval->total_rating;
     * $record['num_ratings']  = $kbval->num_ratings;
     */
    public function prepare(Entity\EntityInterface $entity, $entity_id = null)
    {
        if (!$entity instanceof Entity\Article) {
            Entity\UnexpectedException::throwUnexpectedEntityTypeException($entity);
        }

        $article = $this->findOrCreateArticle($entity_id);
        $article
            ->setTitle($entity->getTitle())
            ->setContent($entity->getContent())
            ->setSlug($entity->getSlug())
            ->setStatus($entity->getStatus())
            ->setPerson($this->getPersonMapper()->findOneByEmail($entity->getPersonEmail()))
            ->setLanguage($this->findLanguage($entity->getLanguage()))
            ->setDateCreated($entity->getDateCreated())
            ->setDatePublished($entity->getDatePublished())
            ->setDateEnd($entity->getDateEnd())
            ->setEndAction($entity->getEndAction())
            ->setViewsCount($entity->getViewCount())
            ->resetCustomData()
            ->resetLabels()
            ->resetCategories()
            ->resetAttachments()
        ;

        if ($article->getId()) {
            $this->getArticleCommentMapper()->resetComments($article->getId());
            $this->getObjectLangMapper()->removeBy('articles', $article->getId());
        }

        foreach ($entity->getCategories() as $category) {
            $article->addToCategory($this->findOrCreateArticleCategory($category));
        }
        foreach ($entity->getCustomFields() as $custom_field) {
            $custom_field = $this->createArticleCustomData($custom_field);
            if ($custom_field) {
                $article->addCustomData($custom_field);
            }
        }
        foreach ($entity->getAttachments() as $attachment) {
            $article->addAttachment($this->createAttachment($attachment, $entity->getPersonEmail()));
        }
        foreach ($entity->getComments() as $comment) {
            $article->addComment($this->createArticleComment($comment));
        }

        $this->records->setPrimaryEntity($article);
    }

    /**
     * Returns an article by oid
     * Creates a new article if not found.
     *
     * @param int $entity_id
     *
     * @return DeskPROEntity\Article
     */
    protected function findOrCreateArticle($entity_id)
    {
        $article = $this->getArticleMapper()->findOneBy(array('id' => $entity_id), false);
        if ($article) {
            $this->logDebug(sprintf('Found existing article `%s`', $article->getRealTitle()));

            return $article;
        }

        $this->logDebug('Creating new article');

        return new DeskPROEntity\Article();
    }

    /**
     * Creates an article comment entity.
     *
     * @param Entity\ArticleComment $entity
     *
     * @return DeskPROEntity\ArticleComment
     */
    public function createArticleComment(Entity\ArticleComment $entity)
    {
        $article_comment = new DeskPROEntity\ArticleComment();
        $article_comment
            ->setPerson($this->getPersonMapper()->findOneByEmail($entity->getPersonEmail()))
            ->setContent($entity->getContent())
            ->setStatus($entity->getStatus())
            ->setDateCreated($entity->getDateCreated())
        ;

        $this->records->addRelatedEntity($article_comment);

        return $article_comment;
    }

    /**
     * Returns the importing DeskPRO doctrine article attachment entity.
     *
     * @param Entity\Attachment $entity
     * @param string            $person_email
     *
     * @return DeskPROEntity\ArticleAttachment
     */
    private function createAttachment(Entity\Attachment $entity, $person_email)
    {
        $email      = $entity->getPersonEmail() ?: $person_email;
        $attachment = new DeskPROEntity\ArticleAttachment();
        $attachment
            ->setPerson($this->getPersonMapper()->findOneByEmail($email))
            ->setBlob($this->blob_adapter->createByBlob($entity))
        ;

        return $attachment;
    }

    /**
     * Returns an article category by title
     * Creates a new article category if not found.
     *
     * @param string $title
     *
     * @throws \Exception
     *
     * @return DeskPROEntity\ArticleCategory|null
     */
    private function findOrCreateArticleCategory($title)
    {
        $category = null;
        if ($title) {
            $category = $this->getArticleCategoryMapper()->findOneByTitle($title, false);
            if ($category) {
                $this->logDebug(sprintf('Found existing article category `%s`', $category->getTitle()));
            } else {
                $category = new DeskPROEntity\ArticleCategory();
                $category->setRealTitle($title);

                $this->records->addRelatedEntity($category);
                $this->logInfo(sprintf('New article category creating `%s`', $category->getTitle()));
            }
        }

        return $category;
    }

    /**
     * Returns custom def article entity.
     *
     * @param Entity\CustomField $entity
     *
     * @throws ImporterException
     *
     * @return DeskPROEntity\CustomDataArticle
     */
    private function createArticleCustomData(Entity\CustomField $entity)
    {
        return $this->createCustomData($this->getArticleCustomDefMapper(), $entity, new DeskPROEntity\CustomDataArticle());
    }

    /**
     * Returns the article comment mapper.
     *
     * @throws \Exception
     *
     * @return Mapper\ArticleComment
     */
    private function getArticleCommentMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_ARTICLE_COMMENT);
    }
}
