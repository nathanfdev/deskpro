<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
