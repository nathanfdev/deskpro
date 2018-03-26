<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity as DeskPROEntity;
use DeskPRO\Bundle\ImportBundle\Model\AbstractCustomDef;
use DeskPRO\Bundle\ImportBundle\Model\CustomDefChoice;
use DeskPRO\Bundle\ImportBundle\Model\PrimaryImportModelInterface;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\CustomDefMapperInterface;

/**
 * Class AbstractCustomDefImporter.
 */
abstract class AbstractCustomDefHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     *
     * @param AbstractCustomDef $model
     */
    public function writeModel(PrimaryImportModelInterface $model, $brandName = null)
    {
        $entity = $this->findOrCreateCustomDef($model);
        $entity
            ->setTitle($model->getTitle())
            ->setDescription($model->getDescription())
            ->setWidgetType($model->getWidgetType())
            ->setIsEnabled($model->isEnabled())
            ->setIsUserEnabled($model->isUserEnabled())
            ->setIsAgentField($model->isAgentField())
            ->setDefaultValue($model->getDefaultValue())
        ;

        $this->persister->persistAndFlush($entity, $model);

        foreach ($model->getOptions() as $optionName => $optionValue) {
            if (is_string($optionName)) {
                $entity->setOption($optionName, $optionValue);
            }
        }

        // Create and update children
        $deleteChoices = function ($model, $parentId) use ($entity) {
            /* @var CustomDefChoice $model */
            $titles = array_map(function (CustomDefChoice $choice) {
                return $choice->getTitle();
            }, $model->getChoices());

            foreach ($entity->getChildren() as $choiceDef) {
                if ($choiceDef->getOption('parent_id') != $parentId) {
                    continue;
                }

                if (!in_array($choiceDef->getTitle(), $titles)) {
                    $entity->removeChild($choiceDef);
                    $this->persister->removeAndFlush($choiceDef);
                }
            }
        };

        $choiceIterator = function (CustomDefChoice $choiceModel, $parentDefId = 0) use ($entity, &$choiceIterator, &$deleteChoices) {
            $choiceDef = $entity->getChildren()
                ->filter(function (DeskPROEntity\CustomDefAbstract $choiceDef) use ($choiceModel, $parentDefId) {
                    return $choiceDef->getTitle() === $choiceModel->getTitle()
                    && $choiceDef->getOption('parent_id') == $parentDefId;
                })
                ->first()
            ;

            if (!$choiceDef) {
                $customDefClass = $this->getCustomDefMapper()->getEntityClass();

                /* @var DeskPROEntity\CustomDefAbstract $choiceDef */
                $choiceDef = new $customDefClass();
                $choiceDef->setTitle($choiceModel->getTitle());
                $choiceDef->setParent($entity);

                if ($parentDefId) {
                    $choiceDef->setOption('parent_id', $parentDefId);
                }

                $this->persister->persistAndFlush($choiceDef, $choiceModel);
                $entity->addChild($choiceDef);
            }

            foreach ($choiceModel->getChoices() as $subChoice) {
                $choiceIterator($subChoice, $choiceDef->getId());
            }

            $deleteChoices($choiceModel, $choiceDef->getId());
        };

        foreach ($model->getChoices() as $choice) {
            $choiceIterator($choice);
        }

        $deleteChoices($model, 0);

        return $entity;
    }

    /**
     * Find or create new custom def.
     *
     * @param PrimaryImportModelInterface $model
     *
     * @return DeskPROEntity\CustomDefFeedback
     */
    private function findOrCreateCustomDef(PrimaryImportModelInterface $model)
    {
        $entityId = $this->mappers->getImportMapMapper()->findIdByModel($model);
        if ($entityId) {
            $entity = $this->getCustomDefMapper()->findOneBy(['id' => $entityId]);
            if ($entity) {
                $this->logger->debug(sprintf('Found existing custom def, id=%s', $entityId));

                return $entity;
            }
        }

        $this->logger->debug('Creating a new custom def');
        $entityClass = $this->getCustomDefMapper()->getEntityClass();

        return new $entityClass();
    }

    /**
     * Returns custom def mapper.
     *
     * @return CustomDefMapperInterface
     */
    abstract protected function getCustomDefMapper();
}
