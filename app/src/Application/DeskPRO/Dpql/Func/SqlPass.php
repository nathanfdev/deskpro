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
	 * Lists valid MySQL functions (in keys, upper case) to the data type
	 * returned and the number of arguments they can take. Value is an array
	 * with 2 or 3 elements:
	 *  - 0: data type returned
	 *  - 1: minimum (or only) number or arguments allowed
	 *  - 2: (optional) maximum number of arguments allowed; -1 for unlimited; omit for same as minimum
	 *
	 * @var array
	 */
	protected static $_functions = array(
		'ABS' => array('number', 1),
		'AVG' => array('number', 1),
		'CEILING' => array('number', 1),
		'CONCAT' => array('string', 2, -1),
		'CONCAT_WS' => array('string', 3, -1),
		'CHAR_LENGTH' => array('number', 1),
		'DATE' => array('date', 1),
		'DATE_FORMAT' => array('string', 2),
		'DATEDIFF' => array('number', 2),
		'DAYOFYEAR' => array('number', 1),
		'FIELD' => array('number', 2, -1),
		'FIND_IN_SET' => array('number', 2),
		'FLOOR' => array('number', 1),
		'FROM_UNIXTIME' => array('datetime', 1),
		'GREATEST' => array('number', 2, -1),
		'IF' => array('mixed', 3),
		'IFNULL' => array('mixed', 2),
		'ISNULL' => array('boolean', 1),
		'LAST_DAY' => array('number', 1),
		'LEAST' => array('number', 2, -1),
		'LEFT' => array('string', 2),
		'LENGTH' => array('number', 1),
		'LOCATE' => array('number', 2, 3),
		'LOWER' => array('string', 1),
		'LPAD' => array('string', 3),
		'LTRIM' => array('string', 1),
		'MAX' => array('number', 1),
		'MIN' => array('number', 1),
		'POW' => array('number', 2),
		'QUARTER' => array('number', 1),
		'RAND' => array('number', 0, 1),
		'REPEAT' => array('string', 2),
		'REPLACE' => array('string', 3),
		'REVERSE' => array('string', 1),
		'RIGHT' => array('string', 2),
		'ROUND' => array('number', 1, 2),
		'RPAD' => array('string', 3),
		'RTRIM' => array('string', 1),
		'SECOND' => array('number', 1),
		'SQRT' => array('number', 1),
		'STDDEV_POP' => array('number', 1),
		'STDDEV_SAMP' => array('number', 1),
		'STRCMP' => array('number', 2),
		'SUBSTRING' => array('string', 2, 3),
		'SUBSTRING_INDEX' => array('string', 3),
		'SUM' => array('number', 1),
		'TIME' => array('time', 1),
		'TIMESTAMP' => array('datetime', 1),
		'TRIM' => array('string', 1),
		'TRUNCATE' => array('number', 1),
		'UNIX_TIMESTAMP' => array('number', 1),
		'UPPER' => array('string', 1),
		'UTC_DATE' => array('date', 0),
		'UTC_TIME' => array('time', 0),
		'UTC_TIMESTAMP' => array('datetime', 0),
		'VAR_POP' => array('number', 1),
		'VAR_SAMP' => array('number', 1),
		'WEEKDAY' => array('number', 1),
		'WEEKOFYEAR' => array('number', 1)
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

		$info = self::$_functions[$lookupName];
		$givenArgs = count($this->_arguments);

		if (isset($info[2])) {
			$minArgs = $info[1];
			$maxArgs = $info[2];

			if ($givenArgs < $minArgs) {
				throw new Exception("DPQL function $name expects at least $minArgs argument(s).");
			}
			if ($maxArgs >= 0 && $givenArgs > $maxArgs) {
				throw new Exception("DPQL function $name expects at least $maxArgs argument(s).");
			}
		} else if ($givenArgs != $info[1]) {
			throw new Exception("DPQL function $name expects $info[1] argument(s).");
		}

		$valuesSql = array();
		$valuesNames = array();
		foreach ($this->_arguments AS $arg) {
			$prepped = $arg->prepare($statement, $section, $stack, $select, $result);
			$valuesSql[] = $prepped->sql();
			$valuesNames[] = $prepped->name();
		}

		$sql = strtoupper($this->_name) . '(' . implode(', ', $valuesSql) . ')';
		return new Prepared($sql, "$this->_name(" . implode(', ', $valuesNames) . ')', false, $info[0]);
	}
}