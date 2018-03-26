<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Application\Tickets\Filters;

use Application\DeskPRO\Tickets\Filters\FilterTermFactory;
use Application\DeskPRO\Tickets\Filters\FilterTerms;
use Application\DeskPRO\Tickets\Filters\LegacyTermsTransformer;
use DpTest\DeskProTestCase;

class LegacyTermsTransformerTest extends DeskProTestCase
{
    public function testTransformAliasFilterFieldToLegacyField()
    {
        $aliasTypeMapping = [
            'ticket_field' => 'FilterTicketField',
            'person_field' => 'FilterUserField',
            'org_field' => 'FilterOrgField'
        ];

        foreach ($aliasTypeMapping as $legacyType => $filterType) {
            $aliasField = [
                'type' => $filterType,
                'op' => 'is',
                'options' => [
                    'field' => "ragnar",
                    'value' => 'viking'
                ]
            ];

            $expected = [
                [
                    "type" => $legacyType,
                    "op" => "is",
                    "options" => [
                        'field' => "ragnar",
                        'value' => 'viking'
                    ]
                ]
            ];

            $terms = new FilterTerms();
            $terms->addTermFromArray($aliasField);
            $transformer = new LegacyTermsTransformer();
            $actual = $transformer->toLegacyTerms($terms);

            $this->assertEquals($expected, $actual, 'Failed to transform between alias and legacy field');
        }
    }

    public function testTransformLegacyFilterFieldToAliasFilterField()
    {
        $aliasTypeMapping = [
            'ticket_field' => 'FilterTicketField',
            'person_field' => 'FilterUserField',
            'org_field' => 'FilterOrgField'
        ];

        foreach ($aliasTypeMapping as $legacyType => $filterType) {
            $legacyField = [
                "type" => $legacyType,
                "op" => "is",
                "options" => [
                    'field' => "ragnar",
                    'value' => 'viking'
                ]
            ];

            $aliasField = [
                'type' => $filterType,
                'op' => 'is',
                'options' => [
                    'field' => "ragnar",
                    'value' => 'viking'
                ]
            ];
            $terms = new FilterTerms();
            $terms->addTermFromArray($aliasField);
            $expected = $terms->exportToArray();

            $transformer = new LegacyTermsTransformer();
            $filterTerms = $transformer->toFilterTerms([ $legacyField ]);

            $this->assertTrue($filterTerms instanceof FilterTerms);
            $actual = $filterTerms->exportToArray();

            $this->assertEquals($expected, $actual, 'Failed to transform between legacy field and filter field');
        }

    }
}
