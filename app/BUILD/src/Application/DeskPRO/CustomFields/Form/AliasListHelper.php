<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\CustomFields\Form;

use Application\DeskPRO\Entity\CustomDefAbstract;
use DeskPRO\Bundle\AppBundle\Entity\ObjectAlias\AbstractAlias;

class AliasListHelper
{
    public function findAdminAlias( CustomDefAbstract $from)
    {
        $adminAlias = null;
        foreach ($from->getAliases() as $alias) {

            $isAdminAlias = true;
            if ($alias instanceof AbstractAlias) {
                $appInstance = $alias->getAppInstance();
                $isAdminAlias = empty($appInstance);
            }

            if ($isAdminAlias) {
                if (is_null($adminAlias)) {
                    $adminAlias = $alias->getQualifiedName();
                } else {
                    $msg = sprintf(
                        'Found more than one admin alias for field id: %s: %s',
                        $from->getId(), implode(', ', [$adminAlias, $alias->getQualifiedName()])
                    );
                    throw new \RuntimeException($msg);
                }
            }
        }

        return $adminAlias;
    }

    /**
     * @param CustomDefAbstract $field
     * @param string $newAdminAlias
     * @return array|string[]
     */
    public function changeAdminAlias(CustomDefAbstract $field, $newAdminAlias = '')
    {
        $existingAdminAlias = null;
        $existingAliases = [];

        /** @var CustomDefAbstract $field */
        foreach ($field->getAliases() as $aliasObject) {
            $alias = $aliasObject->getQualifiedName();

            $isAdminAlias = true;
            if ($aliasObject instanceof AbstractAlias) {
                $appInstance = $aliasObject->getAppInstance();
                $isAdminAlias = empty($appInstance);
            }

            if($isAdminAlias) {
                if (is_null($existingAdminAlias)) {
                    $existingAdminAlias = $alias;
                } else {
                    $msg = sprintf(
                        'Found more than one admin aliases for field id: %s: %s',
                        $field->getId(), implode(', ', [$existingAdminAlias, $alias])
                    );
                    throw new \RuntimeException($msg);
                }
            } else {
                $existingAliases[] = $alias;
            }
        }

        return empty($newAdminAlias) ? $existingAliases : array_merge([$newAdminAlias], $existingAliases);
    }
}
