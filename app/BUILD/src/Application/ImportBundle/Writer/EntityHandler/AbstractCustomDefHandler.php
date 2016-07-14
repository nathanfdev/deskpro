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

namespace Application\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Model\AbstractCustomDef;
use Application\ImportBundle\Writer\Mapper\AbstractCustomDefMapper;
use Application\ImportBundle\Writer\Mapper\OidEntityMap;

/**
 * Class AbstractCustomDefImporter.
 */
abstract class AbstractCustomDefHandler extends AbstractEntityHandler
{
    /**
     * Set custom def properties.
     *
     * @param DeskPROEntity\CustomDefAbstract $def
     * @param AbstractCustomDef               $entity
     *
     * @return DeskPROEntity\CustomDefAbstract
     */
    protected function setCustomDef(DeskPROEntity\CustomDefAbstract $def, AbstractCustomDef $entity)
    {
        $def
            ->setTitle($entity->getTitle())
            ->setDescription($entity->getDescription())
            ->setHandlerClass($entity->getWidgetType())
            ->setOptions($entity->getOptions())
            ->setIsEnabled($entity->isEnabled())
            ->setIsUserEnabled($entity->isUserEnabled())
            ->setIsAgentField($entity->isAgentField())
            ->setDefaultValue($entity->getDefaultValue())
        ;

        // Create and update children
        foreach ($entity->getChildren() as $child_entity) {
            $exist_child = $this->getCustomDefMapper()->findOneBy(['entity' => $child_entity], false);
            if ($exist_child) {
                $this->setCustomDef($exist_child, $child_entity);
            } else {
                $custom_def_class = get_class($def);
                $child_custom_def = $this->setCustomDef(new $custom_def_class(), $child_entity);

                $def->addChild($child_custom_def);
                $this->records->addImportMapEntity(new OidEntityMap($child_entity, $child_custom_def));
            }
        }

        // Remove deleted children
        $new_titles = array_map(
            function (AbstractCustomDef $entity) {
                return $entity->getTitle();
            },
            $entity->getChildren()
        );

        foreach ($def->getAllChildren() as $child_custom_def) {
            if (!in_array($child_custom_def->getTitle(), $new_titles)) {
                $def->removeChild($child_custom_def);
            }
        }

        return $def;
    }

    /**
     * Returns custom def mapper.
     *
     * @return AbstractCustomDefMapper
     */
    abstract protected function getCustomDefMapper();
}
