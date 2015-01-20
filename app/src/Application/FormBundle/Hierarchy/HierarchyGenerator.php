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
use Application\AppBundle\DataService\FeedbackDataService;
use Application\AppBundle\Hierarchy\Formatter\FlatListFormatter;
use Application\AppBundle\Hierarchy\Formatter\ParentListFormatter;
use Application\AuthBundle\Permissions\Portal\PortalPermissionsManager;
use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\Person;
use Application\AppBundle\Hierarchy\Formatter\DashesFormatter;
use Application\DeskPRO\Entity\Product;
use Application\DeskPRO\Entity\TicketCategory;
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

    /**
     * @var FeedbackDataService
     */
    private $feedback_data_service;

    public function __construct(EntityManager $em, DepartmentDataService $department_data_service, FeedbackDataService $feedback_data_service)
    {
        $this->em = $em;
        $this->department_data_service = $department_data_service;
        $this->feedback_data_service = $feedback_data_service;
    }

    public function generateForCustomFormField(CustomDefAbstract $field)
    {
        $root_nodes = array();
        foreach ($field->children as $field_child) {
            // fields with a parent_id are dealt with below
            if (!$field_child->getOption('parent_id')) {
                $root_nodes[] = new HierarchyNode($field_child, 0);
            }
        }

        if ($expanded = $field->getOption('expanded')) {
            $formatter = new ParentListFormatter('title');
        } else {
            $formatter = new FlatListFormatter('title');
        }

        $hierarchy = new Hierarchy($root_nodes, $formatter);
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

    public function generateTicketProductsHierarchy()
    {
        $products = $this->em->getRepository('DeskPRO:Product')->findAll();

        $root_nodes = array();
        foreach ($products as $product) {
            if ($product->getParent()) {
                continue;
            }
            $root_nodes[] = new HierarchyNode($product, 0, $product->display_order);
        }

        $hierarchy = new Hierarchy($root_nodes, new FlatListFormatter('title'));
        $hierarchy->markOnlyLeafSelections();

        $recursive = function (Product $prod, HierarchyNode $parent, $depth) use (&$recursive) {
            foreach ($prod->children as $child) {
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
        $departments = $this->department_data_service->getAuthorizedDepartmentsForPersonInPortal($person);

        $root_nodes = array();
        foreach ($departments as $department) {
            $root_nodes[] = new HierarchyNode($department, 0, $department->display_order);
        }

        $hierarchy = new Hierarchy($root_nodes, new FlatListFormatter('title'));
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

    public function generateTicketCategoriesHierarchy()
    {
        $products = $this->em->getRepository('DeskPRO:TicketCategory')->findAll();

        $root_nodes = array();
        foreach ($products as $product) {
            if ($product->getParent()) {
                continue;
            }
            $root_nodes[] = new HierarchyNode($product, 0, $product->display_order);
        }

        $hierarchy = new Hierarchy($root_nodes, new FlatListFormatter('title'));
        $hierarchy->markOnlyLeafSelections();

        $recursive = function (TicketCategory $prod, HierarchyNode $parent, $depth) use (&$recursive) {
            foreach ($prod->getChildren() as $child) {
                $parent->addChild($child_node = new HierarchyNode($child, $depth, $child->display_order));
                $recursive($child, $child_node, $depth + 1);
            }
        };

        foreach ($hierarchy as $root_node) {
            $recursive($root_node->getData(), $root_node, 1);
        }

        return $hierarchy;
    }

    public function generateForFeedbackCategories(Person $person)
    {
        $categories = $this->feedback_data_service->getFeedbackCategoriesForPerson($person);

        $root_nodes = array();
        foreach ($categories as $category) {
            if ($category->getParent()) {
                continue;
            }
            $root_nodes[] = new HierarchyNode($category, 0, $category->display_order);
        }

        $hierarchy = new Hierarchy($root_nodes, new FlatListFormatter('title'));
        $hierarchy->markOnlyLeafSelections();

        $recursive = function (FeedbackCategory $cat, HierarchyNode $parent, $depth) use (&$recursive) {
            foreach ($cat->getChildren() as $child) {
                $parent->addChild($child_node = new HierarchyNode($child, $depth, $child->getDisplayOrder()));
                $recursive($child, $child_node, $depth + 1);
            }
        };

        foreach ($hierarchy as $root_node) {
            $recursive($root_node->getData(), $root_node, 1);
        }

        return $hierarchy;
    }
}
 