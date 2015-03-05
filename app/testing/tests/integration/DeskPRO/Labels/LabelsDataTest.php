<?php

namespace DpIntegrationTests\DeskPRO\Labels;

use Application\DeskPRO\EntityRepository\LabelDef as LabelDefRepository;

class LabelsDataTest extends \DpIntegrationTestCase
{
    /**
     * @var LabelDefRepository
     */
    private $rep;

    public function runBefore()
    {
        $this->helper->enableDatabaseSet('EmptyDb');
        $this->helper->loadFixtures('General/SimpleLabelsData');
        $this->rep = $this->helper->getSymfonyContainer()->getEm()->getRepository('DeskPRO:LabelDef');
    }

    public function testGetLabelsAndCounts()
    {

        $definitions = $this->rep->getAllDefinitions();
        $check = array();
        foreach ($definitions as $def) {
            $check[$def['label']] = $def;
        }
        $this->assertEquals(4, sizeof($check));
        $this->assertArrayHasKey('feedback_label1', $check);
        $this->assertArrayHasKey('feedback_label2', $check);
        $this->assertArrayHasKey('chat_label1', $check);
        $this->assertArrayHasKey('tickets_label1', $check);
    }

    public function testRenameLabelDef()
    {
        $this->rep->renameLabelDef('tickets_label1', 'new ticket label', '#000000', 'tickets');

        $this->assertNotNull($definition = $this->rep->getDefinition('tickets', 'new ticket label'));
        $this->assertEquals(1, $definition['total']);
    }

    public function testDeleteLabelDef()
    {
        $this->assertNotNull($definition = $this->rep->getDefinition('feedback', 'feedback_label1'));
        $this->rep->deleteDefinition($definition);

        $this->assertNull($this->rep->getDefinition('feedback', 'feedback_label1'));
        $this->assertNull($this->rep->getDefinition('feedback', 'incorrect label'));
    }
}
