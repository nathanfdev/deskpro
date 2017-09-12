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

namespace DpTest\DeskPRO\Bundle\AppBundle\Webhooks\WebhookExecutor;

use Application\DeskPRO\Tickets\ExecutorContext;
use DeskPRO\Bundle\AppBundle\Entity\Webhooks\TicketWebhook;
use DeskPRO\Bundle\AppBundle\ObjectAlias\DefaultIdResolvingStrategy;
use DeskPRO\Bundle\AppBundle\Webhooks\SearchTermsAliasResolver;
use DeskPRO\Bundle\AppBundle\Webhooks\TicketWebhookVars\ExecutorContextEnv;
use DeskPRO\Bundle\AppBundle\Webhooks\WebhookHttpRequest;
use DeskPRO\Bundle\AppBundle\Webhooks\WebhookInvocation;
use DpTest\DeskProTestCase;

class SearchTermsAliasResolverTest extends DeskProTestCase
{
    public function testIsAliasTermReturnsTrue()
    {
        $aliasTypes = ['ticket_field', 'person_field', 'org_field'];

        foreach ($aliasTypes as $alias) {
            $aliasTerm = [
                "type" => $alias,
                "op" => "does-not-mater",
                "options" => [
                    'field' => "ragnar",
                    'value' => 'viking'
                ]
            ];
            $actual = SearchTermsAliasResolver::isAliasTerm($aliasTerm);
            $this->assertTrue($actual, 'failed to detect an alias term');
        }
    }

    public function testResolveFieldAliasReturnsNullWhenIdCanNotBeResolved()
    {
        $mockStrategy = $this->getMockBuilder(DefaultIdResolvingStrategy::class)->disableOriginalConstructor()->getMock();
        $mockStrategy->method('resolve')->willReturn(null);

        $aliasResolver = new SearchTermsAliasResolver([
            'ticket_field' => $mockStrategy,
            'person_field' => $mockStrategy,
            'org_field' => $mockStrategy
        ]);

        $aliasTypes = ['ticket_field', 'person_field', 'org_field'];

        foreach ($aliasTypes as $alias) {
            $aliasTerm = [
                "type" => $alias,
                "op" => "does-not-mater",
                "options" => [
                    'field' => "ragnar",
                    'value' => 'viking'
                ]
            ];

            $expected = $aliasResolver->resolveTerm($aliasTerm);
            $this->assertNull($expected);
        }
    }

    public function testResolveFieldAliasReturnsExpectedResultWithoutDefaultStrategies()
    {
        $aliasResolver = new SearchTermsAliasResolver([]);

        $aliasTypes = ['ticket_field', 'person_field', 'org_field'];
        $aliases = ['66', 'field66'];

        foreach ($aliasTypes as $alias) {
            foreach ($aliases as $field) {
                $aliasTerm = [
                    "type" => $alias,
                    "op" => "does-not-mater",
                    "options" => [
                        'field' => $field,
                        'value' => 'viking'
                    ]
                ];

                $expected = [
                    "type" => "$alias" . "[66]" ,
                    "op" => "does-not-mater",
                    "options" => [
                        "custom_fields" => [
                            "field_66" => "viking"
                        ]
                    ]
                ];

                $actual = $aliasResolver->resolveTerm($aliasTerm);
                $this->assertEquals($expected, $actual, 'Failed to resolve field alias without default strategy');
            }
        }
    }
}
