<?php

namespace DpTest\Bundle\AppBundle\TicketFilters;

use DeskPRO\Bundle\AppBundle\CountBadge\CountBuilder;
use DeskPRO\Bundle\AppBundle\CountBadge\CountTitleResolver;

class CountBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testSimple()
    {
        $tr = $this->getMockBuilder(CountTitleResolver::class)->getMock();
        $tr->method('getTitles')->willReturn([]);
        $tr->method('getHierarchyMap')->willReturn(null);

        $b = new CountBuilder($tr);

        $result = $b->buildFromArray([
            ['count' => 100, 'agent_id' => 1, 'department_id' => 1],
            ['count' => 200, 'agent_id' => 1, 'department_id' => 2],
            ['count' => 200, 'agent_id' => 2, 'department_id' => 1],
        ], ['agent_id', 'department_id']);

        $this->assertEquals(500, $result->getCount());

        $this->assertEquals('agent_id.1', $result->getNested()[0]->getTitle());
        $this->assertEquals(300, $result->getNested()[0]->getCount());

        $this->assertEquals('agent_id.2', $result->getNested()[1]->getTitle());
        $this->assertEquals(200, $result->getNested()[1]->getCount());

        $this->assertEquals('agent_id.1/department_id.1', $result->getNested()[0]->getNested()[0]->getTitle());
        $this->assertEquals(100, $result->getNested()[0]->getNested()[0]->getCount());

        $this->assertEquals('agent_id.1/department_id.2', $result->getNested()[0]->getNested()[1]->getTitle());
        $this->assertEquals(200, $result->getNested()[0]->getNested()[1]->getCount());

        $this->assertEquals('agent_id.2/department_id.1', $result->getNested()[1]->getNested()[0]->getTitle());
        $this->assertEquals(200, $result->getNested()[1]->getNested()[0]->getCount());
    }

    public function testTitles()
    {
        $tr = $this->getMockBuilder(CountTitleResolver::class)->getMock();
        $tr->method('getTitles')->willReturnOnConsecutiveCalls(
            [1 => 'AgentA', 2 => 'AgentB'],
            [55 => 'DepA', 56 => 'DepB']
        );
        $tr->method('getHierarchyMap')->willReturn(null);

        $b = new CountBuilder($tr);

        $result = $b->buildFromArray([
            ['count' => 100, 'agent_id' => 1, 'department_id' => 55],
            ['count' => 200, 'agent_id' => 1, 'department_id' => 56],
            ['count' => 200, 'agent_id' => 2, 'department_id' => 55],
        ], ['agent_id', 'department_id']);

        $this->assertEquals(500, $result->getCount());
        $this->assertCount(2, $result->getNested());

        $this->assertEquals('AgentA', $result->getNested()[0]->getTitle());
        $this->assertEquals('AgentB', $result->getNested()[1]->getTitle());
        $this->assertEquals('DepA', $result->getNested()[0]->getNested()[0]->getTitle());
        $this->assertEquals('DepB', $result->getNested()[0]->getNested()[1]->getTitle());
        $this->assertEquals('DepA', $result->getNested()[1]->getNested()[0]->getTitle());
    }

    public function testFlattenHierarchy()
    {
        $tr = $this->getMockBuilder(CountTitleResolver::class)->getMock();
        $tr->method('getTitles')->willReturnOnConsecutiveCalls(
            [55 => 'DepA', 56 => 'DepB', 58 => 'DepA>DepC', 59 => 'DepA>DepC>DepD'],
            [1 => 'Foo', 2 => 'Bar']
        );
        $tr->method('getHierarchyMap')->willReturnOnConsecutiveCalls(
            [
                55 => [],
                56 => [],
                58 => [55],
                59 => [55, 58],
            ],
            null
        );

        $b = new CountBuilder($tr);

        $result = $b->buildFromArray([
            ['count' => 100, 'department_id' => 55, 'foo_id' => 1],
            ['count' => 200, 'department_id' => 58, 'foo_id' => 2],
            ['count' => 200, 'department_id' => 56, 'foo_id' => 2],
            ['count' => 200, 'department_id' => 59, 'foo_id' => 2],
        ], ['department_id', 'foo_id']);

        $this->assertEquals(700, $result->getCount());
        $this->assertCount(2, $result->getNested());

        $this->assertEquals('DepA', $result->getNested()[0]->getTitle());
        $this->assertEquals(55, $result->getNested()[0]->getId());

        $this->assertEquals('DepB', $result->getNested()[1]->getTitle());
        $this->assertEquals(56, $result->getNested()[1]->getId());

        $this->assertEquals('Foo', $result->getNested()[0]->getNested()[0]->getTitle());
        $this->assertEquals('Bar', $result->getNested()[1]->getNested()[0]->getTitle());
    }
}
