<?php

namespace DeskPRO\Component\FilterQueryLanguage\Query\Node\GroupOp;

use DeskPRO\Component\FilterQueryLanguage\Query\Query;
use DeskPRO\Component\FilterQueryLanguage\Query\QueryPart;

abstract class GroupOp extends QueryPart
{
    const OP = 'ABSTRACT_GROUP_OP';

    public static function createGroupOp($op)
    {
        switch ($op) {
            case Query::GROUP_OP_AND: return new AndGroupOp();
            case Query::GROUP_OP_OR: return new OrGroupOp();
            case Query::GROUP_OP_NOT: return new NotGroupOp();
            default:
                throw new \InvalidArgumentException('Invalid group op');
        }
    }

    public function getOperator()
    {
        return static::OP;
    }
}
