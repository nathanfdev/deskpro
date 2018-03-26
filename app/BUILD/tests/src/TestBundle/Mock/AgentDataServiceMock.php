<?php

namespace DpTestSrc\TestBundle\Mock;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Person;
use Mockery as m;

class AgentDataServiceMock
{
    /**
     * @var \Mockery\MockInterface
     */
    private $mock;

    private function __construct()
    {
        $this->mock = m::mock('Application\\DeskPRO\\DependencyInjection\\SystemServices\\AgentDataService');
    }

    public static function create()
    {
        return new self();
    }

    public function withStandard()
    {
        $this->mock->shouldReceive('get')->andReturnUsing(function ($id) {
            static $agents = [];
            if (isset($agents[$id])) {
                return $agents[$id];
            }
            if ($id > 100) {
                return;
            }
            $agents[$id] = new Person();
            $agents[$id]->id = $id;
            $agents[$id]->is_agent = true;
            $agents[$id]->name = "Agent{$id}_FN Agent{$id}_LN";

            return $agents[$id];
        });

        $this->mock->shouldReceive('getTeam')->andReturnUsing(function ($id) {
            static $teams = [];
            if (isset($teams[$id])) {
                return $teams[$id];
            }
            if ($id > 100) {
                return;
            }
            $teams[$id] = new AgentTeam();
            $teams[$id]->id = $id;
            $teams[$id]->name = "AgentTeam{$id}";

            return $teams[$id];
        });

        return $this;
    }

    /**
     * @return \Application\DeskPRO\DependencyInjection\SystemServices\AgentDataService
     */
    public function get()
    {
        return $this->mock;
    }
}
