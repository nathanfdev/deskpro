<?php

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
