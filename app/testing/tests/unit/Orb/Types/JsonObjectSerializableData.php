<?php

namespace DpUnitTests\Orb\Types;

use Orb\Types\JsonObjectSerializable;

/**
 * TestObject
 */
class TestObject implements JsonObjectSerializable
{
	public $a;
	public $b;
	public $c;
	protected $_y;
	private $_z;

	public function __construct($a, $b, $c, $y = null, $z = null)
	{
		$this->a = $a;
		$this->b = $b;
		$this->c = $c;
		$this->_y = $y;
		$this->_z = $z;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return implode(',', $this->serializeJsonArray());
	}

	/**
	 * {@inheritDoc}
	 */
	public function serializeJsonArray()
	{
		return array(
			'a'  => $this->a,
			'b'  => $this->b,
			'c'  => $this->b,
			'_y' => $this->_y,
			'_z' => $this->_z,
		);
	}


	/**
	 * {@inheritDoc}
	 */
	public static function unserializeJsonArray(array $data)
	{
		$obj = new self(
			$data['a'],
			$data['b'],
			$data['c'],
			$data['_y'],
			$data['_z']
		);
		return $obj;
	}
}