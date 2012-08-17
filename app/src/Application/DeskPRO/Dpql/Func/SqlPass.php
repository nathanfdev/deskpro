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

namespace Application\DeskPRO\Dpql\Func;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Statement\Part\Prepared;
use Application\DeskPRO\Dpql\Exception;

/**
 * This is used for functions that are simply passed through to MySQL's internal behavior.
 */
class SqlPass extends AbstractFunc
{
	/**
	 * Lists valid MySQL functions (in keys, upper case) to the number of arguments
	 * they can take. If it's an integer, it must take exactly that many args.
	 * If it's an array, that range will be accepted; -1 represents infinity.
	 *
	 * @var array
	 */
	protected static $_functions = array(
		'ABS' => 1,
		'AVG' => 1,
		'CEILING' => 1,
		'CONCAT' => array(2, -1),
		'CONCAT_WS' => array(3, -1),
		'CHAR_LENGTH' => 1,
		'DATE' => 1,
		'DATEDIFF' => 2,
		'DAY' => 1,
		'DAYNAME' => 1,
		'DAYOFMONTH' => 1,
		'DAYOFWEEK' => 1,
		'DAYOFYEAR' => 1,
		'FIELD' => array(2, -1),
		'FIND_IN_SET' => 2,
		'FROM_UNIXTIME' => 1,
		'GREATEST' => array(2, -1),
		'HOUR' => 1,
		'IF' => 3,
		'IFNULL' => 2,
		'ISNULL' => 1,
		'LAST_DAY' => 1,
		'LEAST' => array(2, -1),
		'LEFT' => 2,
		'LENGTH' => 1,
		'LOCATE' => array(2, 3),
		'LOWER' => 1,
		'LPAD' => 3,
		'LTRIM' => 1,
		'MAX' => 1,
		'MIN' => 1,
		'MINUTE' => 1,
		'MONTH' => 1,
		'MONTHNAME' => 1,
		'POW' => 2,
		'QUARTER' => 1,
		'RAND' => array(0, 1),
		'REPEAT' => 2,
		'REPLACE' => 3,
		'REVERSE' => 1,
		'RIGHT' => 2,
		'ROUND' => array(1, 2),
		'RPAD' => 3,
		'RTRIM' => 1,
		'SECOND' => 1,
		'SQRT' => 1,
		'STDDEV_POP' => 1,
		'STDDEV_SAMP' => 1,
		'STRCMP' => 2,
		'SUBSTRING' => array(2, 3),
		'SUBSTRING_INDEX' => 3,
		'SUM' => 1,
		'TIME' => 1,
		'TIMESTAMP' => 1,
		'TRIM' => 1,
		'TRUNCATE' => 1,
		'UNIX_TIMESTAMP' => 1,
		'UPPER' => 1,
		'UTC_DATE' => 0,
		'UTC_TIME' => 0,
		'UTC_TIMESTAMP' => 0,
		'VAR_POP' => 1,
		'VAR_SAMP' => 1,
		'WEEKDAY' => 1,
		'WEEKOFYEAR' => 1,
		'YEAR' => 1
	);

	/**
	 * Prepares the function for use, including validating that the usage is valid.
	 *
	 * @param \Application\DeskPRO\Dpql\Statement\Display $statement
	 * @param string $section Name of the section usage is in (select, where, split, group, order)
	 * @param \Application\DeskPRO\Dpql\Statement\Part\AbstractPart[] $stack Parent parts
	 * @param \Application\DeskPRO\Dpql\SqlSelect $select Select being built up
	 * @param \Application\DeskPRO\Dpql\ResultHandler $result
	 *
	 * @throws \Application\DeskPRO\Dpql\Exception
	 *
	 * @return \Application\DeskPRO\Dpql\Statement\Part\Prepared|bool Prepared results or false if there's no output
	 */
	public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	)
	{
		$name = $this->_name;
		$lookupName = strtoupper($name);

		if (!isset(self::$_functions[$lookupName])) {
			throw new Exception("Invalid DPQL function $name.");
		}

		$expectedArgs = self::$_functions[$lookupName];
		$givenArgs = count($this->_arguments);

		if (is_array($expectedArgs)) {
			list($minArgs, $maxArgs) = $expectedArgs;
			if ($givenArgs < $minArgs) {
				throw new Exception("DPQL function $name expects at least $minArgs argument(s).");
			}
			if ($maxArgs >= 0 && $givenArgs > $maxArgs) {
				throw new Exception("DPQL function $name expects at least $maxArgs argument(s).");
			}
		} else if ($givenArgs != $expectedArgs) {
			throw new Exception("DPQL function $name expects $expectedArgs argument(s).");
		}

		$valuesSql = array();
		$valuesNames = array();
		foreach ($this->_arguments AS $arg) {
			$prepped = $arg->prepare($statement, $section, $stack, $select, $result);
			$valuesSql[] = $prepped->sql();
			$valuesNames[] = $prepped->name();
		}

		$sql = strtoupper($this->_name) . '(' . implode(', ', $valuesSql) . ')';
		return new Prepared($sql, "$this->_name(" . implode(', ', $valuesNames) . ')');
	}
}