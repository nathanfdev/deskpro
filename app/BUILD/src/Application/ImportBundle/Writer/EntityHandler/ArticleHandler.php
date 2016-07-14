<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Model;
use Application\ImportBundle\Writer\Helper\BlobAdapter;
use Application\ImportBundle\Writer\Helper\CustomDataHelper;
use Application\ImportBundle\Writer\Helper\LabelHelper;
use Application\ImportBundle\Writer\Mapper\MapperRegistry;
use Psr\Log\LoggerInterface;

/**
 * DeskPRO article importer.
 *
 * Class Article
 */
class ArticleHandler extends AbstractEntityHandler
{
    /**
     * @var BlobAdapter
     */
    private $blobAdapter;

    /**
     * Constructor.
     *
     * @param MapperRegistry  $mappers
     * @param LoggerInterface $logger
     * @param BlobAdapter     $blobAdapter
     */
    public function __construct(MapperRegistry $mappers, LoggerInterface $logger, BlobAdapter $blobAdapter)
    {
        parent::__construct($mappers, $logger);
        $this->blobAdapter = $blobAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return Model\Article::class;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(Model\ImportModelInterface $model, $entityId = null)
    {
        if (!$model instanceof Model\Article) {
            Model\UnexpectedException::throwUnexpectedEntityTypeException($model);
        }

        $entity = $this->findOrCreateArticle($entityId);
        $entity
            ->setTitle($model->getTitle())
            ->setContent($model->getContent())
            ->setStatus($model->getStatus())
            ->setPerson($this->mappers->getPersonMapper()->findOneByEmail($model->getPerson()))
            ->setLanguage($this->findLanguage($model->getLanguage()))
            ->setDateCreated($model->getDateCreated())
            ->setDatePublished($model->getDatePublished())
            ->setDateEnd($model->getDateEnd())
            ->setEndAction($model->getEndAction())
            ->setViewCount($model->getViewCount())
            ->resetCategories()
            ->resetAttachments()
        ;

        if ($entity->getId()) {
            $this->mappers->getArticleCommentMapper()->resetComments($entity->getId());
            $this->mappers->getObjectLangMapper()->removeBy('articles', $entity->getId());
        }

        foreach ($model->getCategories() as $category) {
            $entity->addToCategory($this->findOrCreateArticleCategory($category));
        }
        foreach ($model->getAttachments() as $attachment) {
            $attachment = $this->createAttachment($attachment, $model->getPerson());
            if ($attachment) {
                $entity->addAttachment($attachment);
            }
        }
        foreach ($model->getComments() as $comment) {
            $entity->addComment($this->createArticleComment($comment));
        }

        $labelsHelper = new LabelHelper($this->logger);
        $labelsHelper->updateLabels($model, $entity, DeskPROEntity\LabelArticle::class);

        $customDataHelper = new CustomDataHelper($this->mappers->getArticleCustomDefMapper(), $this->logger);
        $customDataHelper->updateCustomData($model, $entity, $this->records);

        foreach ($model->getUniqueTranslations() as $translation) {
            $this->addObjectLang($translation, $entity);
        }

        $this->records->setPrimaryEntity($entity);
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
        $article = $this->mappers->getArticleMapper()->findOneBy(['id' => $entity_id], false);
        if ($article) {
            $this->logger->debug(sprintf('Found existing article `%s`', $article->getRealTitle()));

            return $article;
        }

        $this->logger->debug('Creating new article');

        return new DeskPROEntity\Article();
    }

    /**
     * Creates an article comment entity.
     *
     * @param Model\ArticleComment $entity
     *
     * @return DeskPROEntity\ArticleComment
     */
    public function createArticleComment(Model\ArticleComment $entity)
    {
        $article_comment = new DeskPROEntity\ArticleComment();
        $article_comment
            ->setPerson($this->mappers->getPersonMapper()->findOneByEmail($entity->getPerson(), false))
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
     * @param Model\Attachment $entity
     * @param string           $personEmail
     *
     * @return DeskPROEntity\ArticleAttachment
     */
    private function createAttachment(Model\Attachment $entity, $personEmail)
    {
        $blob = $this->blobAdapter->createByBlob($entity, false);
        if (!$blob) {
            return false;
        }

        $person = $this->mappers->getPersonMapper()->findOneByEmail($entity->getPerson() ?: $personEmail, false);
        if (!$person) {
            return false;
        }

        $attachment = new DeskPROEntity\ArticleAttachment();
        $attachment
            ->setPerson($person)
            ->setBlob($blob)
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
            $category = $this->mappers->getArticleCategoryMapper()->findOneByTitle($title, false);
            if ($category) {
                $this->logger->debug(sprintf('Found existing article category `%s`', $category->getTitle()));
            } else {
                $category = new DeskPROEntity\ArticleCategory();
                $category->setRealTitle($title);

                $this->records->addRelatedEntity($category);
                $this->logger->info(sprintf('New article category creating `%s`', $category->getTitle()));
            }
        }

        return $category;
    }
}
