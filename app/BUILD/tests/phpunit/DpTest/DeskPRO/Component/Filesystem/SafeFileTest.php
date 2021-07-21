<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Component\Filesystem;

require __DIR__.'/ExamplePharExploitClass.php';

use DeskPRO\Component\Filesystem\SafeFile;
use DpTest\DeskProTestCase;
use ExamplePharExploitClass;

class SafeFileTest extends DeskProTestCase
{
    public function setUp()
    {
        SafeFile::resetBlacklist();
        SafeFile::addBlacklistDir('/etc/');
        SafeFile::addBlacklistDir('/var/log/');
        SafeFile::addBlacklistDir('/var/www/deskpro/attachments/');
        SafeFile::addBlacklistDir('/var/www/deskpro/backups/');
    }

    public function testSafeFileAny()
    {
        $this->assertTrue(SafeFile::isValid('/var/www/deskpro/var/cache/example.txt', SafeFile::UNSPECIFIED));
        $this->assertTrue(SafeFile::isValid('/var/www/deskpro/var/cache/dontexist', SafeFile::UNSPECIFIED));
        $this->assertTrue(SafeFile::isValid('/tmp/foo', SafeFile::UNSPECIFIED));
        $this->assertTrue(SafeFile::isValid('/var/etc/mysql/my.cnf', SafeFile::UNSPECIFIED));
        $this->assertTrue(SafeFile::isValid('/var/etc/mysql/../../etc/mysql/my.cnf', SafeFile::UNSPECIFIED));

        $this->assertFalse(SafeFile::isValid('/var/www/deskpro/attachments/example.txt', SafeFile::UNSPECIFIED));
        $this->assertFalse(SafeFile::isValid('/var/www/deskpro/attachments/dontexist', SafeFile::UNSPECIFIED));
        $this->assertFalse(SafeFile::isValid('/var/www/deskpro/attachments/../attachments/dontexist', SafeFile::UNSPECIFIED));

        // realpath() will resolve empty strings to the pwd, @see https://www.php.net/manual/en/function.realpath.php
        $this->assertFalse(SafeFile::isValid('', SafeFile::UNSPECIFIED));
    }

    public function testException()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        SafeFile::assertValid('/var/www/deskpro/attachments/example.txt', SafeFile::UNSPECIFIED);
    }

    public function testSafeFileWhitelist()
    {
        $this->assertTrue(SafeFile::isValid('/tmp/foo', '/tmp/'));
        $this->assertTrue(SafeFile::isValid('/tmp/foo', ['/bar/', '/tmp/']));

        $this->assertFalse(SafeFile::isValid('/var/www/deskpro/attachments/example.txt', '/tmp/'));
        $this->assertFalse(SafeFile::isValid('/run/foo', '/tmp/'));

        $this->assertTrue(SafeFile::isValid('/run/foo/bar/baz', ['/run/bar/', '/run/foo/bar/']));
    }

    public function testSafeFileWhitelistSpecific()
    {
        $this->assertFalse(SafeFile::isValid('/var/log/mail.log', '/var/log/nginx'));
        $this->assertTrue(SafeFile::isValid('/var/log/mail.log', '/var/log/mail.log'));
        $this->assertTrue(SafeFile::isValid('/var/log/mail.log', '/var/log/'));

        $this->assertTrue(SafeFile::isValid('/var/log/nginx/access.log', '/var/log/nginx/'));
    }

    public function testSafeFileFile()
    {
        $this->assertFalse(SafeFile::isValid('/var/log/mail.log', SafeFile::UNSPECIFIED));
        $this->assertFalse(SafeFile::isValid('ftp://var/log/mail.log', SafeFile::UNSPECIFIED));
        $this->assertTrue(SafeFile::isValid('file://var/log/mail.log', SafeFile::UNSPECIFIED));
    }

    public function testSafeFileData()
    {
        $testFile = 'data:text/plain;charset=utf-8;base64,VEVTVA==';
        $this->assertFalse(SafeFile::isValid($testFile, SafeFile::UNSPECIFIED));
    }

    public function testIsValidResolvedRealpath()
    {
        $whitelist = [
            __DIR__.'/resources',
        ];

        // Valid
        $this->assertTrue(SafeFile::isValid(__DIR__.'/resources/example.txt', __DIR__));
        $this->assertTrue(SafeFile::isValid(__DIR__.'/../../Component/Filesystem/resources/example.txt', __DIR__));
        $this->assertTrue(SafeFile::isValid('file://'.__DIR__.'/resources/example.txt', __DIR__));
        $this->assertTrue(SafeFile::isValid('file://'.__DIR__.'/../../Component/Filesystem/resources/example.txt', __DIR__));

        // Invalid
        $this->assertFalse(SafeFile::isValid('phar://'.__DIR__.'/resources/example.phar', $whitelist));
        $this->assertFalse(SafeFile::isValid('phar://'.__DIR__.'/../../Component/Filesystem/resources/example.phar', $whitelist));
    }

    public function testisValidPathString()
    {
        // Valid
        $this->assertTrue(SafeFile::isValidPathString('/foo/bar'));
        $this->assertTrue(SafeFile::isValidPathString('./foo/bar'));
        $this->assertTrue(SafeFile::isValidPathString('../foo/bar'));
        $this->assertTrue(SafeFile::isValidPathString('FooBundle:Bar:baz/foo'));
        $this->assertTrue(SafeFile::isValidPathString('FooBundle::baz/foo'));
        $this->assertTrue(SafeFile::isValidPathString('BarBundle:foof7e12a477350.58295937'));
        $this->assertTrue(SafeFile::isValidPathString('@FooProfiler/Baz/bar.svg'));
        $this->assertTrue(SafeFile::isValidPathString('::baz/foo'));
        $this->assertTrue(SafeFile::isValidPathString('C:/foo/bar'));
        $this->assertTrue(SafeFile::isValidPathString('foo/bar'));
        $this->assertTrue(SafeFile::isValidPathString('file://foo'));
        $this->assertTrue(SafeFile::isValidPathString('file:\\\\foo'));

        // Invalid
        $this->assertFalse(SafeFile::isValidPathString('http://foo'));
        $this->assertFalse(SafeFile::isValidPathString('http:\\\\foo'));
        $this->assertFalse(SafeFile::isValidPathString('data://foo'));
        $this->assertFalse(SafeFile::isValidPathString('data:foo'));
        $this->assertFalse(SafeFile::isValidPathString('https://foo'));
        $this->assertFalse(SafeFile::isValidPathString('phar:///foo'));
        $this->assertFalse(SafeFile::isValidPathString('phar://foo'));
        $this->assertFalse(SafeFile::isValidPathString('phar:\\\\foo'));
        $this->assertFalse(SafeFile::isValidPathString('zip:///foo'));
        $this->assertFalse(SafeFile::isValidPathString('ftp:///foo'));
        $this->assertFalse(SafeFile::isValidPathString(' phar:///foo'));
        $this->assertFalse(SafeFile::isValidPathString('  phar:///foo'));
        $this->assertFalse(SafeFile::isValidPathString('phar%3A%2F%2Ffoo'));
    }

    public function testPharExploit()
    {
        // a test to confirm our POC actually works, so the next test can confirm we've mitigated it

        ExamplePharExploitClass::reset();
        $this->assertTrue(file_exists('phar://'.__DIR__.'/resources/example0.phar/test_file.txt'));
        $this->assertEquals('EXPLOITED_VALUE', ExamplePharExploitClass::getLastValue());

        // arun it again to confirm that resetting it works (confirs we can run multiple tests using the same exploit class)
        ExamplePharExploitClass::reset();
        $this->assertTrue(is_dir('phar://'.__DIR__.'/resources/example1.phar/subdir'));
        $this->assertEquals('EXPLOITED_VALUE', ExamplePharExploitClass::getLastValue());
    }

    public function testPharExploitMitigation()
    {
        ExamplePharExploitClass::reset();
        $this->assertFalse(SafeFile::file_exists('phar://'.__DIR__.'/resources/example2.phar/test_file.txt', SafeFile::UNSPECIFIED));
        $this->assertNull(ExamplePharExploitClass::getLastValue());

        ExamplePharExploitClass::reset();
        $this->assertFalse(SafeFile::is_dir('phar://'.__DIR__.'/resources/example3.phar/subdir', SafeFile::UNSPECIFIED));
        $this->assertNull(ExamplePharExploitClass::getLastValue());

        ExamplePharExploitClass::reset();
        $this->assertFalse(SafeFile::isValidPathString('phar://'.__DIR__.'/resources/example4.phar/test_file.txt', SafeFile::UNSPECIFIED));
        $this->assertNull(ExamplePharExploitClass::getLastValue());
    }
}
