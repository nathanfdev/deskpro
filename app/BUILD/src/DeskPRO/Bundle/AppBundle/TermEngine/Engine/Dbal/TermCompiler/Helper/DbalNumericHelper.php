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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

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
     * @param $field_name
     * @param $op
     * @param array $num
     * @param null  $num2
     *
     * @return DbalQueryPart
     */
    public function buildQueryPart($field_name, $op, array $num, $num2 = null)
    {
        $part = new DbalQueryPart();

        $num = array_unique(array_map('intval', $num));
        $num = $num ?: [0];

        $where = $field_name.' ';

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
