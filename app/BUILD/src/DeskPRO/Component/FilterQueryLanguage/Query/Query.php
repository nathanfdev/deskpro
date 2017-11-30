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

namespace DeskPRO\Component\FilterQueryLanguage\Query;

use DeskPRO\Component\FilterQueryLanguage\Query\Node\Node;

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
    const OP_NOT_IN      = 'NOT_IN';
    const OP_IS_NULL     = 'IS_NULL';
    const OP_NOT_NULL    = 'NOT_NULL';
    const OP_EMPTY       = 'EMPTY';
    const OP_NOT_EMPTY   = 'NOT_EMPTY';
    const OP_EXISTS      = 'EXISTS';
    const OP_NOT_EXISTS  = 'NOT_EXISTS';
    const OP_BETWEEN     = 'BETWEEN';
    const OP_NOT_BETWEEN = 'NOT_BETWEEN';

    public $fql;
    public $root;

    /**
     * Query constructor.
     *
     * @param Node   $root
     * @param string $fql
     */
    public function __construct($root, $fql = '')
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
}
