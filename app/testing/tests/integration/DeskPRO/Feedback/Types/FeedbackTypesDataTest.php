<?php

namespace DpIntegrationTests\DeskPRO\Feedback\Types;

class FeedbackTypesDataTest extends \DpIntegrationTestCase
{
	/**
	 * @var \Application\DeskPRO\FeedbackTypes\FeedbackTypes
	 */

	private $feedback_types;

	public function runBefore()
	{
		$this->helper->enableDatabaseSet('EmptyDb');
		$this->helper->loadFixtures('General/FeedbackTypesWithUsergroupsData');

		$this->feedback_types = $this->helper->getSymfonyContainer()->getSystemService('feedback_types');
	}

	public function testGetFeedbackTypeEntities()
	{
		$this->assertNull($this->feedback_types->getById(100));
		$this->assertNotNull($this->feedback_types->getById(1));

		$this->assertEquals('Suggestion', $this->feedback_types->getById(1)->getTitle());

		$this->assertEquals(4, $this->feedback_types->count());
	}

	public function testUpdatingOfFeedbackTypeDisplayOrders()
	{
		$this->feedback_types->updateDisplayOrders(
			array(3, 4, 1, 2)
		);

		$this->assertEquals(30, $this->feedback_types->getById(1)->display_order);
		$this->assertEquals(40, $this->feedback_types->getById(2)->display_order);
		$this->assertEquals(10, $this->feedback_types->getById(3)->display_order);
		$this->assertEquals(20, $this->feedback_types->getById(4)->display_order);
	}

	public function testGetAgentUsergroupsForFeedbackTypes()
	{
		$usergroups = $this->feedback_types->getAgentUserGroups(1);

		$this->assertEquals(1, sizeof($usergroups));
	}

	public function testGetNonAgentUsergroupsForFeedbackTypes()
	{
		$usergroups = $this->feedback_types->getNonAgentUserGroups(1);

		$this->assertEquals(2, sizeof($usergroups));
	}
}