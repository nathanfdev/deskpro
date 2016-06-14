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
        if ($this->container) {
            return $this->container;
        }

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
        $agent->shouldReceive('offsetGet')->with('name')->andReturn('James Bond');
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
