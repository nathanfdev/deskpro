<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\FormBundle\Hierarchy;

use Application\AppBundle\Hierarchy\Hierarchy as BaseHierarchy;
use Application\AppBundle\Hierarchy\HierarchyFormatterInterface;
use Application\FormBundle\Form\ChoiceList\HierarchyChoiceList;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Represents a hierarchy.
 *
 * getChoiceList can be used directly in a form (choice type) and will use the hierarchy formatter to render options
 * and the ID of the entity (by default) as the value.
 */
class Hierarchy extends BaseHierarchy
{
    /**
     * @var bool
     */
    private $leaf_selections_only;

    public function __construct(array $root_nodes, HierarchyFormatterInterface $formatter = null, $node_id_path = null)
    {
        parent::__construct($root_nodes, $formatter, $node_id_path);
        $this->leaf_selections_only = false;
    }


    /**
     * @return HierarchyNode[]
     */
    public function getFlattened()
    {
        $collection = new ArrayCollection();
        foreach ($this->getRootNodes() as $root_node) {
            self::flatten($root_node, $collection);
        }

        return $collection;
    }

    /**
     * @param HierarchyNode   $node
     * @param ArrayCollection $append_to_collection
     * @return ArrayCollection|HierarchyNode[]
     */
    public static function flatten(HierarchyNode $node, ArrayCollection $append_to_collection = null)
    {
        if (!$append_to_collection) {
            $append_to_collection = new ArrayCollection();
        }

        $append_to_collection->add($node);

        foreach ($node as $child) {
            self::flatten($child, $append_to_collection);
        }

        return $append_to_collection;
    }

    /**
     * @return HierarchyChoiceList
     */
    public function getChoiceList()
    {
        $choices = array();
        $labels = array();

        if (!$this->leaf_selections_only) {
            /** @var HierarchyNode $node */
            foreach ($this->getFlattened() as $node) {
                $label = (string)$node;
                $key = $this->getNodeId($node);

                $choices[$key] = $key;
                $labels[$key] = $label;
            }

            return new HierarchyChoiceList($choices, $labels);
        }

        /** @var HierarchyNode $node */
        foreach ($this as $node) {
            if (count($node)) {
                $choices[(string)$node] = $node->getChoices();
                $labels[(string)$node] = $node->getLabels();
            } else {
                $choices[$this->getNodeId($node)] = $this->getNodeId($node);
                $labels[$this->getNodeId($node)] = (string)$node;
            }
        }

        return new HierarchyChoiceList($choices, $labels);
    }


    public function markOnlyLeafSelections()
    {
        $this->leaf_selections_only = true;
    }
}
 