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

namespace DeskPRO\Bundle\AppBundle\Form\Hierarchy;

use DeskPRO\Component\Hierarchy\HierarchyNode;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;

/**
 * Class HierarchyChoiceLoader.
 */
class HierarchyChoiceLoader implements ChoiceLoaderInterface
{
    /**
     * @var Hierarchy
     */
    private $hierarchy;

    /**
     * @var ArrayChoiceList|null
     */
    private $list;

    /**
     * HierarchyChoiceLoader constructor.
     *
     * @param Hierarchy $hierarchy
     */
    public function __construct(Hierarchy $hierarchy)
    {
        $this->hierarchy = $hierarchy;
    }

    /**
     * {@inheritdoc}
     */
    public function loadChoiceList($value = null)
    {
        if (!$this->list) {
            $nodes      = $this->hierarchy->getFlattened()->toArray();
            $this->list = new ArrayChoiceList($nodes, function ($value) {
                $value = $value instanceof HierarchyNode ? (string) $this->hierarchy->getNodeId($value) : $value;

                return $value;
            });
        }

        return $this->list;
    }

    /**
     * {@inheritdoc}
     */
    public function loadValuesForChoices(array $choices, $value = null)
    {
        return $this->loadChoiceList()->getValuesForChoices($choices);
    }

    /**
     * {@inheritdoc}
     */
    public function loadChoicesForValues(array $values, $value = null)
    {
        return $this->loadChoiceList()->getChoicesForValues($values);
    }

    public function getHierarchy()
    {
        return $this->hierarchy;
    }
}
