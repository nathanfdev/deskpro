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

    /**
     * @param string $op
     *
     * @return bool
     */
    public static function isValidOperator($op)
    {
        switch ($op) {
            case self::OP_EQ:
            case self::OP_NEQ:
            case self::OP_LT:
            case self::OP_LTE:
            case self::OP_GT:
            case self::OP_GTE:
            case self::OP_IN:
            case self::OP_HAS:
            case self::OP_NOT_IN:
            case self::OP_IS_NULL:
            case self::OP_NOT_NULL:
            case self::OP_EMPTY:
            case self::OP_NOT_EMPTY:
            case self::OP_EXISTS:
            case self::OP_NOT_EXISTS:
            case self::OP_BETWEEN:
            case self::OP_NOT_BETWEEN:
                return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public static function commonValueOperators()
    {
        return [
            self::OP_EQ, self::OP_NEQ,
            self::OP_LT, self::OP_LTE,
            self::OP_GT, self::OP_GTE,
            self::OP_BETWEEN, self::OP_NOT_BETWEEN,
            self::OP_IN, self::OP_NOT_IN,
            self::OP_HAS,
        ];
    }

    /**
     * @return array
     */
    public static function commonStringValueOperators()
    {
        return [self::OP_EQ, self::OP_NEQ, self::OP_IN, self::OP_NOT_IN];
    }

    /**
     * @return array
     */
    public static function commonBoolValueOperators()
    {
        return [
            self::OP_EQ, self::OP_NEQ,
        ];
    }

    /**
     * @return array
     */
    public static function commonDateValueOperators()
    {
        return [
            self::OP_EQ, self::OP_NEQ,
            self::OP_LT, self::OP_LTE,
            self::OP_GT, self::OP_GTE,
            self::OP_BETWEEN, self::OP_NOT_BETWEEN,
            self::OP_EMPTY, self::OP_NOT_EMPTY,
        ];
    }

    /**
     * @return array
     */
    public static function commonIdOperators()
    {
        return self::commonValueOperators();
    }

    /**
     * @return array
     */
    public static function commonIdReferenceOperators()
    {
        return array_merge([
            self::OP_EXISTS, self::OP_NOT_EXISTS,
            self::OP_EMPTY, self::OP_NOT_EMPTY,
        ], self::commonIdOperators());
    }
}
