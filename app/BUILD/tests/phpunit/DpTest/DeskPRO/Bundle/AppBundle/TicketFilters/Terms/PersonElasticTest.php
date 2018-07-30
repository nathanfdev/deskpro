<?php

namespace DpTest\Bundle\AppBundle\TicketFilters\Terms;

use DpTest\Bundle\AppBundle\TicketFilters\AbstractElasticMatcherTest;

require_once __DIR__.'/../AbstractElasticMatcherTest.php';

/**
 * Class PersonElasticTest.
 */
class PersonElasticTest extends AbstractElasticMatcherTest
{
    public function test_person_id()
    {
        $this->assertEqualQuery('ticket.person > 100', [
            'size'  => 10000,
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'range' => [
                                'person_id' => ['gt' => 100],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
