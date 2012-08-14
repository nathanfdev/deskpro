<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Dpql
 */

namespace Application\DeskPRO\Dpql\Placeholder;

use Application\DeskPRO\App;
use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Statement\Part\Prepared;
use Application\DeskPRO\Dpql\Statement\Part\AbstractPart;

abstract class AbstractDateRange extends AbstractPlaceholder
{
	abstract protected function _getDateRange();

	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		$range = $this->_getDateRange();
		return new Prepared($select->escapeForSql($range[0]));
	}

	public function prepareComparison(
		AbstractPart $lhs, $comparison, Display $statement, $section, array $stack,
		Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		$range = $this->_getDateRange();
		$rangeStart = $range[1];
		$rangeEnd = $range[2];

		$lhsRes = $lhs->prepare($statement, $section, $stack, $select, $result);

		$lhsSql = $lhsRes->sql();
		$lhsName = $lhsRes->name();
		$dpql = $this->_toDpql();
		$outputName = "$lhsName $comparison $dpql";

		$prepared = false;

		switch ($comparison) {
			case '=':
				$sql = "$lhsSql BETWEEN '$rangeStart' AND '$rangeEnd'";
				$prepared = new Prepared($sql, $outputName);
				break;

			case '<>':
				$sql = "$lhsSql NOT BETWEEN '$rangeStart' AND '$rangeEnd'";
				$prepared = new Prepared($sql, $outputName);
				break;

			case '>':
				$sql = "$lhsSql > '$rangeEnd'";
				$prepared = new Prepared($sql, $outputName);
				break;

			case '>=':
				$sql = "$lhsSql >= '$rangeStart'";
				$prepared = new Prepared($sql, $outputName);
				break;

			case '<':
				$sql = "$lhsSql < '$rangeStart'";
				$prepared = new Prepared($sql, $outputName);
				break;

			case '<=':
				$sql = "$lhsSql <= '$rangeEnd'";
				$prepared = new Prepared($sql, $outputName);
				break;
		}

		return $prepared;
	}
}