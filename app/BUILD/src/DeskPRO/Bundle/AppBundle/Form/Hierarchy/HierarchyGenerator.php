<?php

namespace DeskPRO\Bundle\AppBundle\Form\Hierarchy;

use Application\DeskPRO\Cache\Adapter\SimpleArrayCache;
use Application\DeskPRO\Cache\ConvenientCache;
use Application\DeskPRO\Entity\Brand;
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
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Component\Hierarchy\Formatter\FlatListLanguageAwareFormatter;
use DeskPRO\Component\Hierarchy\Formatter\ParentListLanguageAwareFormatter;
use DeskPRO\Component\Hierarchy\HierarchyNode;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

/**
 * HierarchyGenerator.
 */
class HierarchyGenerator
{
    /**
     * @var ArbitraryHasher
     */
    protected $hashGenerator;

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
    private $departmentDataService;

    /**
     * @var FeedbackDataService
     */
    private $feedbackDataService;

    /**
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * Constructor.
     *
     * @param EntityManager         $em
     * @param DepartmentDataService $departmentDataService
     * @param FeedbackDataService   $feedbackDataService
     * @param LanguageManager       $languageManager
     * @param BrandStack            $brandStack
     */
    public function __construct(
        EntityManager         $em,
        DepartmentDataService $departmentDataService,
        FeedbackDataService   $feedbackDataService,
        LanguageManager       $languageManager,
        BrandStack            $brandStack
    ) {
        $this->em                    = $em;
        $this->departmentDataService = $departmentDataService;
        $this->feedbackDataService   = $feedbackDataService;
        $this->languageManager       = $languageManager;
        $this->brandStack            = $brandStack;
    }

    /**
     * @param CustomDefAbstract $def
     *
     * @return Hierarchy
     */
    public function generateForCustomFormField(CustomDefAbstract $def)
    {
        return $this->generateAndCache(
            [
                'generateForCustomFormField',
                get_class($def),
                $def,
            ],
            function () use ($def) {
                $rootNodes = [];
                foreach ($def->getChildren() as $child) {
                    // fields with a parent_id are dealt with below
                    if (!$child->getOption('parent_id')) {
                        $rootNodes[] = new HierarchyNode($child, 0, HierarchyGenerator::reverseDisplayOrder($child->getDisplayOrder()));
                    }
                }
                if ($def->isRadio() && $def->getOption('none_choice')) {
                    $noneDef = $def->createChild();
                    $noneDef->setTitle($def->getOption('none_choice_title') ?: 'None');

                    // make the choice def orphaned to prevent persisting it
                    $def->removeChild($noneDef);
                    $noneDef->setParent(null);

                    $rootNodes[] = new HierarchyNode($noneDef, 0);
                }

                $expanded = $def->getOption('expanded');
                if ($expanded) {
                    $formatter = new ParentListLanguageAwareFormatter($this->languageManager);
                } else {
                    $formatter = new FlatListLanguageAwareFormatter($this->languageManager);
                }

                $hierarchy = new Hierarchy($rootNodes, $formatter);
                $hierarchy->markOnlyLeafSelections();

                $iterator = function (HierarchyNode $parentNode) use ($def, &$iterator, $hierarchy) {
                    $hierarchy->addNode($parentNode);
                    $subChoices = $def->getSubChoices($parentNode->getData());
                    foreach ($subChoices as $subChoiceDef) {
                        $subChoiceNode = new HierarchyNode(
                            $subChoiceDef,
                            $parentNode->getDepth() + 1,
                            HierarchyGenerator::reverseDisplayOrder($subChoiceDef->getDisplayOrder())
                        );

                        $parentNode->addChild($subChoiceNode);
                        $iterator($subChoiceNode);
                    }
                };

                foreach ($hierarchy as $parentNode) {
                    $iterator($parentNode);
                }

                return $hierarchy;
            }
        );
    }

    /**
     * @param CustomFieldDefinition   $field
     * @param CustomFieldDefinition[] $contextualChoices
     *
     * @return Hierarchy
     */
    public function generateForCustomPerFormField(CustomFieldDefinition $field, array $contextualChoices = [])
    {
        return $this->generateAndCache(
            [
                'generateForCustomPerFormField',
                $field,
                $contextualChoices,
            ],
            function () use ($field, $contextualChoices) {
                $rootNodes = [];
                foreach ($contextualChoices as $fieldChild) {
                    // fields with a parent_id are dealt with below
                    $rootNodes[] = new HierarchyNode($fieldChild, 0, HierarchyGenerator::reverseDisplayOrder($fieldChild->getDisplayOrder()));
                }

                $expanded = $field->getOption('expanded');
                if ($expanded) {
                    $formatter = new ParentListLanguageAwareFormatter($this->languageManager);
                } else {
                    $formatter = new FlatListLanguageAwareFormatter($this->languageManager);
                }

                $hierarchy = new Hierarchy($rootNodes, $formatter);
                $hierarchy->markOnlyLeafSelections();

                return $hierarchy;
            }
        );
    }

    /**
     * @return Hierarchy
     */
    public function generateTicketProductsHierarchy()
    {
        return $this->generateAndCache(
            [
                'generateTicketProductsHierarchy',
            ],
            function () {
                $products = $this->em->getRepository(Product::class)->findAll();
                $rootNodes = [];
                foreach ($products as $product) {
                    if ($product->getParent()) {
                        continue;
                    }

                    $rootNodes[] = new HierarchyNode($product, 0, HierarchyGenerator::reverseDisplayOrder($product->getDisplayOrder()));
                }

                $hierarchy = new Hierarchy($rootNodes, new FlatListLanguageAwareFormatter($this->languageManager));
                $hierarchy->markOnlyLeafSelections();

                $recursive = function (Product $prod, HierarchyNode $parent, $depth) use (&$recursive, $hierarchy) {
                    $hierarchy->addNode($parent);
                    foreach ($prod->getChildren() as $child) {
                        $parent->addChild($childNode = new HierarchyNode($child, $depth, HierarchyGenerator::reverseDisplayOrder($child->getDisplayOrder())));
                        $recursive($child, $childNode, $depth + 1);
                    }
                };

                /** @var HierarchyNode $rootNode */
                foreach ($hierarchy as $rootNode) {
                    $recursive($rootNode->getData(), $rootNode, 1);
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
     * @param Brand  $brand
     *
     * @return Hierarchy
     */
    public function generateTicketDepartmentsHierarchy(Person $person, Ticket $ticket = null, Brand $brand = null)
    {
        if (!$brand) {
            $brand = $this->brandStack->getActive()->getBrand();
        }

        return $this->generateAndCache(
            [
                'generateTicketDepartmentsHierarchy',
                $person,
                $ticket,
                $brand,
            ],
            function () use ($person, $ticket, $brand) {
                /** @var ArrayCollection|Department[] $allowedDepartments */
                $allowedDepartments = new ArrayCollection($this->departmentDataService->getTicketDepartmentsForPerson($person, $brand));

                if ($ticket) {
                    $ticketDepartment = $ticket->getDepartment();
                    if ($ticketDepartment && !$allowedDepartments->contains($ticketDepartment)) {
                        // the dep on the ticket is not allowed for this person, so we force it
                        // to be allowed here...
                        $allowedDepartments->add($ticketDepartment);
                    }
                }

                /** @var HierarchyNode[] $rootNodes */
                $rootNodes = [];
                $addedDepartments = [];

                /** @var \Application\DeskPRO\Entity\Department $department */
                foreach ($allowedDepartments as $department) {
                    $foundRoot = null;
                    if ($department->getParent()) {
                        // skip department if its parent is not available
                        if (!$department->getParent()->isTicketsEnabled()) {
                            continue;
                        }

                        // go through all parents, add them to the "allowed" array so they are in our hierarchy.
                        $parents = $department->getAllParents();
                        foreach ($parents as $parentDep) {
                            if (!$allowedDepartments->contains($parentDep)) {
                                $allowedDepartments->add($parentDep);
                            }
                            if (!$parentDep->getParent()) {
                                $foundRoot = $parentDep;
                            }
                        }
                    }

                    if ($foundRoot) {
                        // if we found a root, that means the dep has parents and we need to use it's root
                        $department = $foundRoot;
                    }

                    // add only unique root departments
                    if (in_array($department, $addedDepartments)) {
                        continue;
                    }

                    $addedDepartments[] = $department;
                    $rootNodes[] = new HierarchyNode(
                        $department,
                        0,
                        HierarchyGenerator::reverseDisplayOrder($department->getDisplayOrder())
                    );
                }

                $hierarchy = new Hierarchy($rootNodes, new FlatListLanguageAwareFormatter($this->languageManager, 'user'));
                $hierarchy->markOnlyLeafSelections();

                $recursive = function (Department $dep, HierarchyNode $parent, $depth) use (
                    &$recursive,
                    $allowedDepartments,
                    $hierarchy
                ) {
                    $hierarchy->addNode($parent);
                    /** @var \Application\DeskPRO\Entity\Department $child */
                    foreach ($dep->getChildren() as $child) {
                        if (!$allowedDepartments->contains($child)) {
                            continue; // not allowed to use this dep.
                        }

                        $parent->addChild($childNode = new HierarchyNode($child, $depth, HierarchyGenerator::reverseDisplayOrder($child->getDisplayOrder())));
                        $recursive($child, $childNode, $depth + 1);
                    }
                };

                /** @var HierarchyNode $rootNode */
                foreach ($hierarchy as $rootNode) {
                    $recursive($rootNode->getData(), $rootNode, 1);
                }

                return $hierarchy;
            }
        );
    }

    /**
     * @return Hierarchy
     */
    public function generateTicketCategoriesHierarchy()
    {
        $em = $this->em;

        return $this->generateAndCache(
            [
                'generateTicketCategoriesHierarchy',
            ],
            function () use ($em) {
                $categories = $em->getRepository(TicketCategory::class)->findAll();
                $rootNodes = [];
                foreach ($categories as $category) {
                    if ($category->getParent()) {
                        continue;
                    }

                    $rootNodes[] = new HierarchyNode($category, 0, HierarchyGenerator::reverseDisplayOrder($category->getDisplayOrder()));
                }

                $hierarchy = new Hierarchy($rootNodes, new FlatListLanguageAwareFormatter($this->languageManager));
                $hierarchy->markOnlyLeafSelections();

                $recursive = function (TicketCategory $category, HierarchyNode $parent, $depth) use (&$recursive, $hierarchy) {
                    $hierarchy->addNode($parent);
                    foreach ($category->getChildren() as $child) {
                        $parent->addChild($childNode = new HierarchyNode($child, $depth, HierarchyGenerator::reverseDisplayOrder($child->getDisplayOrder())));
                        $recursive($child, $childNode, $depth + 1);
                    }
                };

                /** @var HierarchyNode $rootNode */
                foreach ($hierarchy as $rootNode) {
                    $recursive($rootNode->getData(), $rootNode, 1);
                }

                return $hierarchy;
            }
        );
    }

    /**
     * @param Person $person
     *
     * @return Hierarchy
     */
    public function generateForFeedbackCategories(Person $person)
    {
        $feedback_data_service = $this->feedbackDataService;

        return $this->generateAndCache(
            [
                'generateForFeedbackCategories',
                $person,
            ],
            function () use ($feedback_data_service, $person) {
                $categories = $feedback_data_service->getFeedbackCategoriesForPerson($person);
                $rootNodes = [];
                foreach ($categories as $category) {
                    if ($category->getParent()) {
                        continue;
                    }

                    $rootNodes[] = new HierarchyNode($category, 0, HierarchyGenerator::reverseDisplayOrder($category->getDisplayOrder()));
                }

                $hierarchy = new Hierarchy($rootNodes, new FlatListLanguageAwareFormatter($this->languageManager));
                $hierarchy->markOnlyLeafSelections();

                $recursive = function (FeedbackCategory $cat, HierarchyNode $parent, $depth) use (&$recursive, $hierarchy) {
                    $hierarchy->addNode($parent);
                    foreach ($cat->getChildren() as $child) {
                        $parent->addChild($childNode = new HierarchyNode($child, $depth, HierarchyGenerator::reverseDisplayOrder($child->getDisplayOrder())));
                        $recursive($child, $childNode, $depth + 1);
                    }
                };

                /** @var HierarchyNode $rootNode */
                foreach ($hierarchy as $rootNode) {
                    $recursive($rootNode->getData(), $rootNode, 1);
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
        if (null === $this->hashGenerator) {
            $this->hashGenerator = new ArbitraryHasher();
        }

        return $this->hashGenerator->generateHash($input);
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
