<?php

class TicketsLabelsTest extends DatabaseTestCase
{
	/** @var \Application\DeskPRO\Labels\LabelDefManager */
	private $manager;
	private $types = array('tickets');
	private $label_name;

	public function setUp()
	{
		parent::setUp();
		$this->manager = new \Application\DeskPRO\Labels\LabelDefManager($this->getEm());
		$this->removeAllLabels();
	}

	protected function removeAllLabels()
	{
		$all_labels = $this->manager->getLabels($this->types);
		if (!empty($all_labels)) {
			foreach ($all_labels as $label) {
				$this->manager->deleteLabelDef($label, $this->types);
			}
		}
	}

	protected function getUniqueLabel()
	{
		return 'label'.md5(microtime(true));
	}

	public function testCreateAndRead()
	{
		$name = $this->getUniqueLabel();
		$this->manager->createLabelDef($name, $this->types);
		$labels = $this->manager->getLabelsAndCounts($this->types);
		$this->assertArrayHasKey($name, $labels);
		$this->assertEquals(0, $labels[$name]);
	}

	public function testUpdate()
	{
		$name = $this->getUniqueLabel();
		$this->manager->createLabelDef($name, $this->types);
		$new_name = $this->getUniqueLabel();
		$this->manager->renameLabelDef($name, $new_name, $this->types);
		$labels = $this->manager->getLabelsAndCounts($this->types);
		$this->assertArrayHasKey($new_name, $labels);
		$this->assertArrayNotHasKey($name, $labels);
	}

	public function testDelete()
	{
		$name = $this->getUniqueLabel();
		$this->manager->createLabelDef($name, $this->types);
		$this->manager->deleteLabelDef($name, $this->types);
		$labels = $this->manager->getLabelsAndCounts($this->types);
		$this->assertArrayNotHasKey($name, $labels);
	}
}
