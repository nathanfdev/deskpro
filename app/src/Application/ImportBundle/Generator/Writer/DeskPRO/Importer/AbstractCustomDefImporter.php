<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\ImportBundle\Generator\Writer\DeskPRO\Importer;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Entity\AbstractCustomDef;

/**
 * Class AbstractCustomDefImporter.
 */
abstract class AbstractCustomDefImporter extends AbstractImporter
{
    /**
     * Set custom def properties.
     *
     * @param DeskPROEntity\CustomDefAbstract $custom_def
     * @param AbstractCustomDef               $entity
     *
     * @return DeskPROEntity\CustomDefAbstract
     */
    protected function setCustomDef(DeskPROEntity\CustomDefAbstract $custom_def, AbstractCustomDef $entity)
    {
        $custom_def
            ->setTitle($entity->getTitle())
            ->setDescription($entity->getDescription())
            ->setHandlerClass($entity->getHandlerClass())
            ->setOptions($entity->getOptions())
            ->setIsEnabled($entity->isEnabled())
            ->setIsUserEnabled($entity->isUserEnabled())
            ->setIsAgentField($entity->isAgentField())
            ->setDefaultValue($entity->getDefaultValue())
        ;

        if (!$entity->getChildren()->hasImportMapKey()) {
            $custom_def->resetChildren();

            foreach ($entity->getChildren() as $child_entity) {
                $custom_def_class = get_class($custom_def);
                $custom_def->addChild($this->setCustomDef(new $custom_def_class(), $child_entity));
            }
        } else {
            // Create and update children
            foreach ($entity->getChildren() as $child_entity) {
                $exist_child = $this->getCustomDefMapper()->findOneBy(array('entity' => $child_entity), false);
                if ($exist_child) {
                    $this->setCustomDef($exist_child, $child_entity);
                } else {
                    $custom_def_class = get_class($custom_def);
                    $child_custom_def = $this->setCustomDef(new $custom_def_class(), $child_entity);

                    $custom_def->addChild($child_custom_def);
                    $this->records->addImportMapEntity(new Mapper\OidEntityMap($child_entity, $child_custom_def));
                }
            }

            // Remove deleted children
            $new_titles = array_map(
                function (AbstractCustomDef $entity) {
                    return $entity->getTitle();
                },
                $entity->getChildren()->toArray()
            );

            foreach ($custom_def->getAllChildren() as $child_custom_def) {
                if (!in_array($child_custom_def->getTitle(), $new_titles)) {
                    $custom_def->removeChild($child_custom_def);
                }
            }
        }

        return $custom_def;
    }

    /**
     * Returns custom def mapper.
     *
     * @return Mapper\AbstractCustomDefMapper
     */
    abstract protected function getCustomDefMapper();
}
