<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Component\Filesystem;

use DeskPRO\Component\Filesystem\SafeFile;
use DpTest\DeskProTestCase;

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

    public function testSafeFileHttp()
    {
        $this->assertFalse(SafeFile::isValid('/var/log/mail.log', SafeFile::HTTP));
        $this->assertFalse(SafeFile::isValid('file://var/log/mail.log', SafeFile::HTTP));
        $this->assertFalse(SafeFile::isValid('ftp://var/log/mail.log', SafeFile::HTTP));
        $this->assertTrue(SafeFile::isValid('http://google.com/', SafeFile::HTTP));
        $this->assertTrue(SafeFile::isValid('https://google.com/', SafeFile::HTTP));
        $this->assertTrue(SafeFile::isValid('HTTPS://google.com/', SafeFile::HTTP));
    }

    public function testSafeFileFile()
    {
        $this->assertFalse(SafeFile::isValid('/var/log/mail.log', SafeFile::FILE));
        $this->assertFalse(SafeFile::isValid('ftp://var/log/mail.log', SafeFile::FILE));
        $this->assertTrue(SafeFile::isValid('file://var/log/mail.log', SafeFile::FILE));
    }

    public function testSafeFileData()
    {
        $testFile = 'data:text/plain;charset=utf-8;base64,VEVTVA==';

        $this->assertFalse(SafeFile::isValid('/var/log/mail.log', SafeFile::DATA));
        $this->assertFalse(SafeFile::isValid('file://var/log/mail.log', SafeFile::DATA));
        $this->assertFalse(SafeFile::isValid('ftp://var/log/mail.log', SafeFile::DATA));
        $this->assertTrue(SafeFile::isValid($testFile, SafeFile::DATA));
    }

    public function testResolve()
    {
        $this->assertEquals('/x/var/log/mail.log', SafeFile::tryResolvePath('/x/var/./log/nginx/../mail.log'));
        $this->assertEquals('C:/x/Windows/etc/hosts', SafeFile::tryResolvePath('C:\\x\\Windows\\etc\\..\\etc\\hosts'));

        $this->assertEquals('http://foo.com/', SafeFile::tryResolvePath('http://foo.com/'));

        $testFile = 'data:text/plain;charset=utf-8;base64,VEVTVA==';
        $this->assertEquals($testFile, SafeFile::tryResolvePath($testFile));
    }

    public function testIsValidResolvedRealpath()
    {
        $whitelist = [
            __DIR__.'/resources',
        ];

        // Valid
        $this->assertTrue(SafeFile::isValid(__DIR__.'/resources/example.txt', $whitelist));
        $this->assertTrue(SafeFile::isValid(__DIR__.'/../../Component/Filesystem/resources/example.txt', $whitelist));
        $this->assertTrue(SafeFile::isValid('file://'.__DIR__.'/resources/example.txt', SafeFile::FILE));
        $this->assertTrue(SafeFile::isValid('file://'.__DIR__.'/../../Component/Filesystem/resources/example.txt', SafeFile::FILE));

        // Invalid
        $this->assertFalse(SafeFile::isValid('phar://'.__DIR__.'/resources/example.phar', $whitelist));
        $this->assertFalse(SafeFile::isValid('phar://'.__DIR__.'/../../Component/Filesystem/resources/example.phar', $whitelist));
    }

    public function testIsValidPathPrefix()
    {
        // Valid
        $this->assertTrue(SafeFile::isValidPathPrefix('/foo/bar'));
        $this->assertTrue(SafeFile::isValidPathPrefix('./foo/bar'));
        $this->assertTrue(SafeFile::isValidPathPrefix('../foo/bar'));
        $this->assertTrue(SafeFile::isValidPathPrefix('FooBundle:Bar:baz/foo'));
        $this->assertTrue(SafeFile::isValidPathPrefix('FooBundle::baz/foo'));
        $this->assertTrue(SafeFile::isValidPathPrefix('BarBundle:foof7e12a477350.58295937'));
        $this->assertTrue(SafeFile::isValidPathPrefix('@FooProfiler/Baz/bar.svg'));
        $this->assertTrue(SafeFile::isValidPathPrefix('::baz/foo'));
        $this->assertTrue(SafeFile::isValidPathPrefix('C:/foo/bar'));
        $this->assertTrue(SafeFile::isValidPathPrefix('foo/bar'));
        $this->assertTrue(SafeFile::isValidPathPrefix('http://foo'));
        $this->assertTrue(SafeFile::isValidPathPrefix('https://foo'));
        $this->assertTrue(SafeFile::isValidPathPrefix('file://foo'));
        $this->assertTrue(SafeFile::isValidPathPrefix('data://foo'));

        // Invalid
        $this->assertFalse(SafeFile::isValidPathPrefix('phar:///foo'));
        $this->assertFalse(SafeFile::isValidPathPrefix('zip:///foo'));
        $this->assertFalse(SafeFile::isValidPathPrefix('ftp:///foo'));
        $this->assertFalse(SafeFile::isValidPathPrefix(' phar:///foo'));
        $this->assertFalse(SafeFile::isValidPathPrefix('  phar:///foo'));
        $this->assertFalse(SafeFile::isValidPathPrefix('phar%3A%2F%2Ffoo'));
    }
}
