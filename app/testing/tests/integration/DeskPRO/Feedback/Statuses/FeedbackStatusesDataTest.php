<?php

namespace DpIntegrationTests\DeskPRO\Feedback\Statuses;

class FeedbackStatusesDataTest extends \DpIntegrationTestCase
{
	/**
	 * @var \Application\DeskPRO\FeedbackStatuses\FeedbackStatuses
	 */

	private $feedback_statuses;

	public function runBefore()
	{
		$this->helper->enableDatabaseSet('EmptyDb');
		$this->helper->loadFixtures('General/SimpleFeedbackStatusesData');

		$this->feedback_statuses = $this->helper->getSymfonyContainer()->getSystemService('feedback_statuses');
	}

	public function testGetFeedbackStatusEntities()
	{
		$this->assertNull($this->feedback_statuses->getById(100));
		$this->assertNotNull($this->feedback_statuses->getById(1));

		$this->assertEquals('Planning', $this->feedback_statuses->getById(1)->getTitle());

		$this->assertEquals(4, $this->feedback_statuses->count());
		$this->assertEquals(2, sizeof($this->feedback_statuses->getActiveStatuses()));
		$this->assertEquals(2, sizeof($this->feedback_statuses->getClosedStatuses()));
	}

	public function testUpdatingOfFeedbackStatusDisplayOrders()
	{
		$this->feedback_statuses->updateDisplayOrders(
			array(3, 4, 1, 2)
		);

		$this->assertEquals(30, $this->feedback_statuses->getById(1)->display_order);
		$this->assertEquals(40, $this->feedback_statuses->getById(2)->display_order);
		$this->assertEquals(10, $this->feedback_statuses->getById(3)->display_order);
		$this->assertEquals(20, $this->feedback_statuses->getById(4)->display_order);
	}
}