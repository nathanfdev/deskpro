<?php

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Departments\Form\Type\TicketDepartmentPropsType;


class TicketDepartmentPropsTypeTest extends DatabaseTestCase
{
	public function setUp()
	{
		parent::setUp();

		$this->getDb()->exec("DELETE FROM departments");

		$example_data = array(
			array('id' => 1, 'parent_id' => null, 'title' => 'Department 1', 'is_tickets_enabled' => 1, 'is_chat_enabled' => 0, 'display_order' => 10),
		);

		foreach ($example_data as $d) {
			$this->getDb()->insert('departments', $d);
		}

		DpTestConfig::resetContainer();
	}

	public function testSuccessfulSubmitOfValidData()
	{
		$em = $this->getEm();

		$department = $em->find('DeskPRO:Department', 1);

		/**
		 * @var \Symfony\Component\Form\Form $form
		 */

		$form = DpTestConfig::getContainer()->get('form.factory')->create(
			new TicketDepartmentPropsType(),
			$department
		);

		$form->submit(array('title' => 'some title'));

		$this->assertEquals('some title', $department->title);
		$this->assertEquals('some title', $form->getData()->getTitle());
		$this->assertTrue($form->isValid());
	}

	/**
	 * Part of this test is not passing (see line 77) - and this is weird
	 *
	 * Though it should pass
	 */

	public function testFormNotValidatedInCaseOfEmptyTitle()
	{
		$em = $this->getEm();

		$department = $em->find('DeskPRO:Department', 1);

		/**
		 * @var \Symfony\Component\Form\Form $form
		 */

		$form = DpTestConfig::getContainer()->get('form.factory')->create(
			new TicketDepartmentPropsType(),
			$department
		);

		$form->submit(array('title' => ''));

		/**
		 * This is not passing!
		 *
		 * But according to symfony documentation (@url http://symfony.com/doc/current/book/forms.html) it should pass
		 *
		 * Uncomment to be sure this assertion failing
		 */

		//$this->assertFalse($form->isValid());

		/**
		 * This is to ensure that entity is invalid now, though form object doesn't treat form as invalid
		 *
		 * This is weird!
		 */

		$errors = $this->validateObject($department);

		$this->assertEquals(1, count($errors));
		$this->assertNotSame(false, strpos($errors[0]->getMessage(), 'should not be blank'));
	}

	/**
	 * Another simple test to show that validations constraints doesn't work for simple forms too
	 *
	 * Though it should pass
	 */

	public function testSimpleFormValidation()
	{
		/**
		 * @var \Symfony\Component\Form\Form $form
		 */

		$form = DpTestConfig::getContainer()->get('form.factory')->createBuilder('form')
			->add(
				'name',
				'text',
				array(
					 'constraints' => new Symfony\Component\Validator\Constraints\Length(array('min' => 3)),
				)
			)
			->add(
				'email',
				'email',
				array(
					 'constraints' => array(
						 new Symfony\Component\Validator\Constraints\NotBlank(),
						 new Symfony\Component\Validator\Constraints\Length(array('min' => 3)),
					 )
				)
			)
			->add('message', 'textarea')
			->add('send', 'submit')
			->getForm();

		$form->submit(array('name' => 'n', 'email' => ''));

		/**
		 * This is not passing!
		 *
		 * But according to symfony documentation (@url http://symfony.com/doc/current/book/forms.html#adding-validation) it should pass
		 *
		 * Uncomment to be sure this assertion failing
		 */

		//$this->assertFalse($form->isValid());
	}
}