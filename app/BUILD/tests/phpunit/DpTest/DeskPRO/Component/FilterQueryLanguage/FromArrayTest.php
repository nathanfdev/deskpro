<?php

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
