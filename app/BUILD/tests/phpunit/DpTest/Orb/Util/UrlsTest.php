<?php

namespace DpTest\Orb\Util;

use DpTest\DeskProTestCase;
use Orb\Util\Urls;

class UrlsTest extends DeskProTestCase
{
    public function testEmailDomain()
    {
        // true
        $this->assertTrue(
            Urls::verifyEmailDomain('chris.tickner@deskpro.com', 'deskpro.com')
        );

        // false
        $this->assertFalse(
            Urls::verifyEmailDomain('chris.tickner@deskpros.com', 'deskpro.com')
        );

        $this->assertFalse(
            Urls::verifyEmailDomain('chris.tickner@gmail.com', 'deskpro.com')
        );

        // subdomain match
        $this->assertTrue(
            Urls::verifyEmailDomain('chris.tickner@support.deskpro.com', 'support.deskpro.com')
        );

        // subdomain fail
        $this->assertFalse(
            Urls::verifyEmailDomain('chris.tickner@support.deskpro.com', 'deskpro.com')
        );

        // sanity tests (whitespace)
        $this->assertTrue(
            Urls::verifyEmailDomain(' chris.tickner@deskpro.com   ', ' deskpro.com  ')
        );
    }
}
