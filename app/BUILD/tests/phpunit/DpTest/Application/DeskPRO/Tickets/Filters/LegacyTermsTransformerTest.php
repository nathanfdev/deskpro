<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
