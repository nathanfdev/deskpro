<?php
namespace DpUnitTests\Orb\Util;

use Orb\Util\OptionsArray;
use Orb\Util\CheckedOptionsArray;

class OptionsArrayTest extends \DpUnitTestCase
{
	public function testOptions()
	{
		$options =  new OptionsArray(array(
			'a' => 1,
			'b' => 2,
			'c' => 3,
			'd' => 4
		));

		$this->assertTrue($options->has('a'));
		$this->assertFalse($options->has('z'));

		$this->assertTrue($options->hasAny(array('z', 'x', 'a')));
		$this->assertFalse($options->hasAny(array('z', 'x', 'y')));

		$this->assertTrue($options->hasAll(array('a', 'b', 'c')));
		$this->assertFalse($options->hasAll(array('a', 'b', 'z')));

		$this->assertTrue(isset($options['c']));
		$this->assertFalse(isset($options['z']));

		$this->assertTrue(isset($options->c));
		$this->assertFalse(isset($options->z));

		$this->assertEquals(4, count($options));

		$this->assertEquals(3, $options->get('c'));
		$this->assertEquals(3, $options['c']);
		$this->assertEquals(3, $options->c);

		$options->set('c', 300);
		$this->assertEquals(300, $options->get('c'));

		$options['c'] = 300;
		$this->assertEquals(300, $options->get('c'));

		$options->c = 300;
		$this->assertEquals(300, $options->get('c'));

		$options->setDefault('c', 1000);
		$this->assertEquals(300, $options->get('c'));

		$options->setArray(array('n' => 100));
		$this->assertEquals(5, count($options));

		$options->setArray(array('a' => 100));
		$this->assertEquals(100, $options->get('a'));

		$options->setArrayDefault(array('b' => 200));
		$this->assertEquals(2, $options->get('b'));

		$options->setAll(array('z' => 1, 'x' => 2));
		$this->assertEquals(array('z' => 1, 'x' => 2), $options->all());
	}

	/***
	 * @expectedException
	 */
	public function testCheckedEnsureFail()
	{
		$this->setExpectedException('Orb\Util\CheckedOptionsException');
		$options = $this->getCheckedOptions();
		$options->ensureRequired();
	}

	public function testCheckedValidFail()
	{
		$this->setExpectedException('Orb\Util\CheckedOptionsException');
		$options = $this->getCheckedOptions();
		$options->set('x', 1);
	}

	public function testCheckedNullFail()
	{
		$this->setExpectedException('Orb\Util\CheckedOptionsException');
		$options = $this->getCheckedOptions();
		$options->set('a', null);
	}

	public function testCheckedCallbackFail()
	{
		$this->setExpectedException('Orb\Util\CheckedOptionsException');
		$options = $this->getCheckedOptions();
		$options->set('e', 1);
	}

	public function testCheckedTypeFail()
	{
		$this->setExpectedException('Orb\Util\CheckedOptionsException');
		$options = $this->getCheckedOptions();
		$options->set('d', new OptionsArray());
	}

	public function testCheckedOptions()
	{
		$options = $this->getCheckedOptions();
		$options->setAll(array(
			'a' => 1,
			'b' => 2,
			'c' => 3,
			'd' => new \DateTime(),
			'e' => 100
		));

		$options->ensureRequired();
	}


	/**
	 * @return CheckedOptionsArray
	 */
	private function getCheckedOptions()
	{
		$options = new CheckedOptionsArray();
		$options->addRequiredNames('a', 'b', 'c');
		$options->addValidNames('a', 'b', 'b', 'd', 'e');
		$options->addNotNullOption('a', 'b', 'c');
		$options->addTypeCheckedOption('d', 'DateTime');
		$options->addCallbackCheckedOption('e', function ($val) {
			return $val === 100;
		});

		return $options;
	}
}