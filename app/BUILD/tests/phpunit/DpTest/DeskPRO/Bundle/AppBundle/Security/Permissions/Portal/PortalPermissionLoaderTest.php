<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\Security\Permissions;

use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\DepartmentPermission;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\Usergroup;
use DeskPRO\Bundle\AppBundle\Security\Permissions\Portal\PortalPermissionsLoader;
use DpTest\PortalTestCase;

/**
 * Class PortalPermissionLoaderTest.
 */
class PortalPermissionLoaderTest extends PortalTestCase
{
    /**
     * @var PortalPermissionsLoader
     */
    private $permissionLoader;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->installDataSet('empty', true, true);
        $this->permissionLoader = $this->getContainer()->get('portal_permissions_loader');

        $em = $this->getEntityManager();

        $everyone = new Usergroup();
        $everyone->setSysName(Usergroup::EVERYONE);
        $everyone->setTitle(Usergroup::EVERYONE);
        $em->persist($everyone);

        $registered = new Usergroup();
        $registered->setSysName(Usergroup::REGISTERED);
        $registered->setTitle(Usergroup::REGISTERED);
        $em->persist($registered);
        $em->flush();

        $brand      = $this->getBrand();
        $brandStack = $this->get('brand_stack');
        $brandStack->push($brand);
    }

    /**
     * @test
     * @dataProvider loadAllCategoriesProvider
     *
     * @param string $entityClass
     * @param string $method
     */
    public function load_all_categories($entityClass, $method)
    {
        $em = $this->getEntityManager();

        $everyone   = $this->getUsergroup(Usergroup::EVERYONE);
        $registered = $this->getUsergroup(Usergroup::REGISTERED);

        /** @var FeedbackCategory|NewsCategory|ArticleCategory|DownloadCategory $category1 */
        $category1 = new $entityClass();
        $category1->setTitle('feedbackCategory 1');
        $category1->addUsergroup($everyone);
        if (property_exists($category1, 'brand')) {
            $category1->setBrand($this->getBrand());
        }
        $em->persist($category1);

        /** @var FeedbackCategory|NewsCategory|ArticleCategory|DownloadCategory $category2 */
        $category2 = new $entityClass();
        $category2->setTitle('feedbackCategory 2');
        $category2->addUsergroup($registered);
        if (property_exists($category2, 'brand')) {
            $category2->setBrand($this->getBrand());
        }
        $em->persist($category2);

        /** @var FeedbackCategory|NewsCategory|ArticleCategory|DownloadCategory $category3 */
        $category3 = new $entityClass();
        $category3->setTitle('feedbackCategory 3');
        $category3->addUsergroup($everyone);
        if (property_exists($category3, 'brand')) {
            $category3->setBrand($this->getBrand());
        }
        $em->persist($category3);
        $em->flush();

        $this->assertEquals(
            [
                $category1->getId(),
                $category2->getId(),
                $category3->getId(),
            ],
            $this->permissionLoader->$method([$everyone->getId(), $registered->getId()])
        );

        $this->assertEquals(
            [
                $category1->getId(),
                $category3->getId(),
            ],
            $this->permissionLoader->$method([$everyone->getId()])
        );

        // everyone group should be added automatically to usergroup ids list
        $this->assertEquals(
            [
                $category1->getId(),
                $category2->getId(),
                $category3->getId(),
            ],
            $this->permissionLoader->$method([$registered->getId()])
        );
    }

    /**
     * @return array
     */
    public function loadAllCategoriesProvider()
    {
        return [
            [FeedbackCategory::class, 'getAllowedFeedbackCategories'],
            [NewsCategory::class, 'getAllowedNewsCategories'],
            [ArticleCategory::class, 'getAllowedArticleCategories'],
            [DownloadCategory::class, 'getAllowedDownloadCategories'],
        ];
    }

    /**
     * @test
     * @dataProvider getAllowedDepartmentsProvider
     *
     * @param string $app
     * @param string $method
     */
    public function get_allowed_departments($app, $method)
    {
        $em = $this->getEntityManager();

        $everyone   = $this->getUsergroup(Usergroup::EVERYONE);
        $registered = $this->getUsergroup(Usergroup::REGISTERED);

        $department1 = $this->createDepartment('department 1');
        $em->persist($department1);

        $department2 = $this->createDepartment('department 2');
        $em->persist($department2);

        $department3 = $this->createDepartment('department 3');
        $em->persist($department3);

        $em->persist($this->createPermission($department1, $everyone, 'full', $app));
        $em->persist($this->createPermission($department2, $registered, 'full', $app));

        $em->flush();

        $this->assertEquals(
            [
                $department1->getId() => [
                    'full' => 1,
                ],
                $department2->getId() => [
                    'full' => 1,
                ],
            ],
            $this->permissionLoader->$method([$everyone, $registered])
        );

        $this->assertEquals(
            [
                $department1->getId() => [
                    'full' => 1,
                ],
            ],
            $this->permissionLoader->$method([$everyone])
        );

        $this->assertEquals(
            [
                $department2->getId() => [
                    'full' => 1,
                ],
            ],
            $this->permissionLoader->$method([$registered])
        );
    }

    /**
     * @test
     */
    public function prepare_department_tree()
    {
        $em       = $this->getEntityManager();
        $everyone = $this->getUsergroup(Usergroup::EVERYONE);

        $department1 = $this->createDepartment('department 1');
        $em->persist($department1);

        $department1a = $this->createDepartment('department 1a', $department1);
        $em->persist($department1a);

        $department2 = $this->createDepartment('department 2');
        $em->persist($department2);

        $department2a = $this->createDepartment('department 2a', $department2);
        $em->persist($department2a);

        $em->persist($this->createPermission($department1, $everyone, 'view'));
        $em->persist($this->createPermission($department2a, $everyone, 'full'));

        $em->flush();
        $em->refresh($department1);
        $em->refresh($department1a);
        $em->refresh($department2);
        $em->refresh($department2a);

        $this->assertEquals(
            [
                $department2->getId() => [
                    'full' => 1,
                ],
                $department2a->getId() => [
                    'full' => 1,
                ],
            ],
            $this->permissionLoader->getAllowedTicketDepartments([$everyone])
        );
    }

    /**
     * @test
     */
    public function get_child_department_permissions()
    {
        $em       = $this->getEntityManager();
        $everyone = $this->getUsergroup(Usergroup::EVERYONE);

        $department1 = $this->createDepartment('department 1');
        $em->persist($department1);

        $department1a = $this->createDepartment('department 1a', $department1);
        $em->persist($department1a);

        $department1aa = $this->createDepartment('department 1aa', $department1a);
        $em->persist($department1aa);

        $department1ab = $this->createDepartment('department 1ab', $department1a);
        $em->persist($department1ab);

        $department1b = $this->createDepartment('department 1b', $department1);
        $em->persist($department1b);

        $em->persist($this->createPermission($department1, $everyone, 'dep1'));
        $em->persist($this->createPermission($department1a, $everyone, 'dep1a'));
        $em->persist($this->createPermission($department1aa, $everyone, 'dep1aa'));
        $em->persist($this->createPermission($department1ab, $everyone, 'dep1ab'));
        $em->persist($this->createPermission($department1b, $everyone, 'dep1b'));

        $em->flush();
        $em->refresh($department1);
        $em->refresh($department1a);
        $em->refresh($department1aa);
        $em->refresh($department1ab);
        $em->refresh($department1b);

        $this->assertEquals(
            [
                $department1->getId() => [
                    'dep1'   => 1,
                    'dep1a'  => 1,
                    'dep1aa' => 1,
                    'dep1ab' => 1,
                    'dep1b'  => 1,
                ],
                $department1a->getId() => [
                    'dep1a'  => 1,
                    'dep1aa' => 1,
                    'dep1ab' => 1,
                ],
                $department1aa->getId() => [
                    'dep1aa' => 1,
                ],
                $department1ab->getId() => [
                    'dep1ab' => 1,
                ],
                $department1b->getId() => [
                    'dep1b' => 1,
                ],
            ],
            $this->permissionLoader->getAllowedTicketDepartments([$everyone])
        );
    }

    /**
     * @return array
     */
    public function getAllowedDepartmentsProvider()
    {
        return [
            [DepartmentPermission::APP_TICKETS, 'getAllowedTicketDepartments'],
            [DepartmentPermission::APP_CHAT, 'getAllowedChatDepartments'],
        ];
    }

    /**
     * @param string $sysName
     *
     * @return Usergroup
     */
    private function getUsergroup($sysName)
    {
        return $this->getEntityManager()->getRepository(Usergroup::class)->findOneBy(['sys_name' => $sysName]);
    }

    /**
     * @return Brand
     */
    private function getBrand()
    {
        return $this->getRepository(Brand::class)->find(1);
    }

    /**
     * @param string          $title
     * @param Department|null $parent
     *
     * @return Department
     */
    private function createDepartment($title, Department $parent = null)
    {
        $department                     = new Department();
        $department->is_tickets_enabled = 1;
        $department->is_chat_enabled    = 1;
        $department->addBrand($this->getBrand());
        $department->setRealTitle($title);

        if ($parent) {
            $department->setParent($parent);
        }

        return $department;
    }

    /**
     * @param Department $department
     * @param Usergroup  $userGroup
     * @param string     $name
     * @param string     $app
     *
     * @return DepartmentPermission
     */
    private function createPermission(Department $department, Usergroup $userGroup, $name, $app = DepartmentPermission::APP_TICKETS)
    {
        $permission = new DepartmentPermission();
        $permission->setName($name);
        $permission->setValue(1);
        $permission->setUsergroup($userGroup);
        $permission->setDepartment($department);
        $permission->setApp($app);

        return $permission;
    }
}
