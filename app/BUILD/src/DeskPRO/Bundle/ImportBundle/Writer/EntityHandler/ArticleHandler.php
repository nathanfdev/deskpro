<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity;
use DeskPRO\Bundle\ImportBundle\Model;

/**
 * DeskPRO article importer.
 *
 * Class Article
 */
class ArticleHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return Model\Article::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param Model\Article $model
     */
    public function writeModel(Model\PrimaryImportModelInterface $model, $brandName = null)
    {
        /** @var Entity\Article $entity */
        $entity = $this->findOrCreateEntity($this->mappers->getArticleMapper(), $model);
        $entity
            ->setTitle($model->getTitle())
            ->setContent($model->getContent())
            ->setStatus($model->getStatus())
            ->setPerson($this->helpers->getPersonHelper()->findOrCreatePerson($model->getPerson()))
            ->setLanguage($this->helpers->getLanguageHelper()->findOrCreateLanguage($model->getLanguage()))
            ->setEndAction($model->getEndAction())
            ->setViewCount($model->getViewCount())
        ;

        if ($model->getDateCreated()) {
            $entity->setDateCreated($model->getDateCreated());
        }
        if ($model->getDatePublished()) {
            $entity->setDatePublished($model->getDatePublished());
        } else {
            $entity->setDatePublished($entity->getDateCreated());
        }
        if ($model->getDateEnd()) {
            $entity->setDateEnd($model->getDateEnd());
        }

        $this->helpers->getCustomDataHelper()->updateCustomData($this->mappers->getArticleCustomDefMapper(), $model, $entity);
        $this->helpers->getLabelHelper()->updateLabels($model, $entity, Entity\LabelArticle::class);
        $this->helpers->getTranslationHelper()->updateTranslations($model->getTitleTranslations(), $entity, 'title');
        $this->helpers->getTranslationHelper()->updateTranslations($model->getContentTranslations(), $entity, 'content');

        // update article categories
        foreach ($model->getCategories() as $categoryPath) {
            /** @var Entity\ArticleCategory $categoryEntity */
            $categoryEntity = $this->helpers->getCategoryHelper()->findOrCreateCategory(
                $this->mappers->getArticleCategoryMapper(),
                $categoryPath,
                $brandName
            );

            if ($entity->getCategories()->contains($categoryEntity)) {
                continue;
            }

            $entity->addToCategory($categoryEntity);
        }

        if (!$entity->getCategories()->count()) {
            // use default category
            $defaultCategory = $this->mappers->getArticleCategoryMapper()->getDefaultCategory();
            if ($defaultCategory) {
                $entity->addToCategory($defaultCategory);
            }
        }

        // persist basic entity
        $this->persister->persistAndFlush($entity, $model);

        // persist others related entities which contains own oids
        foreach ($model->getComments() as $commentModel) {
            $this->helpers->getCommentHelper()->createOrUpdateComment(
                $this->mappers->getArticleCommentMapper(), $commentModel, $entity
            );
        }
        foreach ($model->getAttachments() as $attachmentModel) {
            $this->helpers->getAttachmentHelper()->createOrUpdateAttachment(
                $this->mappers->getArticleAttachmentMapper(), $attachmentModel, $entity
            );
        }
    }
}
