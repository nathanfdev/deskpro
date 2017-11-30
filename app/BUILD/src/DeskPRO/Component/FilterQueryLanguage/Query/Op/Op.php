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
