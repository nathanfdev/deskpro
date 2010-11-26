<?php

namespace DeskPRO\Tests\Domain;

use DeskPRO\Domain\DomainObject;

class TestDomainObject extends DomainObject
{
	protected $_var1 = 'var';
	protected $name = 'foo';
	protected $answer = 42;
	protected $complex_name = 'test';
	protected $__double = 'trouble';

	public function getName()
	{
		return strtoupper($this->name);
	}

	public function setAnswer($value)
	{
		$this->answer = 42;
	}

	public function getComplexName()
	{
		return $this->complex_name . '!';
	}
}

class DomainObjectTest extends \PHPUnit_Framework_TestCase
{
	public function testMagic()
	{
		$obj = new TestDomainObject();

		$this->assertEquals('FOO', $obj['name']);

		$obj['name'] = 'bar';
		$this->assertEquals('BAR', $obj['name']);

		$obj['answer'] = 1000;
		$this->assertEquals(42, $obj['answer']);

		$this->assertEquals('test!', $obj['complex_name']);
		
		$obj->setComplexName('testing');
		$this->assertEquals('testing!', $obj['complex_name']);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testBadVar1()
	{
		$obj = new TestDomainObject();
		$obj['_var1'];
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testBadVar2()
	{
		$obj = new TestDomainObject();
		$obj['__double'];
	}

	public function testToArray()
	{
		$obj = new TestDomainObject();
		$arr = $obj->toArray();

		$this->assertEquals(array(
			'name' => 'FOO',
			'answer' => 42,
			'complex_name' => 'test!'
		), $arr);
	}
}