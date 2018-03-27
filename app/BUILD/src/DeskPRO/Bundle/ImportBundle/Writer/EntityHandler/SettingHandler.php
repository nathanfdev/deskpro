<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity;
use DeskPRO\Bundle\ImportBundle\Model;

/**
 * Class SettingHandler.
 */
class SettingHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return Model\Setting::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param Model\Setting $model
     */
    public function writeModel(Model\PrimaryImportModelInterface $model, $brandName = null)
    {
        $entity = $this->mappers->getSettingMapper()->findOneBy(['name' => $model->getName()]);
        if ($entity) {
            $this->logger->debug("Found existing setting `{$model->getName()}`");
        } else {
            $this->logger->debug("Import a new setting `{$model->getName()}`");

            $entity = new Entity\Setting();
            $entity->setName($model->getName());
        }

        $entity->setValue($model->getValue());

        // persist basic entity
        $this->persister->persistAndFlush($entity, $model);
    }
}
