<?php

namespace DpTest\Bundle\AppBundle\TicketFilters;

require_once __DIR__.'/AbstractElasticMatcherTest.php';

/**
 * Class ElasticMatcherTest.
 */
class ElasticMatcherTest extends AbstractElasticMatcherTest
{
    public function test_multiple_and()
    {
        $this->matcher->disableContextPermissions();
        $this->assertEqualQuery('ticket.id > 100 AND ticket.date_created = "2018-06-29"', [
            'size'  => 10000,
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'range' => [
                                '_id' => [
                                    'gt' => 100,
                                ],
                            ],
                        ],
                        [
                            'term' => [
                                'date_created' => '2018-06-29 00:00:00',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function test_perm_all_allowed()
    {
        $this->matcher->enableContextPermissions();

        $this->agent->all_departments_allowed = true;
        $this->agent->view_assigned           = true;
        $this->agent->view_unassigned         = true;

        $this->assertEqualQuery('ticket.id > 100', [
            'size'  => 10000,
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'range' => [
                                '_id' => [
                                    'gt' => 100,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function test_perm_no_allowed()
    {
        $this->matcher->enableContextPermissions();

        $this->agent->all_departments_allowed = false;
        $this->agent->allowed_departments     = [1, 2];
        $this->agent->view_assigned           = false;
        $this->agent->view_unassigned         = false;

        $this->assertEqualQuery('ticket.id > 100', [
            'size'  => 10000,
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'range' => [
                                '_id' => [
                                    'gt' => 100,
                                ],
                            ],
                        ],
                        [
                            'bool' => [
                                'should' => [
                                    [
                                        'bool' => [
                                            'should' => [
                                                [
                                                    'term' => [
                                                        'agent' => 1,
                                                    ],
                                                ],
                                                [
                                                    'terms' => [
                                                        'agent_team' => [1, 2, 3],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    [
                                        'bool' => [
                                            'must' => [
                                                [
                                                    'terms' => [
                                                        'department' => [1, 2],
                                                    ],
                                                ],
                                                [
                                                    'bool' => [
                                                        'should' => [
                                                            [
                                                                'exists' => [
                                                                    'field' => 'agent',
                                                                ],
                                                            ],
                                                            [
                                                                'exists' => [
                                                                    'field' => 'agent_team',
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                            'must_not' => [
                                                [
                                                    'exists' => [
                                                        'field' => 'agent',
                                                    ],
                                                ],
                                                [
                                                    'exists' => [
                                                        'field' => 'agent_team',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
