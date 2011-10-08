<?php

namespace Orb\Tests\Util;

use Orb\Util\Strings;

class StringsTest extends \PHPUnit_Framework_TestCase
{
	public function testBasic()
	{
		$string = '<script>"testing123"</script> and more"';
		$expected_string = '<script>\\"testing123\\"\\x3C/script> and more\\"';
		$this->assertEquals($expected_string, Strings::addslashesJs($string));

		$this->assertEquals(1, preg_match('#^[aeiou]{10}$#', Strings::random(10, 'aeiou')));
		$this->assertEquals(13, strlen(Strings::randomPronounceable(13)));
	}
}