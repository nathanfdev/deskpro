<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
        $check       = array();
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
