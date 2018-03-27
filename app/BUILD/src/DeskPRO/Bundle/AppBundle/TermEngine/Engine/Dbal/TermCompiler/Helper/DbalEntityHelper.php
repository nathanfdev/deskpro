<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalEntityHelper.
 */
class DbalEntityHelper extends AbstractDbalHelper
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'entity';
    }

    /**
     * Given a field name from the query, like "ticket.agent_id", create a query
     * part that satisfied the operation for the given array of IDs.
     *
     * You can use a TermEngineExpression in the $ids array.
     *
     * @param $field_name
     * @param $op
     * @param $ids
     *
     * @return DbalQueryPart
     */
    public function buildQueryPart($field_name, $op, $ids)
    {
        $part = new DbalQueryPart();

        // filter ids, which makes sure 0 is used for all null cases, etc
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $ids = $this->filterIds($ids);

        // determine what we will assert
        $assert_ids  = [];
        $assert_null = false;
        foreach ($ids as $id) {
            if (0 === $id) {
                $assert_null = true;
            } else {
                $assert_ids[] = $id;
            }
        }

        $this->getLogger()->debug('DbalEntityHelper: using ids', ['ids' => $assert_ids, 'null?' => $assert_null]);

        $where = '';

        // are there real IDs we need to worry about?
        if (count($assert_ids) > 0) {
            $part->setParameter('ids', $assert_ids);

            $where .= sprintf(
                '%s %s (:ids)',
                $field_name,
                $op === TermInterface::OP_NOT ? 'NOT IN' : 'IN'
            );
        }

        // are we making a null assertion?
        if ($assert_null) {
            $and_or     = $op === TermInterface::OP_NOT ? 'AND' : 'OR';
            $null_isser = $op === TermInterface::OP_NOT ? 'IS NOT NULL' : 'IS NULL';

            $where .= sprintf(
                '%s%s %s',
                strlen($where) > 0 ? " {$and_or} " : '', // add the AND/OR if necessary
                $field_name,
                $null_isser
            );
        }

        $this->getLogger()->debug('DbalEntityHelper: asserting WHERE', ['where' => $where]);
        $part->setWhereString($where);

        return $part;
    }

    /**
     * basic filtering of the "ids" data.
     *
     * @param array $ids
     *
     * @return array
     */
    protected function filterIds(array $ids)
    {
        // the "count" function won't catch arrays that are all null
        $all_null = true;
        foreach ($ids as $val) {
            if (null !== $val) {
                $all_null = false;
                break;
            }
        }

        if (!count($ids) || $all_null) {
            $ids = [0];
        }

        // filter ids
        $filtered_ids = [];
        foreach ($ids as $id) {
            if (null === $id) {
                $filtered_ids[] = 0;
            } elseif ($id instanceof TermEngineExpression) {
                $filtered_ids[] = $id;
            } else {
                $filtered_ids[] = (int) $id;
            }
        }

        return $filtered_ids;
    }
}
