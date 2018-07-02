<?php

namespace DpTest\Bundle\AppBundle\TicketFilters\Util;

use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\Util\ElasticQueryUtils;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;

/**
 * Class ElasticQueryUtilsTest.
 */
class ElasticQueryUtilsTest extends \PHPUnit_Framework_TestCase
{
    public function test_eq()
    {
        $this->assertEquals(
            [
                'term' => ['my_column' => 'my_val'],
            ],
            ElasticQueryUtils::buildQuery('my_column', Query::OP_EQ, 'my_val')->toArray()
        );
    }

    public function test_neq()
    {
        $this->assertEquals(
            [
                'bool' => [
                    'must_not' => [
                        [
                            'term' => ['my_column' => 'my_val'],
                        ],
                    ],
                ],
            ],
            ElasticQueryUtils::buildQuery('my_column', Query::OP_NEQ, 'my_val')->toArray()
        );
    }

    public function test_neq_with_null()
    {
        $this->assertEquals(
            [
                'bool' => [
                    'must_not' => [
                        [
                            'term' => ['my_column' => 'my_val'],
                        ],
                        [
                            'exists' => [
                                'field' => 'my_column',
                            ],
                        ],
                    ],
                ],
            ],
            ElasticQueryUtils::buildQuery('my_column', Query::OP_NEQ, 'my_val', true)->toArray()
        );
    }

    /**
     * @param string $op
     * @param string $elasticOp
     *
     * @dataProvider ltGtProvider
     */
    public function test_lt_gt_number($op, $elasticOp)
    {
        $this->assertEquals(
            [
                'range' => [
                    'my_column' => [$elasticOp => 5],
                ],
            ],
            ElasticQueryUtils::buildQuery('my_column', $op, 5)->toArray()
        );
    }

    /**
     * @param string $op
     * @param string $elasticOp
     *
     * @dataProvider ltGtProvider
     */
    public function test_lt_gt_date($op, $elasticOp)
    {
        $this->assertEquals(
            [
                'range' => [
                    'my_column' => [$elasticOp => '2018-06-25 00:00:00'],
                ],
            ],
            ElasticQueryUtils::buildQuery('my_column', $op, new \DateTime('2018-06-25'))->toArray()
        );
    }

    /**
     * @return array
     */
    public function ltGtProvider()
    {
        return [
            [Query::OP_LT, 'lt'],
            [Query::OP_LTE, 'lte'],
            [Query::OP_GT, 'gt'],
            [Query::OP_GTE, 'gte'],
        ];
    }

    /**
     * @param string $op
     *
     * @dataProvider inHasOpProvider
     */
    public function test_scalar_in_has($op)
    {
        $this->assertEquals(
            [
                'terms' => [
                    'my_column' => [5],
                ],
            ],
            ElasticQueryUtils::buildQuery('my_column', $op, 5)->toArray()
        );
    }

    /**
     * @param string $op
     *
     * @dataProvider inHasOpProvider
     */
    public function test_in_has($op)
    {
        $this->assertEquals(
            [
                'terms' => [
                    'my_column' => [5],
                ],
            ],
            ElasticQueryUtils::buildQuery('my_column', $op, [5])->toArray()
        );
    }

    /**
     * @return array
     */
    public function inHasOpProvider()
    {
        return [[Query::OP_IN, Query::OP_HAS]];
    }

    public function test_scalar_not_in()
    {
        $this->assertEquals(
            [
                'bool' => [
                    'must_not' => [
                        [
                            'terms' => [
                                'my_column' => [5],
                            ],
                        ],
                    ],
                ],
            ],
            ElasticQueryUtils::buildQuery('my_column', Query::OP_NOT_IN, 5)->toArray()
        );
    }

    public function test_not_in()
    {
        $this->assertEquals(
            [
                'bool' => [
                    'must_not' => [
                        [
                            'terms' => [
                                'my_column' => [5],
                            ],
                        ],
                    ],
                ],
            ],
            ElasticQueryUtils::buildQuery('my_column', Query::OP_NOT_IN, [5])->toArray()
        );
    }

    public function test_not_in_with_null()
    {
        $this->assertEquals(
            [
                'bool' => [
                    'must_not' => [
                        [
                            'terms' => [
                                'my_column' => [5],
                            ],
                        ],
                        [
                            'exists' => [
                                'field' => 'my_column',
                            ],
                        ],
                    ],
                ],
            ],
            ElasticQueryUtils::buildQuery('my_column', Query::OP_NOT_IN, [5], true)->toArray()
        );
    }

    public function test_between_numbers()
    {
        $this->assertEquals(
            [
                'range' => [
                    'my_column' => [
                        'gte' => 5,
                        'lte' => 10,
                    ],
                ],
            ],
            ElasticQueryUtils::buildQuery('my_column', Query::OP_BETWEEN, [5, 10])->toArray()
        );
    }

    public function test_between_dates()
    {
        $this->assertEquals(
            [
                'range' => [
                    'my_column' => [
                        'gte' => '2018-06-25 00:00:00',
                        'lte' => '2018-06-26 00:00:00',
                    ],
                ],
            ],
            ElasticQueryUtils::buildQuery('my_column', Query::OP_BETWEEN, [new \DateTime('2018-06-25'), new \DateTime('2018-06-26')])->toArray()
        );
    }

    public function test_not_between_numbers()
    {
        $this->assertEquals(
            [
                'bool' => [
                    'must_not' => [
                        [
                            'range' => [
                                'my_column' => [
                                    'gte' => 5,
                                    'lte' => 10,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            ElasticQueryUtils::buildQuery('my_column', Query::OP_NOT_BETWEEN, [5, 10])->toArray()
        );
    }

    public function test_not_between_numbers_with_null()
    {
        $this->assertEquals(
            [
                'bool' => [
                    'must_not' => [
                        [
                            'range' => [
                                'my_column' => [
                                    'gte' => 5,
                                    'lte' => 10,
                                ],
                            ],
                        ],
                        [
                            'exists' => [
                                'field' => 'my_column',
                            ],
                        ],
                    ],
                ],
            ],
            ElasticQueryUtils::buildQuery('my_column', Query::OP_NOT_BETWEEN, [5, 10], true)->toArray()
        );
    }

    public function test_not_between_dates()
    {
        $this->assertEquals(
            [
                'bool' => [
                    'must_not' => [
                        [
                            'range' => [
                                'my_column' => [
                                    'gte' => '2018-06-25 00:00:00',
                                    'lte' => '2018-06-26 00:00:00',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            ElasticQueryUtils::buildQuery('my_column', Query::OP_NOT_BETWEEN, [new \DateTime('2018-06-25'), new \DateTime('2018-06-26')])->toArray()
        );
    }

    /**
     * @param string $op
     *
     * @dataProvider isNullOpProvider
     */
    public function test_is_null($op)
    {
        $this->assertEquals(
            [
                'bool' => [
                    'must_not' => [
                        [
                            'exists' => [
                                'field' => 'my_column',
                            ],
                        ],
                    ],
                ],
            ],
            ElasticQueryUtils::buildQuery('my_column', $op)->toArray()
        );
    }

    /**
     * @return array
     */
    public function isNullOpProvider()
    {
        return [[Query::OP_IS_NULL], [Query::OP_EMPTY], [Query::OP_NOT_EXISTS]];
    }

    /**
     * @param string $op
     *
     * @dataProvider notNullOpProvider
     */
    public function test_not_null($op)
    {
        $this->assertEquals(
            [
                'exists' => [
                    'field' => 'my_column',
                ],
            ],
            ElasticQueryUtils::buildQuery('my_column', $op)->toArray()
        );
    }

    /**
     * @return array
     */
    public function notNullOpProvider()
    {
        return [[Query::OP_NOT_NULL], [Query::OP_NOT_EMPTY], [Query::OP_EXISTS]];
    }
}
