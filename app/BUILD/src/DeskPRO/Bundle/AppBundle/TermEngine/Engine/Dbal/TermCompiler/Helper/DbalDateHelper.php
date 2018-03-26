<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

class DbalDateHelper extends AbstractDbalHelper
{
    /**
     * An identifier for this helper.
     *
     * @return string
     */
    public function getId()
    {
        return 'date';
    }

    public function buildQueryPart($field_name, $op, \DateTime $date, \DateTime $date2 = null, $ignore_time = false)
    {
        $part = new DbalQueryPart();

        $date = clone $date;
        if ($date2) {
            $date2 = clone $date2;
        }

        if ($ignore_time) {
            $date->setTime(0, 0, 0);
            $date2 = $date2 ?: clone $date;
            $date2->setTime(23, 59, 59);
        }

        $date->setTimezone(new \DateTimeZone('UTC'));
        $part->setParameter('date', $date->format('Y-m-d H:i:s'));

        if ($date2) {
            $date2->setTimezone(new \DateTimeZone('UTC'));

            if (TermInterface::OP_IS === $op) {
                $op = TermInterface::OP_RANGE;
            } elseif (TermInterface::OP_NOT === $op) {
                $op = TermInterface::OP_NOT_RANGE;
            }

            if (TermInterface::OP_RANGE === $op || TermInterface::OP_NOT_RANGE === $op) {
                $part->setParameter('date2', $date2->format('Y-m-d H:i:s'));
            }
        }

        $where = $field_name.' ';

        switch ($op) {
            case TermInterface::OP_NOT:
                $where .= '!= :date';
                break;
            case TermInterface::OP_GT:
                $where .= '> :date';
                break;
            case TermInterface::OP_GTE:
                $where .= '>= :date';
                break;
            case TermInterface::OP_LT:
                $where .= '< :date';
                break;
            case TermInterface::OP_LTE:
                $where .= '<= :date';
                break;
            case TermInterface::OP_RANGE:
                $where .= 'BETWEEN :date AND :date2';
                break;
            case TermInterface::OP_NOT_RANGE:
                $where .= 'NOT BETWEEN :date AND :date2';
                break;
            case TermInterface::OP_IS:
            default:
                $where .= '= :date';
                break;
        }

        $this->getLogger()->debug('DbalDateHelper: asserting WHERE', ['where' => $where]);

        $part->setWhereString($where);

        return $part;
    }
}
