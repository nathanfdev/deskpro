<?php

namespace DpTest\DeskPRO\Bundle\AppStoreBundle\Domain;

use DeskPRO\Bundle\AppStoreBundle\Domain;
use DpTest\DeskProTestCase;

class AppVersionTest extends DeskProTestCase
{
    public function testParseValidSemver()
    {
        $semver = '0.1.3-alpha.54';
        $version = Domain\AppVersion::parse($semver);

        $this->assertEquals(0, $version->getMajor());
        $this->assertEquals(1, $version->getMinor());
        $this->assertEquals(3, $version->getPatch());
        $this->assertEquals('alpha.54', $version->getLabel());
    }

    public function testInvalidSemverThrowsError()
    {
        $semver = 'very wrong 0.1.3-alpha.54';
        $expected = null;
        try {
            $version = Domain\AppVersion::parse($semver);
        } catch (\DomainException $e) {
            $expected = $e;
        }
        $this->assertNotNull($expected);
    }
}
