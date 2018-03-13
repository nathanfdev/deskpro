<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\Entity\ObjectAlias;

use DeskPRO\Bundle\AppBundle\Entity\ObjectAlias\FullyQualifiedAppName;
use DeskPRO\Bundle\AppBundle\ObjectAlias\QualifiedName;
use DpTest\DeskProTestCase;

class FullyQualifiedAppNameTest extends DeskProTestCase
{
    public function testParseArrayReturnsExpectedObject()
    {
        $qname = new QualifiedName('jira', ['app', '5']);
        $actualQname = FullyQualifiedAppName::parseArray($qname->toList());

        $expectedQname = new FullyQualifiedAppName('5', 'jira');
        $this->assertTrue($expectedQname->equals($actualQname));

        $qname = new QualifiedName('jira', ['app', '5', 'trigger']);
        $actualQname = FullyQualifiedAppName::parseArray($qname->toList());
        $this->assertNull($actualQname);
    }


    public function testParseNameReturnsExpectedObject()
    {
        $qname = new QualifiedName('jira', ['app', '5']);
        $actualQname = FullyQualifiedAppName::parseName($qname);

        $expectedQname = new FullyQualifiedAppName('5', 'jira');
        $this->assertTrue($expectedQname->equals($actualQname));

        $qname = new QualifiedName('jira', ['app', '5', 'trigger']);
        $actualQname = FullyQualifiedAppName::parseName($qname);
        $this->assertNull($actualQname);
    }

    public function testEqualsReturnsTrue()
    {
        $qname = new FullyQualifiedAppName('5', 'jira');
        $otherQName = new FullyQualifiedAppName('5', 'jira');

        $this->assertTrue($qname->equals($otherQName));
    }
}
