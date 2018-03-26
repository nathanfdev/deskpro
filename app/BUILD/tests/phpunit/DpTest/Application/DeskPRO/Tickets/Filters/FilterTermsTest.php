<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Application\Tickets\Filters;

use Application\DeskPRO\Tickets\Filters\FilterTerms;
use DpTest\DeskProTestCase;

class FilterTermsTest extends DeskProTestCase
{
    public function testAddingAliasField()
    {
        $aliasTypes = ['FilterTicketField', 'FilterUserField', 'FilterOrgField'];

        foreach ($aliasTypes as $alias) {
            $aliasField = [
                'type' => $alias,
                'op' => 'is',
                'options' => [
                    'field' => "ragnar",
                    'value' => 'viking'
                ]
            ];

            $terms = new FilterTerms();
            $terms->addTermFromArray($aliasField);
            $actual = $terms->exportToArray();

            $expected = [
                "version" => 1,
                "terms" => [ $aliasField ]
            ];

            $this->assertEquals($expected, $actual, 'failed to add a filter term with alias');
        }
    }
}
