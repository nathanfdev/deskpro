<?php

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
