<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity;
use DeskPRO\Bundle\ImportBundle\Model;

/**
 * DeskPRO download importer.
 *
 * Class Download
 */
class DownloadHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return Model\Download::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param Model\Download $model
     */
    public function writeModel(Model\PrimaryImportModelInterface $model, $brandName = null)
    {
        /** @var Entity\Download $entity */
        $entity = $this->findOrCreateEntity($this->mappers->getDownloadMapper(), $model);
        $entity
            ->setTitle($model->getTitle())
            ->setContent($model->getContent())
            ->setStatus($model->getStatus())
            ->setPerson($this->helpers->getPersonHelper()->findOrCreatePerson($model->getPerson()))
            ->setLanguage($this->helpers->getLanguageHelper()->findOrCreateLanguage($model->getLanguage()))
            ->setViewCount($model->getViewCount())
            ->setNumDownloads($model->getNumDownloads())
        ;

        if ($model->getDateCreated()) {
            $entity->setDateCreated($model->getDateCreated());
        }
        if ($model->getDatePublished()) {
            $entity->setDatePublished($model->getDatePublished());
        } else {
            $entity->setDatePublished($entity->getDateCreated());
        }

        // update download category
        if ($model->getCategory()) {
            $entity->setCategory($this->helpers->getCategoryHelper()->findOrCreateCategory(
                $this->mappers->getDownloadCategoryMapper(),
                $model->getCategory(),
                $brandName
            ));
        } else {
            // use default category
            $entity->setCategory($this->mappers->getDownloadCategoryMapper()->getDefaultCategory());
        }

        // update download blob
        if ($model->getBlob()) {
            $entity->setBlob($this->helpers->getBlobAdapter()->createByBlob($model->getBlob(), false));
        } else {
            $entity->setBlob(null);
        }

        $this->helpers->getLabelHelper()->updateLabels($model, $entity, Entity\LabelDownload::class);

        // persist basic entity
        $this->persister->persistAndFlush($entity, $model);
    }
}
