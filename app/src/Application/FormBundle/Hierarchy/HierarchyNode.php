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

use Application\AppBundle\Hierarchy\HierarchyNode as BaseNode;

/**
 * A HierarchyNode is iteratable, and countable, because each can have an arbitray number of children.
 */
class HierarchyNode extends BaseNode
{
    /**
     * Recursively get a choices array for a form ChoiceList (only leaf values can be selected, the others are opt groups)
     *
     * @return array
     */
    public function getChoices()
    {
        $choices = array();

        /** @var HierarchyNode $node */
        foreach ($this as $node) {
            if (count($node)) {
                $choices[(string)$node] = $node->getChoices();
            } else {
                $nodeId = $this->hierarchy->getNodeId($node);
                $choices[$nodeId] = $nodeId;
            }
        }

        return $choices;
    }

    /**
     * Recursively get a labels array for a form ChoiceList (only leaf values can be selected, the others are opt groups)
     *
     * @return array
     */
    public function getLabels()
    {
        $labels = array();

        /** @var HierarchyNode $node */
        foreach ($this as $node) {
            if (count($node)) {
                $labels[(string)$node] = $node->getLabels();
            } else {
                $nodeId = $this->hierarchy->getNodeId($node);
                $labels[$nodeId] = (string) $node;
            }
        }

        return $labels;
    }
}
 