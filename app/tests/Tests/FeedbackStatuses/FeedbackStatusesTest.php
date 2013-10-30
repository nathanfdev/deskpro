<?php

use Application\DeskPRO\FeedbackStatuses\FeedbackStatuses;

class FeedbackStatusesTest extends DatabaseTestCase
{
	/**
	 * @var Application\DeskPRO\FeedbackStatuses\FeedbackStatuses;
	 */

	protected $feedbackStatuses;

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

		$this->feedbackStatuses = new FeedbackStatuses($this->getEm());
	}

	public function testGetActiveStatuses()
	{
		$this->assertEquals(2, sizeof($this->feedbackStatuses->getActiveStatuses()));
	}

	public function testGetClosedStatuses()
	{
		$this->assertEquals(2, sizeof($this->feedbackStatuses->getClosedStatuses()));
	}

	public function testGetAllStatuses()
	{
		$this->assertEquals(4, sizeof($this->feedbackStatuses->getAll()));
	}

	public function testCount()
	{
		$this->assertEquals(4, $this->feedbackStatuses->count());
	}

	public function testCreateNew()
	{
		$this->assertInstanceOf(
			'Application\\DeskPRO\\Entity\\FeedbackStatusCategory',
			$this->feedbackStatuses->createNew()
		);
	}

	public function testGetById()
	{
		$this->assertInstanceOf(
			'Application\\DeskPRO\\Entity\\FeedbackStatusCategory',
			$this->feedbackStatuses->getById(1)
		);
	}

	public function testUpdateDisplayOrders()
	{
		$new_display_order = array(3, 2, 1, 4);

		$this->feedbackStatuses->updateDisplayOrders($new_display_order);
		$records = $this->feedbackStatuses->getAll();

		// @todo implement this
	}
}