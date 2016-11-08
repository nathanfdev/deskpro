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

namespace DeskPRO\Bundle\AppBundle\Form\DataTransformer;

use DeskPRO\Component\Hierarchy\HierarchyNode;
use Symfony\Component\Form\ChoiceList\LazyChoiceList;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface;

/**
 * Class CustomDefHierarchyNodeTransformer.
 */
class CustomDefHierarchyNodeTransformer implements DataTransformerInterface
{
    /**
     * @var ChoiceListInterface
     */
    private $choiceList;

    /**
     * Constructor.
     *
     * @param LazyChoiceList $choiceList
     */
    public function __construct(LazyChoiceList $choiceList)
    {
        $this->choiceList = $choiceList;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (!$value) {
            return '';
        }

        if (!is_array($value)) {
            return $value instanceof HierarchyNode ? $value : $this->findChoiceForValue($value);
        }

        $items = [];
        foreach ($value as $item) {
            $items[] = $item instanceof HierarchyNode ? $item : $this->findChoiceForValue($item);
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return '';
        }

        if (!is_array($value)) {
            return $value instanceof HierarchyNode ? (string) $value->getData()->getId() : $value;
        }

        $items = [];
        foreach ($value as $item) {
            $items[] = $item instanceof HierarchyNode ? (string) $item->getData()->getId() : $item;
        }

        return $items;
    }

    /**
     * @param $value
     *
     * @return HierarchyNode
     */
    protected function findChoiceForValue($value)
    {
        /** @var HierarchyNode $choice */
        foreach ($this->choiceList->getChoices() as $choice) {
            if ($value == $choice->getData()->getId()) {
                return $choice;
            }
        }

        return false;
    }
}
