<?php

namespace DpTest\DeskPRO\Bundle\AppStoreBundle\Infrastructure;

use DeskPRO\Bundle\AppStoreBundle\Infrastructure\IdentifierParser;
use DpTest\DeskProTestCase;

class IdentifierParserTest extends DeskProTestCase
{
    public function testParseApplicationRef()
    {
        $parser = new IdentifierParser();
        $ref    = $parser->parseApplicationRef('app:123');

        $this->assertNotNull($ref);
        $this->assertEquals('123', $ref->getIdentifier());
        $this->assertFalse($ref->isName());

        $ref = $parser->parseApplicationRef('123');
        $this->assertEquals('123', $ref->getIdentifier());
        $this->assertFalse($ref->isName());
    }

    public function testRecognizeApplicationInstanceId()
    {
        $parser = new IdentifierParser();
        $ref    = $parser->recognizeNumericIdentifier('123');
        $this->assertTrue($ref);

        $ref = $parser->recognizeNumericIdentifier('app:123');
        $this->assertFalse($ref);
    }
}
