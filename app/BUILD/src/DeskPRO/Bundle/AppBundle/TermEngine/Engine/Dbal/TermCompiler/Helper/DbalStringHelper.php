<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

class DbalStringHelper extends AbstractDbalHelper
{
    /**
     * An identifier for this helper.
     *
     * @return string
     */
    public function getId()
    {
        return 'string';
    }

    /**
     * OP_IS:      field =        string1 OR  field =        string2
     * OP_NOT:     field !=       string1 AND field !=       string2
     * OP_HAS:     field LIKE     string1 OR  field LIKE     string2
     * OP_NOT_HAS: field NOT LIKE string1 AND field NOT LIKE string2.
     *
     * @param $field_name
     * @param $op
     * @param array $strings
     * @param bool  $wildcard_postfix
     * @param bool  $wildcard_prefix
     *
     * @return DbalQueryPart
     */
    public function buildQueryPart($field_name, $op, array $strings, $wildcard_prefix = false, $wildcard_postfix = false)
    {
        $part    = new DbalQueryPart();
        $strings = array_unique(array_map('strval', $strings));

        if ($wildcard_prefix || $wildcard_postfix) {
            if (TermInterface::OP_IS === $op) {
                $op = TermInterface::OP_HAS;
            } elseif (TermInterface::OP_NOT === $op) {
                $op = TermInterface::OP_NOT_HAS;
            }
        } elseif (TermInterface::OP_HAS === $op || TermInterface::OP_NOT_HAS === $op) {
            $wildcard_postfix = $wildcard_prefix = true;
        }

        switch ($op) {
            case TermInterface::OP_NOT:
                $sql_op = ' != ';
                break;
            case TermInterface::OP_HAS:
                $sql_op = ' LIKE ';
                break;
            case TermInterface::OP_NOT_HAS:
                $sql_op = ' NOT LIKE ';
                break;
            case TermInterface::OP_IS:
            default:
                $sql_op = ' = ';
                break;
        }

        $parts = $values = [];
        foreach ($strings as $k => $string) {
            $prefix  = $wildcard_prefix ? '%' : '';
            $postfix = $wildcard_postfix ? '%' : '';
            $parts[] = $field_name.$sql_op.':string'.$k;
            $part->setParameter('string'.$k, $prefix.trim($string, '%').$postfix);
        }

        $and_or = (TermInterface::OP_NOT === $op || TermInterface::OP_NOT_HAS === $op) ? ' AND ' : ' OR ';

        $where = implode($and_or, $parts);

        $this->getLogger()->debug('DbalStringHelper: asserting WHERE', ['where' => $where]);

        $part->setWhereString($where);

        return $part;
    }
}
