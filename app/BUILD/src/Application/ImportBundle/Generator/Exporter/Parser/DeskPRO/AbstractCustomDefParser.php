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

namespace Application\ImportBundle\Generator\Exporter\Parser\DeskPRO;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Entity;

/**
 * Class AbstractCustomDefParser.
 */
abstract class AbstractCustomDefParser extends AbstractParser
{
    /**
     * Returns custom def entity.
     *
     * @param DeskPROEntity\CustomDefAbstract $custom_def
     *
     * @return Entity\AbstractCustomDef
     */
    protected function exportCustomDef(DeskPROEntity\CustomDefAbstract $custom_def)
    {
        $entity = $this->getDefaultCustomDefEntity();
        $entity
            ->setRawData($custom_def->toArray($custom_def::TOARRAY_DEEP))
            ->setOid($custom_def->getId())
            ->setDestination($entity->getDestinationPrefix().$custom_def->getId())
            ->setImportMapKey($this->getImportMapKey())
            ->setTitle($custom_def->getRealTitle())
            ->setDescription($custom_def->getRealDescription())
            ->setOptions($custom_def->getAllOptions())
            ->setAsEnabled($custom_def->isEnabled())
            ->setAsUserEnabled($custom_def->isUserEnabled())
            ->setAsAgentField($custom_def->isAgentField())
            ->setDefaultValue($custom_def->getDefaultValue())
            ->setHandlerClass($custom_def->getHandlerClass())
        ;

        foreach ($custom_def->getAllChildren() as $child) {
            $entity->addCustomDef($this->exportCustomDef($child));
        }

        return $entity;
    }

    /**
     * Returns empty custom def entity.
     *
     * @return Entity\AbstractCustomDef
     */
    abstract protected function getDefaultCustomDefEntity();

    /**
     * Returns import map key.
     *
     * @return string
     */
    abstract protected function getImportMapKey();
}
