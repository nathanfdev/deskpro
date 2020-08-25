<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Component\Util;

use DeskPRO\Component\Util\IpUtils;
use DpTest\DeskProTestCase;

class IpUtilsTest extends DeskProTestCase
{
    public function testIsLocalNetwork()
    {
        $this->assertTrue(
            IpUtils::guessIsLocalNetworkHost("localhost")
        );
        $this->assertTrue(IpUtils::guessIsLocalNetworkHost("192.168.1.1"));
        $this->assertTrue(IpUtils::guessIsLocalNetworkHost(""));
        $this->assertTrue(IpUtils::guessIsLocalNetworkHost("::1"));
        $this->assertTrue(IpUtils::guessIsLocalNetworkHost("10.42.24.18"));
        $this->assertFalse(IpUtils::guessIsLocalNetworkHost("google.com"));
        $this->assertFalse(IpUtils::guessIsLocalNetworkHost("172.217.169.14"));
    }

    public function testIsHostUserCallable()
    {
        $GLOBALS['DPC_TESTING_IPUTILS_HOST_CALLABLE'] = true;

        $this->assertTrue(
            IpUtils::isHostUserCallable("google.com"),
            "google.com should be allowed because it is not an interal host"
        );

        $this->assertTrue(
            IpUtils::isHostUserCallable("172.217.169.14"),
            "non-private ip should be allowed"
        );

        $this->assertFalse(
            IpUtils::isHostUserCallable("localhost"),
            "localhost is not allowed"
        );
        $this->assertFalse(
            IpUtils::isHostUserCallable("foo.internal.deskpro.com"),
            "internal deskpro host name is not allowed"
        );
        $this->assertFalse(
            IpUtils::isHostUserCallable("foo.deskpro-service.com"),
            "deskpro service host name is not allowed"
        );
        $this->assertFalse(
            IpUtils::isHostUserCallable("foo.es.amazonaws.com"),
            "aws es host is not allowed"
        );

        // any other host is allowed
        $this->assertTrue(
            IpUtils::isHostUserCallable("example.com"),
            "any other host is allowed"
        );

        //... unless it resolves to a priv ip
        $GLOBALS['DPC_TESTING_IPUTILS_HOST_RESOLVE'] = ['example.com' => "10.42.24.18"];
        $this->assertFalse(
            IpUtils::isHostUserCallable("example.com"),
            "host that resolves to priv ip is not allowed"
        );

        $GLOBALS['DPC_TESTING_IPUTILS_HOST_RESOLVE'] = null;
    }

    public function testIsUrlUserCallable()
    {
        $GLOBALS['DPC_TESTING_IPUTILS_HOST_CALLABLE'] = true;

        $this->assertFalse(
            IpUtils::isUrlUserCallable("foo"),
            "non-url is not allowed"
        );

        $this->assertTrue(
            IpUtils::isUrlUserCallable("http://google.com/"),
            "google.com should be allowed because it is not an interal host"
        );

        $this->assertFalse(
            IpUtils::isUrlUserCallable("telnet://google.com/"),
            "non-http is not allowed"
        );

        $this->assertTrue(
            IpUtils::isUrlUserCallable("telnet://google.com/", false),
            "non-http is not allowed unless expectHttp is off"
        );

        $this->assertTrue(
            IpUtils::isUrlUserCallable("https://172.217.169.14/"),
            "non-private ip should be allowed"
        );

        $this->assertFalse(
            IpUtils::isUrlUserCallable("http://localhost/"),
            "localhost is not allowed"
        );
        $this->assertFalse(
            IpUtils::isUrlUserCallable("http://foo.internal.deskpro.com"),
            "internal deskpro host name is not allowed"
        );
        $this->assertFalse(
            IpUtils::isUrlUserCallable("http://foo.deskpro-service.com"),
            "deskpro service host name is not allowed"
        );
        $this->assertFalse(
            IpUtils::isUrlUserCallable("http://foo.es.amazonaws.com:9001/index"),
            "aws es host is not allowed"
        );

        // any other host is allowed
        $this->assertTrue(
            IpUtils::isUrlUserCallable("https://example.com/foo/bar"),
            "any other host is allowed"
        );

        //... unless it resolves to a priv ip
        $GLOBALS['DPC_TESTING_IPUTILS_HOST_RESOLVE'] = ['example.com' => "10.42.24.18"];
        $this->assertFalse(
            IpUtils::isUrlUserCallable("https://example.com/foo/bar"),
            "host that resolves to priv ip is not allowed"
        );

        $GLOBALS['DPC_TESTING_IPUTILS_HOST_RESOLVE'] = null;
    }
}
