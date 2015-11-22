<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\PortalBundle\Form\Hierarchy;

use Application\DeskPRO\Cache\Adapter\SimpleArrayCache;
use Application\DeskPRO\Cache\ConvenientCache;
use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Product;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketCategory;
use DeskPRO\Bundle\AppBundle\DataService\DepartmentDataService;
use DeskPRO\Bundle\AppBundle\DataService\Feedback\FeedbackDataService;
use DeskPRO\Bundle\AppBundle\Helper\ArbitraryHasher;
use DeskPRO\Component\Hierarchy\Formatter\FlatListFormatter;
use DeskPRO\Component\Hierarchy\Formatter\ParentListFormatter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

//
// TODO: the methods in this class are very repetitive, meaning we have a good chance to extract a class for reuse
//

class HierarchyGenerator
{
    /**
     * @var ArbitraryHasher
     */
    protected $hash_generator;

    /**
     * @var ConvenientCache
     */
    protected $cache;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \DeskPRO\Bundle\AppBundle\DataService\DepartmentDataService
     */
    private $department_data_service;

    /**
     * @var FeedbackDataService
     */
    private $feedback_data_service;

    public function __construct(EntityManager $em, DepartmentDataService $department_data_service, FeedbackDataService $feedback_data_service)
    {
        $this->em                      = $em;
        $this->department_data_service = $department_data_service;
        $this->feedback_data_service   = $feedback_data_service;
    }

    public function generateForCustomFormField(CustomDefAbstract $field)
    {
        return $this->generateAndCache(
            array(
                'generateForCustomFormField',
                $field,
            ),
            function () use ($field) {
                $root_nodes = array();
                foreach ($field->children as $field_child) {
                    // fields with a parent_id are dealt with below
                    if (!$field_child->getOption('parent_id')) {
                        $root_nodes[] = new HierarchyNode($field_child, 0, HierarchyGenerator::reverseDisplayOrder($field_child->display_order));
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
                            $parent->addChild(new HierarchyNode($field_child, $parent->getDepth() + 1, HierarchyGenerator::reverseDisplayOrder($field_child->display_order)));
                        }
                    }
                }

                return $hierarchy;
            }
        );
    }

    public function generateForCustomPerFormField(CustomFieldDefinition $field, array $contextual_choices = array())
    {
        return $this->generateAndCache(
            array(
                'generateForCustomPerFormField',
                $field,
                $contextual_choices,
            ),
            function () use ($field, $contextual_choices) {
                $root_nodes = array();
                foreach ($contextual_choices as $field_child) {
                    // fields with a parent_id are dealt with below
                    $root_nodes[] = new HierarchyNode($field_child, 0, HierarchyGenerator::reverseDisplayOrder($field_child->display_order));
                }

                if ($expanded = $field->getOption('expanded')) {
                    $formatter = new ParentListFormatter('title');
                } else {
                    $formatter = new FlatListFormatter('title');
                }

                $hierarchy = new Hierarchy($root_nodes, $formatter);
                $hierarchy->markOnlyLeafSelections();

                return $hierarchy;
            }
        );
    }

    public function generateTicketProductsHierarchy()
    {
        $em = $this->em;

        return $this->generateAndCache(
            array(
                'generateTicketProductsHierarchy',
            ),
            function () use ($em) {
                $products = $em->getRepository('DeskPRO:Product')->findAll();

                $root_nodes = array();
                foreach ($products as $product) {
                    if ($product->getParent()) {
                        continue;
                    }
                    $root_nodes[] = new HierarchyNode($product, 0, HierarchyGenerator::reverseDisplayOrder($product->display_order));
                }

                $hierarchy = new Hierarchy($root_nodes, new FlatListFormatter('title'));
                $hierarchy->markOnlyLeafSelections();

                $recursive = function (Product $prod, HierarchyNode $parent, $depth) use (&$recursive) {
                    foreach ($prod->children as $child) {
                        $parent->addChild($child_node = new HierarchyNode($child, $depth, HierarchyGenerator::reverseDisplayOrder($child->display_order)));
                        $recursive($child, $child_node, $depth + 1);
                    }
                };

                foreach ($hierarchy as $root_node) {
                    $recursive($root_node->getData(), $root_node, 1);
                }

                return $hierarchy;
            }
        );
    }

    /**
     * If you provide a $ticket, we ensure that the department of that ticket is always in the hierarchy
     * regardless of that person's department permissions.
     *
     * @param Person $person
     * @param Ticket $ticket
     *
     * @return Hierarchy|null
     */
    public function generateTicketDepartmentsHierarchy(Person $person, Ticket $ticket = null)
    {
        $department_data_service = $this->department_data_service;

        return $this->generateAndCache(
            array(
                'generateTicketDepartmentsHierarchy',
                $person,
                $ticket,
            ),
            function () use ($department_data_service, $person, $ticket) {
                $allowed_departments = $department_data_service->getTicketDepartmentsForPerson($person);
                $allowed_departments = new ArrayCollection($allowed_departments); // for convenient methods
                if ($ticket) {
                    $ticket_department = $ticket->getDepartment();
                    if (!$allowed_departments->contains($ticket_department)) {
                        // the dep on the ticket is not allowed for this person, so we force it
                        // to be allowed here...
                        $allowed_departments->add($ticket_department);
                    }
                }

                $root_nodes = array();
                /** @var \Application\DeskPRO\Entity\Department $department */
                foreach ($allowed_departments as $department) {
                    $found_root = null;
                    if ($department->getParent()) {
                        // go through all parents, add them to the "allowed" array so they are in our hierarchy.
                        $parents = $department->getAllParents();
                        foreach ($parents as $parent_dep) {
                            if (!$allowed_departments->contains($parent_dep)) {
                                $allowed_departments->add($parent_dep);
                            }
                            if (!$parent_dep->getParent()) {
                                $found_root = $parent_dep;
                            }
                        }
                    }

                    if ($found_root) {
                        // if we found a root, that means the dep has parents and we need to use it's root
                        $department = $found_root;
                    }

                    $root_nodes[] = new HierarchyNode(
                        $department,
                        0,
                        HierarchyGenerator::reverseDisplayOrder($department->display_order)
                    );
                }

                $hierarchy = new Hierarchy($root_nodes, new FlatListFormatter('user_title'));
                $hierarchy->markOnlyLeafSelections();

                $recursive = function (Department $dep, HierarchyNode $parent, $depth) use (&$recursive,
                    $allowed_departments) {
                    /** @var \Application\DeskPRO\Entity\Department $child */
                    foreach ($dep->children as $child) {
                        if (!$allowed_departments->contains($child)) {
                            continue; // not allowed to use this dep.
                        }
                        $parent->addChild($child_node = new HierarchyNode($child, $depth, HierarchyGenerator::reverseDisplayOrder($child->display_order)));
                        $recursive($child, $child_node, $depth + 1);
                    }
                };

                /** @var \Application\DeskPRO\Entity\Department $root_node */
                foreach ($hierarchy as $root_node) {
                    $recursive($root_node->getData(), $root_node, 1);
                }

                return $hierarchy;
            }
        );
    }

    public function generateTicketCategoriesHierarchy()
    {
        $em = $this->em;

        return $this->generateAndCache(
            array(
                'generateTicketCategoriesHierarchy',
            ),
            function () use ($em) {
                $products = $em->getRepository('DeskPRO:TicketCategory')->findAll();

                $root_nodes = array();
                foreach ($products as $product) {
                    if ($product->getParent()) {
                        continue;
                    }
                    $root_nodes[] = new HierarchyNode($product, 0, HierarchyGenerator::reverseDisplayOrder($product->display_order));
                }

                $hierarchy = new Hierarchy($root_nodes, new FlatListFormatter('title'));
                $hierarchy->markOnlyLeafSelections();

                $recursive = function (TicketCategory $prod, HierarchyNode $parent, $depth) use (&$recursive) {
                    foreach ($prod->getChildren() as $child) {
                        $parent->addChild($child_node = new HierarchyNode($child, $depth, HierarchyGenerator::reverseDisplayOrder($child->display_order)));
                        $recursive($child, $child_node, $depth + 1);
                    }
                };

                foreach ($hierarchy as $root_node) {
                    $recursive($root_node->getData(), $root_node, 1);
                }

                return $hierarchy;
            }
        );
    }

    public function generateForFeedbackCategories(Person $person)
    {
        $feedback_data_service = $this->feedback_data_service;

        return $this->generateAndCache(
            array(
                'generateForFeedbackCategories',
                $person,
            ),
            function () use ($feedback_data_service, $person) {

                $categories = $feedback_data_service->getFeedbackCategoriesForPerson($person);

                $root_nodes = array();
                foreach ($categories as $category) {
                    if ($category->getParent()) {
                        continue;
                    }
                    $root_nodes[] = new HierarchyNode($category, 0, HierarchyGenerator::reverseDisplayOrder($category->display_order));
                }

                $hierarchy = new Hierarchy($root_nodes, new FlatListFormatter('title'));
                $hierarchy->markOnlyLeafSelections();

                $recursive = function (FeedbackCategory $cat, HierarchyNode $parent, $depth) use (&$recursive) {
                    foreach ($cat->getChildren() as $child) {
                        $parent->addChild($child_node = new HierarchyNode($child, $depth, HierarchyGenerator::reverseDisplayOrder($child->getDisplayOrder())));
                        $recursive($child, $child_node, $depth + 1);
                    }
                };

                foreach ($hierarchy as $root_node) {
                    $recursive($root_node->getData(), $root_node, 1);
                }

                return $hierarchy;
            }
        );
    }

    /**
     * @param mixed $params   the "ArbitraryHasher" input to create cache key for this callable
     * @param mixed $callable doesn't need to be a callable, can be any default value, but usually is a callable
     *
     * @return mixed|null
     */
    protected function generateAndCache($params, $callable)
    {
        return $this->getCache()->get($this->generateHash($params), $callable);
    }

    /**
     * @return ConvenientCache
     */
    protected function getCache()
    {
        if (null === $this->cache) {
            $this->cache = new ConvenientCache(new SimpleArrayCache());
        }

        return $this->cache;
    }

    /**
     * @param mixed $input
     *
     * @return string
     */
    protected function generateHash($input)
    {
        if (null === $this->hash_generator) {
            $this->hash_generator = new ArbitraryHasher();
        }

        return $this->hash_generator->generateHash($input);
    }

    /**
     * The Hierarchy component uses higher values as higher display order, but our models use lower values to display
     * higher in the lists. To fix this, we need to invert the values.
     *
     * @param int $display_order
     *
     * @return int
     */
    public static function reverseDisplayOrder($display_order)
    {
        return -1 * ((int) $display_order);
    }
}
