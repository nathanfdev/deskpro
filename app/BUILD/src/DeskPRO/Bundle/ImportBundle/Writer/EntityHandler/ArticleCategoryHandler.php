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

use Application\DeskPRO\Entity as DeskPROEntity;
use DeskPRO\Bundle\ImportBundle\Model;

/**
 * Article category importer.
 *
 * Class ArticleCategory
 */
class ArticleCategoryHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return Model\ArticleCategory::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param Model\ArticleCategory $model
     */
    public function writeModel(Model\PrimaryImportModelInterface $model, $brandName = null)
    {
        $entity = null;
        if ($model->getOid()) {
            $entity = $this->findArticleCategoryByOid($model);
        }
        if (!$entity) {
            $entity = $this->mappers->getArticleCategoryMapper()->findOneByTitle($model->getTitle());
            if ($entity) {
                $this->logger->debug("Found existing article category `{$model->getTitle()}` by oid");
            } else {
                $this->logger->debug("Creating new article category `{$model->getTitle()}`");
                $entity = new DeskPROEntity\ArticleCategory();
            }
        }

        $this->setCategoryProperties($model, $entity, $brandName);
        $this->persister->persistAndFlush($entity, $model);
        $this->createOrUpdateDeepCategories($entity, $model, $brandName);
    }

    /**
     * @param Model\AbstractArticleCategory $model
     *
     * @return DeskPROEntity\ArticleCategory
     */
    private function findArticleCategoryByOid(Model\AbstractArticleCategory $model)
    {
        $entityId = $this->mappers->getImportMapMapper()->findIdByModel($model);
        if ($entityId) {
            $entity = $this->mappers->getArticleCategoryMapper()->find($entityId);
            if ($entity) {
                $this->logger->debug("Found existing article category `{$model->getTitle()}` by oid");

                return $entity;
            }
        }

        return false;
    }

    /**
     * Create categories tree.
     *
     * @param DeskPROEntity\ArticleCategory $entity
     * @param Model\AbstractArticleCategory $model
     * @param string                        $brandName
     */
    private function createOrUpdateDeepCategories(DeskPROEntity\ArticleCategory $entity, Model\AbstractArticleCategory $model, $brandName)
    {
        $newTitles = [];
        $newIds    = [];

        // create and update sub categories
        foreach ($model->getCategories() as $subModel) {
            $subEntity = null;

            if ($subModel->getOid()) {
                $subEntity = $this->findArticleCategoryByOid($subModel);
                if ($subEntity) {
                    $this->logger->debug("Found article sub category `{$subModel->getTitle()}` by oid");
                    $newIds[] = $subEntity->getId();
                }
            }

            if (!$subEntity) {
                $subEntity = $entity->getChildren()
                    ->filter(function (DeskPROEntity\ArticleCategory $subEntity) use ($subModel) {
                        return $subEntity->getTitle() === $subModel->getTitle();
                    })
                    ->first()
                ;
                if ($subEntity) {
                    $this->logger->debug("Found article sub category `{$subModel->getTitle()}` by title");
                } else {
                    $this->logger->debug("Creating new article sub category `{$subModel->getTitle()}`");
                    $subEntity = new DeskPROEntity\ArticleCategory();
                }
            }

            $this->setCategoryProperties($subModel, $subEntity, $brandName);
            $entity->addChild($subEntity);
            $this->persister->persistAndFlush($subEntity, $subModel);
            $this->createOrUpdateDeepCategories($subEntity, $subModel, $brandName);

            $newTitles[] = $subModel->getTitle();
        }
    }

    /**
     * Set article category properties.
     *
     * @param Model\AbstractArticleCategory $model
     * @param DeskPROEntity\ArticleCategory $entity
     * @param string                        $brandName
     *
     * @return DeskPROEntity\ArticleCategory
     */
    private function setCategoryProperties(Model\AbstractArticleCategory $model, DeskPROEntity\ArticleCategory $entity, $brandName)
    {
        $entity
            ->setRealTitle($model->getTitle())
            ->setIsAgent($model->isAgent())
            ->setIsBook($model->isBook())
        ;

        if ($brandName) {
            // set specific brand for multi-brand helpdesks
            $brand = $this->mappers->getBrandMapper()->findByName($brandName);
            if ($brand) {
                $entity->setBrand($brand);
            }
        }

        if (!$entity->getBrand()) {
            $entity->setBrand($this->mappers->getBrandMapper()->findOneBy([])); // set first brand for now
        }

        $this->helpers->getUserGroupHelper()->updateUserGroupsByModel($model, $entity);

        return $entity;
    }
}
