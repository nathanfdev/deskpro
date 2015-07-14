<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper;

use DeskPRO\Bundle\AppBundle\TermEngine\TermCompilerHelperInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

class DbalJoinedHelper extends AbstractDbalHelper
{

    /**
     * An identifier for this helper
     *
     * @return string
     */
    public function getId()
    {
        return 'joined';
    }

    /**
     * @param $field_name
     * @param $op
     * @param array $num
     * @param null $num2
     * @return DbalQueryPart
     */
    public function buildQueryPart($field_name, $join_table, $join_clause, $op, $input)
    {
        $part = new DbalQueryPart();
        
        $sql_op = (TermInterface::OP_NOT_HAS === $op) ? '!=' : '=';
        
        $part->addUniqueJoin($join_table, $join_table, $join_clause);        
        $part->setWhereString(sprintf('%s %s :input', $field_name, $sql_op));
        $part->setParameter('input', $input);

        return $part;
    }
}
