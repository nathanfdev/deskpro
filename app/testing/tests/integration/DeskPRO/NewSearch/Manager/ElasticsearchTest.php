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

namespace DpIntegrationTests\DeskPRO\NewSearch\Manager;

class ElasticsearchTest extends \DpIntegrationTestCase
{
    public function runBefore()
    {
        if (!isset($GLOBALS['DP_TEST_ELASTIC'])) {
            return;
        }

        $this->helper->enableFreshDatabaseSet('FreshDb');
        $this->helper->loadFixtures('General/SimpleDepartmentData');
        $this->helper->loadFixtures('General/AgentData');
        $this->helper->loadFixtures('General/TicketData');
        $this->helper->indexElasticsearch();
    }

    public function testQuickSearchReturnsResult()
    {
        if (!isset($GLOBALS['DP_TEST_ELASTIC'])) {
            return;
        }

        $elasticManager = $this->helper->getSymfonyContainer()->get('deskpro.search_manager.elasticsearch');
        $entityManager  = $this->helper->getSymfonyContainer()->getEm();

        $agents = $entityManager->getRepository('DeskPRO:Person')->getAgents();
        $teams  = $entityManager->getRepository('DeskPRO:AgentTeam')->getTeams();

        $agents[1]->loadHelper('Agent');
        $agents[1]->loadHelper('AgentPermissions');

        $elasticManager->setPersonContext($agents[1]);

        list($result, $meta, $people_top) = $elasticManager->quickSearch('example');

        $this->assertEquals(1, count($result['article']));
        $this->assertEquals(1, count($result['feedback']));
        $this->assertEquals(1, count($result['news']));
        $this->assertEquals(1, count($result['ticket']));
    }

    public function testAgentCanViewOthersTicketsIfPermitted()
    {
        if (!isset($GLOBALS['DP_TEST_ELASTIC'])) {
            return;
        }

        $elasticManager = $this->helper->getSymfonyContainer()->get('deskpro.search_manager.elasticsearch');
        $entityManager  = $this->helper->getSymfonyContainer()->getEm();

        $agents = $entityManager->getRepository('DeskPRO:Person')->getAgents();
        $teams  = $entityManager->getRepository('DeskPRO:AgentTeam')->getTeams();

        $agents[1]->loadHelper('Agent');
        $agents[1]->loadHelper('AgentPermissions');

        $elasticManager->setPersonContext($agents[1]);

        list($result, $meta, $people_top) = $elasticManager->quickSearch('DeskPRO');

        $this->assertEquals(5, count($result['ticket']));
    }
}
