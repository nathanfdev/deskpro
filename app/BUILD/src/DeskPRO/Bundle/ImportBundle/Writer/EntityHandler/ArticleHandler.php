<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
