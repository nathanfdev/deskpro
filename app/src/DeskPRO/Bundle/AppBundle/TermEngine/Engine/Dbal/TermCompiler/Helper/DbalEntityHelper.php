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

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryBuilder;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

class DbalEntityHelper
{
    public function write(DbalQueryBuilder $query_writer, $field_name, $op, array $ids)
    {
        // the "count" function won't catch arrays that are all null
        $all_null = true;
        foreach ($ids as $val) {
            if (null !== $val) {
                $all_null = false;
            }
        }

        if (!count($ids) || $all_null) {
            $ids = array(0);
        }

        // filter ids
        $filtered_ids = array();
        foreach ($ids as $id) {
            if (null === $id) {
                $filtered_ids[] = 0;
            } elseif ($id instanceof TermEngineExpression) {
                $filtered_ids[] = $id;
            } else {
                $filtered_ids[] = (int)$id;
            }
        }

        // determine what we will assert
        $assert_ids = array();
        $assert_null = false;
        foreach ($filtered_ids as $id) {
            if (0 === $id) {
                $assert_null = true;
            } else {
                $assert_ids[] = $id;
            }
        }

        $where = '';
        if (count($assert_ids) > 0) {
            $ids_isser = $op === TermInterface::OP_NOT ? 'NOT IN' : 'IN';
            $ids_param = $query_writer->addParameter('ids', $assert_ids);

            $where .= sprintf('%s %s (:%s)', $field_name, $ids_isser, $ids_param);
        }

        if ($assert_null) {
            $null_isser = $op === TermInterface::OP_NOT ? 'IS NOT NULL' : 'IS NULL';
            $and_or = $op === TermInterface::OP_NOT ? 'AND' : 'OR';

            $where .= sprintf(
                '%s%s %s',
                strlen($where) > 0 ? ' ' . $and_or . ' ' : '',
                $field_name,
                $null_isser
            );
        }

        return $where;
    }
}
