<?php

namespace DpIntegrationTests\DeskPRO\Labels;

class LabelsDataTest extends \DpIntegrationTestCase
{
	/**
	 * @var \Application\DeskPRO\Labels\LabelDefManager
	 */

	private $label_def_manager;

	public function runBefore()
	{
		$this->helper->enableDatabaseSet('EmptyDb');
		$this->helper->loadFixtures('General/SimpleLabelsData');

		$this->label_def_manager = $this->helper->getSymfonyContainer()->getSystemService('label_def_manager');
	}

	public function testGetLabelsAndCounts()
	{
		$feedback_labels = $this->label_def_manager->getLabelsAndCounts('feedback');
		$this->assertEquals(2, sizeof($feedback_labels));
		$this->assertArrayHasKey('feedback_label1', $feedback_labels);
		$this->assertArrayHasKey('feedback_label2', $feedback_labels);

		$chat_labels = $this->label_def_manager->getLabelsAndCounts('chat');
		$this->assertEquals(1, sizeof($chat_labels));
		$this->assertArrayHasKey('chat_label1', $chat_labels);

		$tickets_labels = $this->label_def_manager->getLabelsAndCounts('tickets');
		$this->assertEquals(1, sizeof($tickets_labels));
		$this->assertArrayHasKey('tickets_label1', $tickets_labels);
	}

	public function testRenameLabelDef()
	{
		$this->label_def_manager->renameLabelDef('tickets_label1', 'new ticket label', 'tickets');

		$tickets_labels = $this->label_def_manager->getLabelsAndCounts('tickets');
		$this->assertArrayHasKey('new ticket label', $tickets_labels);
	}

	public function testDeleteLabelDef()
	{
		$this->label_def_manager->deleteLabelDef('feedback_label1', 'feedback');

		$feedback_labels = $this->label_def_manager->getLabelsAndCounts('feedback');
		$this->assertEquals(1, sizeof($feedback_labels));
		$this->assertArrayNotHasKey('feedback_label1', $feedback_labels);

		$this->label_def_manager->deleteLabelDef('incorrect label', 'feedback');

		$feedback_labels = $this->label_def_manager->getLabelsAndCounts('feedback');
		$this->assertEquals(1, sizeof($feedback_labels));
	}
}