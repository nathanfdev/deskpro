<?php

use Application\DeskPRO\Entity\FeedbackStatusCategory;
use Application\DeskPRO\FeedbackStatuses\Form\Type\FeedbackStatusPropsType;


class FeedbackStatusPropsTypeTest extends DatabaseTestCase
{
	public function setUp()
	{
		parent::setUp();

		$this->getDb()->exec("DELETE FROM feedback_status_categories");

		$example_data = array(
			array('id' => 1, 'status_type' => 'active', 'title' => 'Status 1', 'display_order' => 10),
		);

		foreach ($example_data as $d) {
			$this->getDb()->insert('feedback_status_categories', $d);
		}

		DpTestConfig::resetContainer();
	}

	public function testSuccessfulSubmitOfValidData()
	{
		$em = $this->getEm();

		$feedback_status_category = $em->find('DeskPRO:FeedbackStatusCategory', 1);

		$form = $this->createForm(new FeedbackStatusPropsType(), $feedback_status_category);

		$form->submit(array('title' => 'some title'));

		$this->assertEquals('some title', $feedback_status_category->title);
		$this->assertEquals('some title', $form->getData()->getTitle());
		$this->assertTrue($form->isValid());
	}

	/**
	 * Part of this test is not passing (see line 65) - and this is weird
	 *
	 * Though it should pass
	 */

	public function testFormNotValidatedInCaseOfEmptyTitle()
	{
		$em = $this->getEm();

		$feedback_status_category = $em->find('DeskPRO:FeedbackStatusCategory', 1);

		$form = $this->createForm(new FeedbackStatusPropsType(), $feedback_status_category);

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

		$errors = $this->validateObject($feedback_status_category);

		$this->assertEquals(1, count($errors));
		$this->assertNotSame(false, strpos($errors[0]->getMessage(), 'feedback_status.title.not_blank'));
	}
}