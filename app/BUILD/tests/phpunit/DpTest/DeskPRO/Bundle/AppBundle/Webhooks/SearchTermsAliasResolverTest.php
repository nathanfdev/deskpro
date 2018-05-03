<?php

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
