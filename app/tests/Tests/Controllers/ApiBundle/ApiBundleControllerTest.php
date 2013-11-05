<?php

use Application\ApiBundle\Controller\AbstractController;
use Application\DeskPRO\Entity\Department;

// as AbstractController is abstract we have to create concrete implementation

class SomeController extends AbstractController
{

}

class ApiBundleControllerTest extends ControllerTestCase
{
	public function setUp()
	{
		parent::setUp();
	}

	public function testEntityValidatedUsingControllerIsEntityValidMethod()
	{
		/**
		 * @var SomeController $controller
		 */

		$controller = $this->createController('SomeController');

		$department = new Department();
		$department->setRealTitle('some title');
		$department->setUserTitle('some user title');

		$this->assertTrue($controller->isEntityValid($department));
	}

	public function testEntityNotValidatedUsingControllerIsEntityValidMethod()
	{
		/**
		 * @var SomeController $controller
		 */

		$controller = $this->createController('SomeController');

		$department = new Department();
		$department->setRealTitle('');
		$department->setUserTitle('some user title');

		$this->assertFalse($controller->isEntityValid($department));
	}

	public function testFormValidationErrorsStringInCaseOfSingleValidationError()
	{
		/**
		 * @var SomeController $controller
		 */

		$controller = $this->createController('SomeController');

		$form = $this->createForm(new SomeTestFormType(), new SomeTestEntity());
		$form->submit(array('order_number' => '12'));

		$this->assertFalse($form->isValid());
		$this->assertEquals(
			'some_test_entity.order_number.min_length',
			$controller->getFormValidationErrorsString($form)
		);
	}

	public function testFormValidationErrorsStringInCaseOfMultipleValidationErrors()
	{
		/**
		 * @var SomeController $controller
		 */

		$controller = $this->createController('SomeController');

		$form = $this->createForm(new SomeTestFormType(), new SomeTestEntity());
		$form->submit(array('order_number' => '1a'));

		$this->assertFalse($form->isValid());
		$this->assertEquals(
			'some_test_entity.order_number.min_length,some_test_entity.order_number.type',
			$controller->getFormValidationErrorsString($form)
		);
	}

	public function testFormValidationErrorsStringWhenValidationPassed()
	{
		/**
		 * @var SomeController $controller
		 */

		$controller = $this->createController('SomeController');

		$form = $this->createForm(new SomeTestFormType(), new SomeTestEntity());
		$form->submit(array('order_number' => '12345'));

		$this->assertTrue($form->isValid());
		$this->assertEquals('',$controller->getFormValidationErrorsString($form));
	}
}

use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

// We use this entity inside tests

class SomeTestEntity
{
	public $order_number;

	public static function loadValidatorMetadata(ValidatorClassMetadata $metadata)
	{
		$metadata->addPropertyConstraint('order_number', new Length(array('min' => 3, 'minMessage' => 'some_test_entity.order_number.min_length')));
		$metadata->addPropertyConstraint('order_number', new Type(array('type' => 'numeric', 'message' => 'some_test_entity.order_number.type')));
	}
}

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

// We use this from type inside tests

class SomeTestFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('order_number', 'text', array('required' => true));
	}

	public function getName()
	{
		return 'some_test_entity';
	}
}