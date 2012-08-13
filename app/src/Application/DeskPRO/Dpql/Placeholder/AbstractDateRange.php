<?php

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