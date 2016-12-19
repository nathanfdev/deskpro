<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
