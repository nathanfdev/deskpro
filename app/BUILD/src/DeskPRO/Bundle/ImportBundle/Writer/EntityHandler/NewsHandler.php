<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity;
use DeskPRO\Bundle\ImportBundle\Model;

/**
 * DeskPRO news importer.
 *
 * Class News
 */
class NewsHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return Model\News::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param Model\News $model
     */
    public function writeModel(Model\PrimaryImportModelInterface $model, $brandName = null)
    {
        /** @var Entity\News $entity */
        $entity = $this->findOrCreateEntity($this->mappers->getNewsMapper(), $model);
        $entity
            ->setTitle($model->getTitle())
            ->setContent($model->getContent())
            ->setStatus($model->getStatus())
            ->setPerson($this->helpers->getPersonHelper()->findOrCreatePerson($model->getPerson()))
            ->setLanguage($this->helpers->getLanguageHelper()->findOrCreateLanguage($model->getLanguage()))
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

        // update news category
        if ($model->getCategory()) {
            $entity->setCategory($this->helpers->getCategoryHelper()->findOrCreateCategory(
                $this->mappers->getNewsCategoryMapper(),
                $model->getCategory(),
                $brandName
            ));
        } else {
            // use default category
            $entity->setCategory($this->mappers->getNewsCategoryMapper()->getDefaultCategory());
        }

        $this->helpers->getLabelHelper()->updateLabels($model, $entity, Entity\LabelNews::class);

        // persist basic entity
        $this->persister->persistAndFlush($entity, $model);
    }
}
