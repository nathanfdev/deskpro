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

namespace DpTest\DeskPRO\Component\FilterQueryLanguage;

use DeskPRO\Component\FilterQueryLanguage\Query\Query;

class FromArrayTest extends \PHPUnit_Framework_TestCase
{
    public function testFromArray()
    {
        $queryParts = $this->getQueryParts();
        $query      = Query::fromArray($queryParts);

        $this->assertEquals(
            'foo = 2 AND bar IN someFunc()',
            $query->fql
        );

        $this->assertEquals(
            Query::NODE_TERM_GROUP,
            $query->root->getType()
        );

        $this->assertEquals(
            Query::NODE_TERM,
            $query->root->terms[0]->getType()
        );

        $this->assertEquals(
            Query::OP_EQ,
            $query->root->terms[0]->operator->getOperator()
        );
    }

    private function getQueryParts()
    {
        $queryParts = <<<'JSON'
{
    "fql": "foo = 2 AND bar IN someFunc()",
    "query": {
        "type": "TERM_GROUP",
        "operator": "AND",
        "terms": [
            {
                "type": "TERM",
                "field": {
                    "identity": "foo"
                },
                "operator": "=",
                "options": {
                    "optType": "COMPARE_OPTION",
                    "value": {
                        "valueType": "NUMERIC",
                        "value": 2,
                        "tokenPos": 6
                    }
                },
                "tokenPos": null
            },
            {
                "type": "TERM",
                "field": {
                    "identity": "bar"
                },
                "operator": "IN",
                "options": {
                    "optType": "IN_OPTION",
                    "valueList": [
                        {
                            "valueType": "FUNC",
                            "name": "somefunc",
                            "params": [],
                            "tokenPos": 16
                        }
                    ]
                },
                "tokenPos": 12
            }
        ],
        "tokenPos": null
    }
}
JSON;

        return json_decode($queryParts, true);
    }
}
