<?php

namespace Application\DeskPRO\Dpql\Placeholder;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Statement\Part\AbstractPart;

abstract class AbstractPlaceholder
{
	protected static $_placeholderMap = array(
		'TODAY' => 'Today',
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
			throw new \Exception("Unknown placeholder $name specified.");
		}
	}
}