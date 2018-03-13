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
