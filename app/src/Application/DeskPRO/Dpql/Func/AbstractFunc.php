<?php

namespace Application\DeskPRO\Dpql\Func;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;

abstract class AbstractFunc
{
	protected static $_functionMap = array(
		'COUNT' => 'Count',
		'PERCENT' => 'Percent',
		'PRINT' => 'Printable',
		'X' => 'X',
		'Y' => 'Y'
	);

	protected $_name;
	protected $_arguments;

	abstract public function prepare(
		Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
	);

	protected function __construct($name, array $arguments = array())
	{
		$this->_name = $name;
		$this->_arguments = $arguments;
	}

	public static function create($name, array $arguments = array())
	{
		$name = strtoupper($name);
		if (isset(self::$_functionMap[$name])) {
			$map = __NAMESPACE__ . '\\' . self::$_functionMap[$name];
			return new $map($name, $arguments);
		} else {
			return new SqlPass($name, $arguments);
		}
	}
}