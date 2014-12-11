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


use Application\AppBundle\DataService\DepartmentDataService;
use Application\AuthBundle\Permissions\Portal\PortalPermissionsManager;
use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Application\AppBundle\Hierarchy\Formatter\DashesFormatter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

//
// TODO: the methods in this class are very repetitive, meaning we have a good chance to extract a class for reuse
//

class HierarchyGenerator
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Application\AppBundle\DataService\DepartmentDataService
     */
    private $department_data_service;

    public function __construct(EntityManager $em, DepartmentDataService $department_data_service)
    {
        $this->em = $em;
        $this->department_data_service = $department_data_service;
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
            // fields with a parent_id are dealt with below
            if (!$field_child->getOption('parent_id')) {
                $root_nodes[] = new HierarchyNode($field_child, 0);
            }
        }

        $hierarchy = new Hierarchy($root_nodes, new DashesFormatter('title'));
        $hierarchy->markOnlyLeafSelections();

        foreach ($field->children as $field_child) {
            if ($parent_id = $field_child->getOption('parent_id')) {
                if ($parent = $parent_node = $hierarchy->findNodeById($parent_id)) {
                    $parent->addChild(new HierarchyNode($field_child, $parent->getDepth() + 1));
                }
            }
        }

        return $hierarchy;
    }

    public function generateTicketDepartmentsHierarchy(Person $person)
    {
        $departments = $this->department_data_service->getAuthorizedDepartmentsForPersonInPortal($person);

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
 