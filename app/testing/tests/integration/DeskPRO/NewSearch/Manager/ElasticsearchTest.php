<?php

namespace DpIntegrationTests\DeskPRO\NewSearch\Manager;

class ElasticsearchTest extends \DpIntegrationTestCase
{
    public function runBefore()
    {
        if (!isset($GLOBALS['DP_TEST_ELASTIC'])) return;

        $this->helper->enableFreshDatabaseSet('FreshDb');
        $this->helper->loadFixtures('General/SimpleDepartmentData');
        $this->helper->loadFixtures('General/AgentData');
        $this->helper->loadFixtures('General/TicketData');
        $this->helper->indexElasticsearch();
    }

    public function testQuickSearchReturnsResult()
    {
        if (!isset($GLOBALS['DP_TEST_ELASTIC'])) return;

        $elasticManager = $this->helper->getSymfonyContainer()->get('deskpro.search_manager.elasticsearch');
        $entityManager = $this->helper->getSymfonyContainer()->getEm();

        $agents = $entityManager->getRepository('DeskPRO:Person')->getAgents();
        $teams = $entityManager->getRepository('DeskPRO:AgentTeam')->getTeams();

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
        if (!isset($GLOBALS['DP_TEST_ELASTIC'])) return;

        $elasticManager = $this->helper->getSymfonyContainer()->get('deskpro.search_manager.elasticsearch');
        $entityManager = $this->helper->getSymfonyContainer()->getEm();

        $agents = $entityManager->getRepository('DeskPRO:Person')->getAgents();
        $teams = $entityManager->getRepository('DeskPRO:AgentTeam')->getTeams();

        $agents[1]->loadHelper('Agent');
        $agents[1]->loadHelper('AgentPermissions');

        $elasticManager->setPersonContext($agents[1]);

        list($result, $meta, $people_top) = $elasticManager->quickSearch('DeskPRO');

        $this->assertEquals(5, count($result['ticket']));
    }
}
