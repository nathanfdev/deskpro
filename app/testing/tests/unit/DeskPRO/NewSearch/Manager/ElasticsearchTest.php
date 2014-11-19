<?php

namespace DpUnitTests\DeskPRO\NewSearch\Manager;

use Application\DeskPRO\NewSearch\Manager\Elasticsearch;
use DpTestingMocks\ContainerMock;
use Mockery as m;

class ElasticsearchTest extends \DpUnitTestCase
{
    /**
     * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    private $container;

    /**
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    private function getMockContainer()
    {
        if ($this->container) return $this->container;

        $this->container = ContainerMock::create()
                                        ->withAgentData()
                                        ->withNullEm()
                                        ->withElasticaRepositoryManager()
                                        ->get();

        return $this->container;
    }

    public function testQuickSearchWithBlankData()
    {
        $manager = new Elasticsearch();
        $manager->setContainer($this->getMockContainer());

        $agent = m::mock('Application\\DeskPRO\\Entity\\Person');
        $agent->shouldReceive('offsetGet')->with('id')->andReturn(1);
        $agent->shouldReceive('offsetGet')->with('name')->andReturn("James Bond");
        $agent->shouldReceive('offsetGet')->with('is_agent')->andReturn(true);
        $agent->shouldReceive('hasPerm')->with('agent_people.use')->andReturn(true);

        $manager->setPersonContext($agent);

        $results = $manager->quickSearch('test');

        foreach (array('article', 'download', 'feedback', 'news', 'ticket', 'person', 'organization') as $object) {
            $this->assertEmpty(@$results[0][$object]);
        }

        $this->assertEmpty($results[1]);
        $this->assertFalse($results[2]);
    }
}
