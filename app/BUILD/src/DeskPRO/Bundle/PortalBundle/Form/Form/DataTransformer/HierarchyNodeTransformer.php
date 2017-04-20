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

namespace DeskPRO\Bundle\PortalBundle\Form\Form\DataTransformer;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Component\Hierarchy\HierarchyNode;
use DeskPRO\Component\Util\EntityUtils;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class HierarchyNodeTransformer.
 */
class HierarchyNodeTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (!$value) {
            return $value;
        }

        if (!is_array($value)) {
            if ($value instanceof DomainObject || $value instanceof EntityInterface) {
                $value = EntityUtils::getIdentifier($value);
            }
            $ret = $value instanceof HierarchyNode ? $value->getId() : $value;

            return $ret;
        }

        $ret = [];
        foreach ($value as $item) {
            if ($value instanceof DomainObject || $value instanceof EntityInterface) {
                $value = EntityUtils::getIdentifier($value);
            }
            $ret[] = $item instanceof HierarchyNode ? $item->getId() : $item;
        }

        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return $value;
        }

        if (!is_array($value)) {
            if (!$value instanceof HierarchyNode) {
                throw new TransformationFailedException('Expected HierarchyNode');
            }

            return $value->getData();
        }

        $items = [];
        foreach ($value as $item) {
            if (!$item instanceof HierarchyNode) {
                throw new TransformationFailedException('Expected HierarchyNode');
            }
            $items[] = $item->getData();
        }

        return $items;
    }
}
