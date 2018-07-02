<?php

namespace DpTest\Bundle\AppBundle\TicketFilters\Terms;

use DpTest\Bundle\AppBundle\TicketFilters\AbstractElasticMatcherTest;

require_once __DIR__.'/../AbstractElasticMatcherTest.php';

/**
 * Class DateElasticTest.
 */
class DateElasticTest extends AbstractElasticMatcherTest
{
    public function test_date_gte()
    {
        $this->assertEqualQuery("ticket.date_created >= '2018-06-27'", [
            'size'  => 10000,
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'range' => [
                                'date_created' => ['gte' => '2018-06-27 00:00:00'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function test_date_range()
    {
        $this->assertEqualQuery("ticket.date_created BETWEEN '2018-06-27' AND '2018-06-30'", [
            'size'  => 10000,
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'range' => [
                                'date_created' => [
                                    'gte' => '2018-06-27 00:00:00',
                                    'lte' => '2018-06-30 00:00:00',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
