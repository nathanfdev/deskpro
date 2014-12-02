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

namespace Application\FormBundle\Heirarchy;


use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Department;
use Application\FormBundle\Heirarchy\Formatter\DashesFormatter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class HeirarchyGenerator
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function generateForCustomTicketFormField(CustomDefTicket $field)
    {
        $repo = $this->em->getRepository('DeskPRO:CustomDefTicket');

        return $this->generateForCustomFormField($repo, $field);
    }

    public function generateForCustomFormField(EntityRepository $repo, CustomDefAbstract $field)
    {
        $root_nodes = array();
        foreach ($field->children as $field_child) {
            $root_nodes[] = new HeirarchyNode($field_child, 0);
        }

        $heirarchy = new Heirarchy($root_nodes, new DashesFormatter('title'));

        $recursive = function(CustomDefAbstract $field, HeirarchyNode $parent, $depth) use (&$recursive) {
            foreach ($field->children as $child) {
                $parent->addChild($child_node = new HeirarchyNode($child, $depth, $child->display_order));
                $recursive($child, $child_node, $depth + 1);
            }
        };

        foreach ($heirarchy as $root_node) {
            $recursive($root_node->getData(), $root_node, 1);
        }

        return $heirarchy;
    }

    public function generateForDepartments(Department $dep)
    {
        $level = 0;
        $parent_node = new HeirarchyNode($field, $level);
        $heirarchy = new Heirarchy($parent_node, new DashesFormatter('title'));

        $recursive = function(CustomDefAbstract $field, HeirarchyNode $parent, $depth) use (&$recursive) {

            foreach ($field->children as $child) {
                $parent->addChild($child_node = new HeirarchyNode($child, $depth, $child->display_order));
                $recursive($child, $child_node, $depth + 1);
            }


        };

        $recursive($field, $parent_node, $level + 1);

        return $heirarchy;
    }
}
 