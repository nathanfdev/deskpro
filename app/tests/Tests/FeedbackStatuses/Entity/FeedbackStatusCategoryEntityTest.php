<?php

use Application\DeskPRO\Entity\FeedbackStatusCategory;

class FeedbackStatusCategoryEntityTest extends DatabaseTestCase
{
	public function setUp()
	{
		parent::setUp();

		$this->getDb()->exec("DELETE FROM feedback_status_categories");

		$example_data = array(
			array('id' => 1, 'status_type' => 'active', 'title' => 'Status 1', 'display_order' => 0),
			array('id' => 2, 'status_type' => 'active', 'title' => 'Status 2', 'display_order' => 0),
			array('id' => 3, 'status_type' => 'closed', 'title' => 'Status 3', 'display_order' => 0),
			array('id' => 4, 'status_type' => 'closed', 'title' => 'Status 4', 'display_order' => 0),
			array('id' => 5, 'status_type' => 'closed', 'title' => 'Status to delete'),
		);

		foreach ($example_data as $d) {
			$this->getDb()->insert('feedback_status_categories', $d);
		}

		DpTestConfig::resetContainer();
	}

	public function testFeedbackStatusCategoryNotValidatedInCaseOfEmptyTitle()
	{
		$em = $this->getEm();

		$feedback_status_category = $em->find('DeskPRO:FeedbackStatusCategory', 1);
		$feedback_status_category->setTitle('');

		$errors = $this->validateObject($feedback_status_category);

		$this->assertEquals(1, count($errors));
		$this->assertNotSame(false, strpos($errors[0]->getMessage(), 'feedback_status.title.not_blank'));
	}

	public function testFeedbackStatusCategoryNotValidatedInCaseOfInvalidStatusType()
	{
		$em = $this->getEm();

		$feedback_status_category = $em->find('DeskPRO:FeedbackStatusCategory', 1);
		$feedback_status_category->setStatusType('forbidden status');

		$errors = $this->validateObject($feedback_status_category);

		$this->assertEquals(1, count($errors));
		$this->assertNotSame(false, strpos($errors[0]->getMessage(), 'Choose a valid status type'));
	}

	public function testFeedbackStatusCategoryCreate()
	{
		$em = $this->getEm();

		$feedback_status_category = new FeedbackStatusCategory();
		$feedback_status_category->setTitle('some title');
		$feedback_status_category->setStatusType('active');

		$em->persist($feedback_status_category);
		$em->flush();

		$feedback_status_category = $em->find('DeskPRO:FeedbackStatusCategory', $feedback_status_category->getId());

		$this->assertNotEmpty($feedback_status_category);
		$this->assertEquals('some title', $feedback_status_category->getTitle());
		$this->assertEquals('active', $feedback_status_category->getStatusType());
	}

	public function testFeedbackStatusCategoryUpdate()
	{
		$em = $this->getEm();

		$feedback_status_category = $em->find('DeskPRO:FeedbackStatusCategory', 1);

		$feedback_status_category->setTitle('Title Updated');
		$feedback_status_category->setStatusType('Status Type Updated');

		$em->persist($feedback_status_category);
		$em->flush();

		$feedback_status_category = $em->find('DeskPRO:FeedbackStatusCategory', 1);

		$this->assertNotEmpty($feedback_status_category);
		$this->assertEquals('Title Updated', $feedback_status_category->getTitle());
		$this->assertEquals('Status Type Updated', $feedback_status_category->getStatusType());
	}

	public function testFeedbackStatusCategoryDelete()
	{
		$em = $this->getEm();

		$feedback_status_category = $em->find('DeskPRO:FeedbackStatusCategory', 5);
		$em->remove($feedback_status_category);

		$em->flush();

		$feedback_status_category = $em->find('DeskPRO:FeedbackStatusCategory', 5);

		$this->assertEmpty($feedback_status_category);
	}
}