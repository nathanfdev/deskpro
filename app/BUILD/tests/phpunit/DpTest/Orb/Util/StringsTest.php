<?php

namespace DpTest\Orb\Util;

use DpTest\DeskProTestCase;
use Orb\Util\Strings;

class StringsTest extends DeskProTestCase
{
    public function testNullClase()
    {
        $this->assertEquals(
            [],
            Strings::splitStringIntoArray(
                null,
                12
            )
        );

        $this->assertEquals(
            [],
            Strings::splitStringIntoArray(
                '',
                12
            )
        );
    }

    public function testStringSplitMaxLength()
    {
        $this->assertEquals(
            [
                'hello there',
                'how are you',
                'doing today?',
            ],
            Strings::splitStringIntoArray(
                'hello there how are you doing today?',
                12
            )
        );
    }

    public function testCaseWhereLineStartsOrEndsWithWhitespace()
    {
        $this->assertEquals(
            [
                'hello there',
                'how are you',
                'doing today',
                '?',
            ],
            Strings::splitStringIntoArray(
                ' hello there how are you doing today ? ',
                12
            )
        );
    }
}
