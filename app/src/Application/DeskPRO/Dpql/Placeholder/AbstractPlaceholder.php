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

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Statement\Part\AbstractPart;
use Application\DeskPRO\Dpql\Exception;

abstract class AbstractPlaceholder
{
	protected static $_placeholderMap = array(
		'LAST_MONTH' => 'LastMonth',
		'LAST_WEEK' => 'LastWeek',
		'LAST_YEAR' => 'LastYear',
		'PAST_DAY' => 'PastDay',
		'PAST_MONTH' => 'PastMonth',
		'PAST_WEEK' => 'PastWeek',
		'PAST_YEAR' => 'PastYear',
		'THIS_MONTH' => 'ThisMonth',
		'THIS_WEEK' => 'ThisWeek',
		'THIS_YEAR' => 'ThisYear',
		'TODAY' => 'Today',
		'TOMORROW' => 'Tomorrow',
		'YESTERDAY' => 'Yesterday'
	);

	protected $_name;

	abstract public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	);

	protected function __construct($name)
	{
		$this->_name = $name;
	}

	public function prepareComparison(
		AbstractPart $lhs, $comparison, Display $statement, $section, array $stack,
		Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		return false;
	}

	protected function _toDpql()
	{
		return '%' . strtoupper($this->_name) . '%';
	}

	public static function create($name)
	{
		$name = strtoupper($name);
		if (isset(self::$_placeholderMap[$name])) {
			$map = __NAMESPACE__ . '\\' . self::$_placeholderMap[$name];
			return new $map($name);
		} else {
			throw new Exception("Unknown placeholder $name specified.");
		}
	}
}