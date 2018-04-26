<?php

namespace DeskPRO\Component\FilterQueryLanguage\Query;

use DeskPRO\Component\FilterQueryLanguage\Query\Node\Node;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\TermGroup;

class Query
{
    const NODE_QUERY      = 'QUERY';
    const NODE_TERM_GROUP = 'TERM_GROUP';
    const NODE_TERM       = 'TERM';

    const GROUP_OP_AND = 'AND';
    const GROUP_OP_OR  = 'OR';
    const GROUP_OP_NOT = 'NOT';

    const VAL_STRING        = 'STRING';
    const VAL_NUMERIC       = 'NUMERIC';
    const VAL_BOOLEAN       = 'BOOLEAN';
    const VAL_RELATIVE_TIME = 'RELATIVE_TIME';
    const VAL_FUNC          = 'FUNC';
    const VAL_VAR           = 'VAR';

    const OPT_BETWEEN = 'BETWEEN_OPTION';
    const OPT_COMPARE = 'COMPARE_OPTION';
    const OPT_IN      = 'IN_OPTION';
    const OPT_NONE    = 'NO_OPTION';

    const OP_EQ          = '=';
    const OP_NEQ         = '!=';
    const OP_LT          = '<';
    const OP_LTE         = '<=';
    const OP_GT          = '>';
    const OP_GTE         = '>=';
    const OP_IN          = 'IN';
    const OP_HAS         = 'HAS';
    const OP_NOT_IN      = 'NOT_IN';
    const OP_IS_NULL     = 'IS_NULL';
    const OP_NOT_NULL    = 'NOT_NULL';
    const OP_EMPTY       = 'EMPTY';
    const OP_NOT_EMPTY   = 'NOT_EMPTY';
    const OP_EXISTS      = 'EXISTS';
    const OP_NOT_EXISTS  = 'NOT_EXISTS';
    const OP_BETWEEN     = 'BETWEEN';
    const OP_NOT_BETWEEN = 'NOT_BETWEEN';

    /**
     * @var null|string
     */
    public $fql;

    /**
     * @var Node|null
     */
    public $root;

    /**
     * Query constructor.
     *
     * @param Node|null $root
     * @param string    $fql
     */
    public function __construct($root, $fql = null)
    {
        $this->root = $root;
        $this->fql  = $fql;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'fql'   => $this->fql,
            'query' => $this->root->toArray(),
        ];
    }

    public static function fromArray(array $parts)
    {
        // didnt put it in the query wrapper
        if (!isset($parts['query'])) {
            $parts = ['query' => $parts];
        }

        $type = !empty($parts['query']['type']) ? $parts['query']['type'] : null;

        switch ($type) {
            case self::NODE_TERM_GROUP:
                $root = TermGroup::fromArray($parts['query']);
                break;

            case self::NODE_TERM:
                $root = Term::fromArray($parts['query']);
                break;

            default:
                throw new \InvalidArgumentException('Expected a root node type (TERM or TERM_GROUP)');
        }

        return new self(
            $root,
            !empty($parts['fql']) ? $parts['fql'] : null
        );
    }
}
