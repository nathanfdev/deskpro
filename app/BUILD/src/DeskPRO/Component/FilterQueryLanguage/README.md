Filter Query Language
=====================

FQL is meant to be a way to express filtering against any arbitrary data source.

There are two parts to this component described below. A FQL query string is a string representing a query; and
a Query is an actual query.

How you _use_ the query is NOT a concern of this component. Every implementation will have it's own rules for
compiling or applying a query. This component simply defines the schema as well as the FQL query string parser.

FQL Query String Parser
-----------------------

A FQL query string is a string representation of a query. The parser can parse a FQL string into an actual Query.

An example FQL query string:

```
(ticket.agent IN (1, 2, 3) OR ticket.agent_team = $my_team) AND ticket.date_created < -1w
```

A FQL query string is made up of one or more _terms_. Terms are separated by a boolean operator OR/AND.

Each term follows this pattern:

```
FIELD OPERATOR VALUE
```

The "field" is always an identifier referring to some checkable field.


Filter Query
------------

A filter query is a data structure representing the query. A Query is just a collection of plain arrays. The
actual schema can be validated with the JSON schema in `fq-schema.json`.

Example:

```
{
    "fql": "ticket.agent_team IN (1, 2, $my_team)",
    "query": {
        "type": "TERM",
        "field": {
            "identity": "ticket.agent_team",
            "name": "ticket",
            "path": "agent_team",
            "tokenPos": 0
        },
        "operator": "IN",
        "options": {
            "valueList": [
                {
                    "valueType": "NUMERIC",
                    "value": "1",
                    "tokenPos": 22
                },
                {
                    "valueType": "NUMERIC",
                    "value": "2",
                    "tokenPos": 25
                },
                {
                    "valueType": "VAR",
                    "identity": "my_team",
                    "name": "my_team",
                    "path": "",
                    "tokenPos": 28
                }
            ]
        },
        "tokenPos": 18
    }
}
```

Note: In the example above, there exists a `fql` property and each node also has a `tokenPos` property too. These
are optional properties and are added by the Parser. These properties can be useful upon validating options
and showing specific errors to the user. E.g. you might validate that the agent team IDs provided are actually
real IDs and show an error to the user if one is wrong.


Usage Example
-------------

```
use DeskPRO\Component\FilterQueryLanguage;

$parser = new FilterQueryLanguage\Parser();

$query = "ticket.agent_team IN (1, 2, $my_team)";
$query = $parser->parseQuery($query);

$debugc = new FilterQueryLanguage\DebugCompiler();
echo $debugc->compile($parts);
```