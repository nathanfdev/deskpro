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
