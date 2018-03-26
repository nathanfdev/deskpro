<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalNumericHelper.
 */
class DbalNumericHelper extends AbstractDbalHelper
{
    /**
     * An identifier for this helper.
     *
     * @return string
     */
    public function getId()
    {
        return 'numeric';
    }

    /**
     * @param string    $fieldName
     * @param string    $op
     * @param int|int[] $num
     * @param null      $num2
     *
     * @return DbalQueryPart
     */
    public function buildQueryPart($fieldName, $op, $num, $num2 = null)
    {
        $part = new DbalQueryPart();

        if (!is_array($num)) {
            $num = [$num];
        }

        $num = array_unique(array_map('intval', $num));
        $num = $num ?: [0];

        $where = $fieldName.' ';

        switch ($op) {
            case TermInterface::OP_NOT:
                $where .= 'NOT IN (:num)';
                break;
            case TermInterface::OP_GT:
                $where .= '> :num';
                $num = max($num);
                break;
            case TermInterface::OP_GTE:
                $where .= '>= :num';
                $num = max($num);
                break;
            case TermInterface::OP_LT:
                $where .= '< :num';
                $num = min($num);
                break;
            case TermInterface::OP_LTE:
                $where .= '<= :num';
                $num = min($num);
                break;
            case TermInterface::OP_RANGE:
                $where .= 'BETWEEN :num AND :num2';
                $num  = reset($num);
                $num2 = max($num, $num2);
                break;
            case TermInterface::OP_NOT_RANGE:
                $where .= 'NOT BETWEEN :num AND :num2';
                $num  = reset($num);
                $num2 = max($num, $num2);
                break;
            case TermInterface::OP_IS:
            default:
                $where .= 'IN (:num)';
                break;
        }

        $part->setParameter('num', $num);
        if (null !== $num2 && (TermInterface::OP_RANGE === $op || TermInterface::OP_NOT_RANGE === $op)) {
            $part->setParameter('num2', $num2);
        }

        $this->getLogger()->debug('DbalNumericHelper: asserting WHERE', ['where' => $where]);

        $part->setWhereString($where);

        return $part;
    }
}
