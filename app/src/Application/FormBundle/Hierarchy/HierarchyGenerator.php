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


use Application\AuthBundle\Permissions\Portal\PortalPermissionsManager;
use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Application\AppBundle\Hierarchy\Formatter\DashesFormatter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class HierarchyGenerator
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Application\AuthBundle\Permissions\Portal\PortalPermissionsManager
     */
    private $permissions_manager;

    public function __construct(EntityManager $em, PortalPermissionsManager $permissions_manager)
    {
        $this->em = $em;
        $this->permissions_manager = $permissions_manager;
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
            $root_nodes[] = new HierarchyNode($field_child, 0);
        }

        $hierarchy = new Hierarchy($root_nodes, new DashesFormatter('title'));
        $hierarchy->markOnlyLeafSelections();

        $recursive = function(CustomDefAbstract $field, HierarchyNode $parent, $depth) use (&$recursive) {
            foreach ($field->children as $child) {
                $parent->addChild($child_node = new HierarchyNode($child, $depth, $child->display_order));
                $recursive($child, $child_node, $depth + 1);
            }
        };

        foreach ($hierarchy as $root_node) {
            $recursive($root_node->getData(), $root_node, 1);
        }

        return $hierarchy;
    }

    public function generateTicketDepartmentsHierarchy(Person $person)
    {
        $allowed_department_ids = $this->permissions_manager->getAllowedDepartmentIds($person);

        // TODO: make sure allowed_departmetn_ids is correct
        // TODO: since allowed_dep_ids is cached. like, ensure enabled = true for ex, stuff that is always true
        $departments = $this->em
            ->getRepository('DeskPRO:Department')
            ->createQueryBuilder('d')
            ->select('d')
            ->where('d.id IN (:allowed_department_ids) AND d.parent IS NULL AND d.is_tickets_enabled = true')
            ->orderBy('d.display_order', 'ASC')
            ->setParameter('allowed_department_ids', $allowed_department_ids)
            ->getQuery()
            ->getResult()
        ;

        //
        // TODO: the methods in this class are very repetitive, meaning we have a good chance to extract a class for reuse
        //

        $root_nodes = array();
        foreach ($departments as $department) {
            $root_nodes[] = new HierarchyNode($department, 0);
        }

        $hierarchy = new Hierarchy($root_nodes, new DashesFormatter('title'));
        $hierarchy->markOnlyLeafSelections();

        $recursive = function (Department $dep, HierarchyNode $parent, $depth) use (&$recursive) {
            foreach ($dep->children as $child) {
                $parent->addChild($child_node = new HierarchyNode($child, $depth, $child->display_order));
                $recursive($child, $child_node, $depth + 1);
            }
        };

        foreach ($hierarchy as $root_node) {
            $recursive($root_node->getData(), $root_node, 1);
        }

        return $hierarchy;
    }
}
 