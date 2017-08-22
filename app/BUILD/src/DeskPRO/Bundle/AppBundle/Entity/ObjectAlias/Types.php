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

namespace DeskPRO\Bundle\AppBundle\Entity\ObjectAlias;

use Doctrine\ORM;

class Types
{
    /**
     * @param {string} $objectType the fully qualified class name for the desired object
     * @param ORM\EntityManager $entityManager
     * @return string
     */
    public static function aliasTypeForObjectType($objectType, ORM\EntityManager $entityManager)
    {
        $metadata = $entityManager->getClassMetadata(AbstractAlias::class);
        if (empty($metadata->subClasses)) {
            return null;
        }

        $resolution = [];
        foreach ($metadata->subClasses as $className) {
            if (Types::isAliasFor($className, $objectType)) {
                $resolution[] = $className;
            }
        }

        if (0 === count($resolution)) {
            return null;
        }

        if (1 === count($resolution)) {
            return $resolution[0];
        }

        throw new \DomainException('Ambiguous alias resolution');
    }

    private static function isAliasFor($aliasType, $objectType)
    {
        /** @var \ReflectionMethod $setObjectMethod */
        $setObjectMethod = null;

        $reflectionClass = new \ReflectionClass($aliasType);
        if ($reflectionClass->hasMethod('setObject')) {
            $setObjectMethod = $reflectionClass->getMethod('setObject');
        }

        $declaredType = null;
        if ($setObjectMethod && $setObjectMethod->getNumberOfParameters() === 1) {
            $parameter = $setObjectMethod->getParameters()[0];
            try {
                $declaredType = $parameter->getClass()->getName();
            } catch (\Exception $e) { $declaredType = null; }
        }

        if (!is_null($declaredType) && $declaredType === $objectType) {
            return true;
        }

        return false;
    }
}
