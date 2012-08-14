<?php

namespace Application\DeskPRO\Dpql\Func;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Statement\Part\Prepared;
use Application\DeskPRO\Dpql\Exception;

class Count extends AbstractFunc
{
	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		if (!in_array($section, array('select', 'split', 'group', 'order'))) {
			throw new Exception('COUNT() may only be used in SELECT, SPLIT BY, GROUP BY, and ORDER BY sections.');
		}

		if (!$this->_arguments) {
			$res = new Prepared('COUNT(*)', 'COUNT()');
		} else {
			if (count($this->_arguments) > 1) {
				throw new Exception('COUNT() can only accept 0 or 1 argument');
			}

			$condition = reset($this->_arguments);
			$prepped = $condition->prepare($statement, $section, $stack, $select, $result);

			$sql = 'SUM(IF(' . $prepped->sql() . ', 1, 0))';
			$res = new Prepared($sql, 'COUNT(' . $prepped->name() . ')');
		}

		$res->setRenderer(function($type, $value) {
			return number_format($value);
		});

		return $res;
	}
}