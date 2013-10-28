<?php

use Application\DeskPRO\FeedbackStatuses\FeedbackStatusEdit;
use Application\DeskPRO\FeedbackStatuses\Form\Type\FeedbackStatusType;

class FeedbackStatusEditTest extends DatabaseTestCase
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
		);

		foreach ($example_data as $data) {

			$this->getDb()->insert('feedback_status_categories', $data);
		}

		DpTestConfig::resetContainer();
	}

	public function testSuccessfulSavingOfFeedbackStatus()
	{
		/**
		 * @var \Application\DeskPro\Entity\FeedbackStatusCategory $feedback_status
		 */

		$em = $this->getEm();

		$feedback_status = $em->find('DeskPRO:FeedbackStatusCategory', 1);

		$feedback_status_edit = new FeedbackStatusEdit($feedback_status);

		$form = $this->createForm(new FeedbackStatusType(), $feedback_status_edit, array('cascade_validation' => true));

		$form->submit(
			array(
				 'feedback_status' => array(
					 'title'       => 'new title',
					 'status_type' => 'closed',
				 )
			)
		);

		$this->assertTrue($form->isValid());
		$this->assertEquals('new title', $feedback_status->getTitle());
		$this->assertEquals('closed', $feedback_status->getStatusType());

		$feedback_status_edit->save($this->getEm());

		$feedback_status = $em->find('DeskPRO:FeedbackStatusCategory', $feedback_status->getId());

		$this->assertEquals('new title', $feedback_status->getTitle());
		$this->assertEquals('closed', $feedback_status->getStatusType());
	}

	public function testNotSavingOfFeedbackStatusDueToValidationFailed()
	{
		// this is not working for now, refer to Tests/Departments/Form/Type/TicketDepartmentPropsType.php
	}
}