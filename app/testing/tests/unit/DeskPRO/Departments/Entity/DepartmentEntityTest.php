<?php
namespace DpUnitTests\DeskPRO\Departments\Entity;

use Application\DeskPRO\Entity\Department;

class DepartmentEntityTest extends \DpUnitTestCase
{
    /**
     * @var \Application\DeskPRO\Entity\Department
     */

    private $entity;

    /**
     * @var \Symfony\Component\Validator\Validator
     */

    private $validator;

    public function runBefore()
    {
        $this->entity    = new Department();
        $this->validator = $this->helper->getSymfonyContainer()->getValidator();
    }

    public function testSuccessfulValidationOfDepartmentEntity()
    {
        $this->entity->title              = 'test title';
        $this->entity->is_tickets_enabled = true;
        $this->entity->is_chat_enabled    = false;

        $errors = $this->validator->validate($this->entity);
        $this->assertEquals(0, sizeof($errors));
    }

    public function testUnsuccessfulValidationOfDepartmentEntity()
    {
        $this->entity->title  = '';

        $errors = $this->validator->validate($this->entity);

        $this->assertEquals(1, sizeof($errors));
        $this->assertNotSame(false, strpos($errors[0]->getMessage(), 'This value should not be blank'));
    }

    public function testUnsuccessfulValidationOfDepartmentDueToParentCantBeSelf()
    {
        $this->entity->title  = 'test title';
        $this->entity->parent = $this->entity;

        $errors = $this->validator->validate($this->entity);

        $this->assertEquals(1, sizeof($errors));

        $this->assertNotSame(false, strpos($errors[0]->getMessage(), 'Parent cannot be set to self'));
    }

    public function testDepartmentIsTypeMethod()
    {
        $this->entity->is_tickets_enabled = true;
        $this->entity->is_chat_enabled    = false;

        $this->assertTrue($this->entity->isType('tickets'));
        $this->assertFalse($this->entity->isType('chat'));

        $this->entity->is_tickets_enabled = false;
        $this->entity->is_chat_enabled    = true;

        $this->assertFalse($this->entity->isType('tickets'));
        $this->assertTrue($this->entity->isType('chat'));

        $this->assertFalse($this->entity->isType('some invalid type'));
    }

    public function testGetUserTitleOfDepartment()
    {
        $this->entity->title      = 'test title';
        $this->entity->user_title = '';

        $this->assertEquals('test title', $this->entity->getUserTitle());

        $this->entity->title      = 'test title';
        $this->entity->user_title = 'user title';

        $this->assertEquals('user title', $this->entity->getUserTitle());
    }

    public function testDepartmentChildrenAndParentHierarchy()
    {
        $parent = new Department();
        $parent->title = 'parent';

        $child1 = new Department();
        $child1->title = 'child 1';
        $child1->display_order = 100;

        $child2 = new Department();
        $child2->title = 'child 2';
        $child2->display_order = 20;

        $parent->addChild($child1);
        $parent->addChild($child2);

        $this->assertEquals(2, sizeof($parent->getAllChildren()));
        $this->assertEquals($parent, $child1->parent);
        $this->assertEquals($parent, $child2->parent);

        $children = $parent->getChildrenOrdered();

        $this->assertEquals(2, sizeof($children));
    }
}
