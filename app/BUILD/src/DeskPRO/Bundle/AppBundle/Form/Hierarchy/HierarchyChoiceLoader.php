<?php

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
}
