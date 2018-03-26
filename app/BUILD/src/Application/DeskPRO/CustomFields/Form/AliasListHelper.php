<?php

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
    public function findAdminAlias(CustomDefAbstract $from)
    {
        $adminAlias = null;
        foreach ($from->getAliases() as $alias) {
            $isAdminAlias = true;
            if ($alias instanceof AbstractAlias) {
                $appInstance  = $alias->getAppInstance();
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
     * @param string            $newAdminAlias
     *
     * @return array|string[]
     */
    public function changeAdminAlias(CustomDefAbstract $field, $newAdminAlias = '')
    {
        $existingAdminAlias = null;
        $existingAliases    = [];

        /** @var CustomDefAbstract $field */
        foreach ($field->getAliases() as $aliasObject) {
            $alias = $aliasObject->getQualifiedName();

            $isAdminAlias = true;
            if ($aliasObject instanceof AbstractAlias) {
                $appInstance  = $aliasObject->getAppInstance();
                $isAdminAlias = empty($appInstance);
            }

            if ($isAdminAlias) {
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
