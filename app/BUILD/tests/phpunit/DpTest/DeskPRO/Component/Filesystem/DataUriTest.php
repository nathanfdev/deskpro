<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Component\Filesystem;

use DeskPRO\Component\Filesystem\DataUri;
use DpTest\DeskProTestCase;

class DataUriTest extends DeskProTestCase
{
    private static $cases = [
        [
            'dataUri' => 'data:,hello',
            'data' => 'hello',
            'mediaType' => null,
        ],
        [
            'dataUri' => 'data:text/plain,hello',
            'data' => 'hello',
            'mediaType' => 'text/plain',
        ],
        [
            'dataUri' => 'data:,Hello%2C%20World%21',
            'data' => 'Hello, World!',
            'mediaType' => null,
        ],
        [
            'dataUri' => 'data:text/plain;base64,SGVsbG8sIFdvcmxkIQ==',
            'data' => 'Hello, World!',
            'mediaType' => 'text/plain',
        ],
        [
            'dataUri' => 'data:text/html,%3Ch1%3EHello%2C%20World%21%3C%2Fh1%3E',
            'data' => '<h1>Hello, World!</h1>',
            'mediaType' => 'text/html',
        ],
    ];

    public function testDataUri()
    {
        foreach (self::$cases as $idx => $case) {
            $this->assertTrue(DataUri::isDataUri($case['dataUri']), "Case $idx: isDataUri");
            $val = DataUri::decode($case['dataUri']);

            $this->assertEquals($case['data'], $val->data, "Case $idx: data matches");
            $this->assertEquals($case['mediaType'], $val->mediaType, "Case $idx: media type matches");
        }
    }

    public function testNotDataUri()
    {
        $this->assertFalse(DataUri::isDataUri('/tmp/foo'));
        $this->assertFalse(DataUri::isDataUri('data:bar'));
    }

    public function testDecodeNotDataUri()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        DataUri::decode('foo');
    }

    public function testDecodeNotDataUri2()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        DataUri::decode('data:foo');
    }

    public function testDecodeInvalidMediaType()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        DataUri::decode('data:text,foo');
    }

    public function testDecodeInvalidMediaType2()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        DataUri::decode('data:text/plain!,foo');
    }

    public function testDecodeInvalidEncStr()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        DataUri::decode('data:text/plain;base69,foo');
    }

    public function testDecodeInvalidBase64String()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        DataUri::decode('data:text/plain;base64,foo!');
    }
}
