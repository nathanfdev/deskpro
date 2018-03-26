<?php

namespace DeskPRO\Component\FilterQueryLanguage\Query\Op;

use DeskPRO\Component\FilterQueryLanguage\Query\Query;
use DeskPRO\Component\FilterQueryLanguage\Query\QueryPart;

abstract class Op extends QueryPart
{
    const OP = 'ABSTRACT_TYPE';

    public static function createOp($op)
    {
        switch ($op) {
            case Query::OP_EQ: return new EqOp();
            case Query::OP_NEQ: return new NeqOp();
            case Query::OP_LT: return new LtOp();
            case Query::OP_LTE: return new LteOp();
            case Query::OP_GT: return new GtOp();
            case Query::OP_GTE: return new GteOp();
            case Query::OP_IN: return new InOp();
            case Query::OP_HAS: return new HasOp();
            case Query::OP_NOT_IN: return new NotInOp();
            case Query::OP_IS_NULL: return new IsNullOp();
            case Query::OP_NOT_NULL: return new NotNullOp();
            case Query::OP_EMPTY: return new EmptyOp();
            case Query::OP_NOT_EMPTY: return new NotEmptyOp();
            case Query::OP_EXISTS: return new ExistsOp();
            case Query::OP_NOT_EXISTS: return new NotExistsOp();
            case Query::OP_BETWEEN: return new BetweenOp();
            case Query::OP_NOT_BETWEEN: return new NotBetweenOpt();
            default:
                throw new \InvalidArgumentException('Unknown operator');
        }
    }

    public function getOperator()
    {
        return static::OP;
    }
}
