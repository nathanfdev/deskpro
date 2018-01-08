<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Class Union.
 */
class Union extends AbstractPart
{
    /**
     * @var array
     */
    private $parts;

    /**
     * Constructor.
     *
     * @param array $parts
     */
    public function __construct(array $parts)
    {
        $this->parts = $parts;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result)
    {
        $sqlParts = [];
        $first    = true;

        foreach ($this->parts as $partInfo) {
            /** @var SelectPart $part */
            list($type, $part) = $partInfo;
            $part->prepare();

            if ($first) {
                $sqlParts[] = '('.$part->toSql().')';
            } else {
                $op         = 'UNION '.($type === 'ANY' ? 'ALL' : 'DISTINCT');
                $sqlParts[] = "\n$op\n".'('.$part->toSql().')';
            }

            $first = false;
        }

        $sql = implode('', $sqlParts);

        return new Prepared("\n$sql\n", 'UNION subqueries', false, 'boolean');
    }

    /**
     * {@inheritdoc}
     */
    public function toDpql(SelectPart $statement, $section, array $stack)
    {
        $dpqlParts = [];
        $first     = true;

        foreach ($this->parts as $partInfo) {
            /** @var SelectPart $part */
            list($type, $part) = $partInfo;

            if ($first) {
                $dpqlParts[] = '('.$part->toDpql().')';
            } else {
                $op          = 'UNION '.($type === 'ANY' ? 'ALL' : 'DISTINCT');
                $dpqlParts[] = "\n$op\n".'('.$part->toDpql().')';
            }

            $first = false;
        }

        return implode('', $dpqlParts);
    }
}
