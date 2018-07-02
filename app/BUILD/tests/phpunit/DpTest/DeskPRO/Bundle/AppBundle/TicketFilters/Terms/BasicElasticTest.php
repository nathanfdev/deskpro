<?php

namespace DpTest\Bundle\AppBundle\TicketFilters\Terms;

use DpTest\Bundle\AppBundle\TicketFilters\AbstractElasticMatcherTest;

require_once __DIR__.'/../AbstractElasticMatcherTest.php';

/**
 * Class BasicElasticTest.
 */
class BasicElasticTest extends AbstractElasticMatcherTest
{
    public function test_id()
    {
        $this->assertEqualQuery('ticket.id IN (1, 2)', [
            'size'  => 10000,
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'terms' => ['_id' => [1, 2]],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @param string $fqlField
     * @param string $elasticField
     *
     * @dataProvider basicFieldProvider
     */
    public function test_basic_fields($fqlField, $elasticField)
    {
        $this->assertEqualQuery("ticket.$fqlField = 1", [
            'size'  => 10000,
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'term' => [$elasticField => 1],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @return array
     */
    public function basicFieldProvider()
    {
        return [
            ['organization', 'organization_id'],
            ['agent', 'agent'],
            ['department', 'department'],
        ];
    }

    public function test_label()
    {
        $this->assertEqualQuery('ticket.labels = "my label"', [
            'size'  => 10000,
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'bool' => [
                                'should' => [
                                    [
                                        'query_string' => [
                                            'query'            => 'my label',
                                            'fields'           => ['labels'],
                                            'default_operator' => 'AND',
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

    public function test_labels()
    {
        $this->assertEqualQuery('ticket.labels IN("my label", "my label 2")', [
            'size'  => 10000,
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'bool' => [
                                'should' => [
                                    [
                                        'query_string' => [
                                            'query'            => 'my label',
                                            'fields'           => ['labels'],
                                            'default_operator' => 'AND',
                                        ],
                                    ],
                                    [
                                        'query_string' => [
                                            'query'            => 'my label 2',
                                            'fields'           => ['labels'],
                                            'default_operator' => 'AND',
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
