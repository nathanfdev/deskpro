<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity\CustomDataAbstract;
use Application\DeskPRO\Entity\CustomDefAbstract;
use DeskPRO\Bundle\ImportBundle\Model\AbstractCustomDef;
use DeskPRO\Bundle\ImportBundle\Model\CustomDataAwareModelInterface;
use DeskPRO\Bundle\ImportBundle\Model\CustomField;
use DeskPRO\Bundle\ImportBundle\Writer\EntityPersister;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\CustomDefMapperInterface;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\ImportMapMapper;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;

/**
 * Class CustomDataHelper.
 */
class CustomDataHelper
{
    /**
     * @var ImportMapMapper
     */
    private $importMapMapper;

    /**
     * @var EntityPersister
     */
    private $persister;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param ImportMapMapper $importMapMapper
     * @param EntityPersister $persister
     * @param LoggerInterface $logger
     */
    public function __construct(ImportMapMapper $importMapMapper, EntityPersister $persister, LoggerInterface $logger)
    {
        $this->importMapMapper = $importMapMapper;
        $this->persister       = $persister;
        $this->logger          = $logger;
    }

    /**
     * Returns custom def person entity.
     *
     * @param CustomDefMapperInterface      $mapper
     * @param CustomDataAwareModelInterface $model
     * @param mixed                         $customDataOwner
     */
    public function updateCustomData(CustomDefMapperInterface $mapper, CustomDataAwareModelInterface $model, $customDataOwner)
    {
        foreach ($model->getCustomFields() as $fieldModel) {
            if (!$fieldModel->getValue()) {
                continue;
            }
            if (!is_string($fieldModel->getValue())) {
                throw new \RuntimeException('Custom field value should be a string');
            }

            $customDef = $this->findOrCreateCustomDef($mapper, $fieldModel);
            if (!$customDef) {
                continue;
            }

            if ($customDef->isChoiceType()) {
                $this->updateChoiceCustomData($customDef, $fieldModel, $customDataOwner);
            } else {
                $this->updateSingleCustomData($customDef, $fieldModel, $customDataOwner);
            }
        }
    }

    /**
     * @param CustomDefMapperInterface $mapper
     * @param CustomField              $fieldModel
     *
     * @return CustomDefAbstract|null
     */
    private function findOrCreateCustomDef(CustomDefMapperInterface $mapper, CustomField $fieldModel)
    {
        $customDefModelClass = $mapper->getModelClass();

        /** @var AbstractCustomDef $customDefModel */
        $customDefModel = new $customDefModelClass();
        $customDefModel->setOid($fieldModel->getOid() ?: $fieldModel->getName());
        $customDefModel->setTitle($fieldModel->getName() ?: 'Custom field '.$fieldModel->getOid());

        $customDef = null;

        // try to get custom def by oid
        if ($fieldModel->getOid()) {
            $entityId = $this->importMapMapper->findIdByModel($customDefModel);
            if ($entityId) {
                $this->logger->debug('Found existing custom def id by OID');
                $customDef = $mapper->find($entityId);
            }

            // just oid was provided (no name), try to get by field name fallback
            if (!$customDef && !$fieldModel->getName()) {
                $customDef = $mapper->findOneBy([
                    'title'  => 'Custom field '.$fieldModel->getOid(),
                    'parent' => null,
                ]);
            }
        }

        // try to get custom def by title
        if (!$customDef) {
            $customDef = $mapper->findOneBy([
                'title'  => $fieldModel->getName(),
                'parent' => null,
            ]);

            if ($customDef) {
                $this->logger->debug('Found existing custom def id by title');
            }
        }

        if ($customDef) {
            if ($customDef->getParent()) {
                $this->logger->warning(
                    "Unable to set data to child custom def {$mapper->getEntityClass()} ".
                    "`{$fieldModel->getOid()}` `{$fieldModel->getName()}`"
                );

                return;
            }
        } else {
            // no custom def found, create a new one
            $customDefEntityClass = $mapper->getEntityClass();

            /** @var CustomDefAbstract $customDef */
            $customDef = new $customDefEntityClass();
            $customDef->setTitle($customDefModel->getTitle());
            $customDef->setWidgetType(CustomDefAbstract::TYPE_TEXT);

            $this->persister->persistAndFlush($customDef, $customDefModel);
        }

        return $customDef;
    }

    /**
     * @param CustomDefAbstract $customDef
     * @param CustomField       $fieldModel
     * @param mixed             $customDataOwner
     */
    private function updateChoiceCustomData(CustomDefAbstract $customDef, CustomField $fieldModel, $customDataOwner)
    {
        /** @var CustomDataAbstract[]|ArrayCollection $allCustomData */
        $allCustomData = $customDataOwner->getCustomData();
        $newChoiceIds  = [];

        $choicePaths = explode(',', $fieldModel->getValue());
        foreach ($choicePaths as $choicePath) {
            // get choice def
            $choiceDef = null;

            $choicePath = explode('>', $choicePath);
            $choicePath = array_map('trim', $choicePath);

            $parentDefId = null;
            foreach ($choicePath as $choiceName) {
                $choiceDef = $customDef->getChildren()->filter(function (CustomDefAbstract $choiceDef) use ($choiceName, $parentDefId) {
                    return $choiceDef->getTitle() == $choiceName && $choiceDef->getOption('parent_id') == $parentDefId;
                })->first();

                if (!$choiceDef) {
                    $this->logger->debug("Choice def was not found for {$customDef->getTitle()}, create a new one");
                    $choiceDefClass = get_class($customDef);

                    /** @var CustomDefAbstract $choiceDef */
                    $choiceDef = new $choiceDefClass();
                    $choiceDef->setParent($customDef);
                    $choiceDef->setOption('parent_id', $parentDefId);
                    $choiceDef->setTitle($choiceName);

                    $this->persister->persistAndFlush($choiceDef);
                }

                $parentDefId = $choiceDef->getId();
            }

            $customDefData = $allCustomData->filter(function (CustomDataAbstract $customData) use ($customDef, $choiceDef) {
                return $customData->root_field === $customDef && $customData->field === $choiceDef;
            });

            if (!$customDefData->count()) {
                $customData = $customDef->createCustomData();
                $customData->setField($choiceDef);
                $customData->setValue(1);

                $customDataOwner->addCustomData($customData);
            }

            // remember current choice to delete
            $newChoiceIds[] = $choiceDef->getId();
        }

        foreach ($allCustomData as $customData) {
            if ($customData->root_field === $customDef && !in_array($customData->getFieldId(), $newChoiceIds)) {
                $allCustomData->removeElement($customData);
            }
        }
    }

    /**
     * @param CustomDefAbstract $customDef
     * @param CustomField       $fieldModel
     * @param mixed             $customDataOwner
     */
    private function updateSingleCustomData(CustomDefAbstract $customDef, CustomField $fieldModel, $customDataOwner)
    {
        /** @var CustomDataAbstract[]|ArrayCollection $customDefData */
        $customDefData = $customDataOwner->getCustomData()->filter(function (CustomDataAbstract $customData) use ($customDef) {
            return $customData->root_field === $customDef && null !== $customData->field;
        });

        if ($customDefData->first()) {
            $customData = $customDefData->first();
        } else {
            $customData = $customDef->createCustomData();
            $customDataOwner->addCustomData($customData);
        }

        switch ($customDef->getTypeName()) {
            case CustomDefAbstract::TYPE_TEXT:
            case CustomDefAbstract::TYPE_TEXTAREA:
                $customData->setInput($fieldModel->getValue());
                break;
            case CustomDefAbstract::TYPE_TOGGLE:
                $customData->setValue($fieldModel->getValue() ? 1 : 0);
                break;
            case CustomDefAbstract::TYPE_DATE:
            case CustomDefAbstract::TYPE_DATETIME:
                $customData->setValue(strtotime($fieldModel->getValue()));
                break;
        }
    }
}
