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

namespace Application\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity\CustomDataAbstract;
use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\ImportBundle\Model\CustomDataOwnerModelInterface;
use Application\ImportBundle\Writer\EntityHandler\DoctrineEntities;
use Application\ImportBundle\Writer\Mapper\AbstractCustomDefMapper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Psr\Log\LoggerInterface;

/**
 * Class CustomDataHelper.
 */
class CustomDataHelper
{
    /**
     * @var AbstractCustomDefMapper
     */
    private $mapper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param AbstractCustomDefMapper $mapper
     * @param LoggerInterface         $logger
     */
    public function __construct(AbstractCustomDefMapper $mapper, LoggerInterface $logger)
    {
        $this->mapper = $mapper;
        $this->logger = $logger;
    }

    /**
     * Returns custom def person entity.
     *
     * @param CustomDataOwnerModelInterface $model
     * @param mixed                         $entity
     * @param DoctrineEntities              $entities
     */
    public function updateCustomData(CustomDataOwnerModelInterface $model, $entity, DoctrineEntities $entities)
    {
        $updatedDefs = new ArrayCollection();
        $defClass    = $this->mapper->getEntityClass();

        // prepare new custom definitions
        foreach ($model->getCustomFields() as $fieldModel) {
            $criteria = [
                'title'  => $fieldModel->getKey(),
                'parent' => null,
            ];

            $customDef = $this->mapper->findOneBy($criteria, false);
            if (!$customDef) {
                /** @var CustomDefAbstract $customDef */
                $customDef = new $defClass();
                $customDef->setHandlerClass(CustomDefAbstract::HANDLER_CLASS_TEXT);
                $customDef->setTitle($fieldModel->getKey());

                $entities->addRelatedEntity($customDef);
            }

            if ($customDef->isChoiceType()) {
                $choiceDef = $this->mapper->findChoiceCustomDef($fieldModel->getValue(), $customDef);
                if (!$choiceDef) {
                    /** @var CustomDefAbstract $choiceDef */
                    $choiceDef = new $defClass();
                    $customDef->addChild($choiceDef);
                    $entities->addRelatedEntity($choiceDef);
                }
            }

            if (in_array($customDef->getTypeName(), [CustomDefAbstract::TYPE_DISPLAY, CustomDefAbstract::TYPE_HIDDEN])) {
                continue;
            }

            $updatedDefs->add($customDef);
        }

        // update custom data
        foreach ($model->getCustomFields() as $fieldModel) {
            $customDef = $updatedDefs->matching(new Criteria(Criteria::expr()->eq('title', $fieldModel->getKey())))->first();
            if (!$customDef) {
                continue;
            }
            if (!$fieldModel->getValue()) {
                continue;
            }

            /** @var CustomDataAbstract[]|ArrayCollection $customDefData */
            if ($customDef->isChoiceType()) {
                $choiceDef     = $this->mapper->findChoiceCustomDef($fieldModel->getValue(), $customDef);
                $customDefData = $entity->getCustomData()->filter(function (CustomDataAbstract $customData) use ($choiceDef) {
                    return $customData->field === $choiceDef;
                });

                if (!$customDefData->count()) {
                    $customData = $customDef->createCustomData();
                    $customData->setField($choiceDef);
                    $customData->setValue(1);

                    $entity->addCustomData($customDef);
                }
            } else {
                $customDefData = $entity->getCustomData()->filter(function (CustomDataAbstract $customData) use ($customDef) {
                    return $customData->root_field === $customDef && null !== $customData->field;
                });

                if ($customDefData->first()) {
                    $customData = $customDefData->first();
                } else {
                    $customData = $customDef->createCustomData();
                    $entity->addCustomData($customData);
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
    }
}
