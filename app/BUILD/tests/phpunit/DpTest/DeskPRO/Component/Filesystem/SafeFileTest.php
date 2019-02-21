<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Component\Filesystem;

use DeskPRO\Component\Filesystem\SafeFile;
use DpTest\DeskProTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class SafeFileTest extends DeskProTestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    public function setUp()
    {
        $this->root = vfsStream::setup('instance_test');
        vfsStream::create([
            'var' => [
                'etc' => [
                    'mysql' => [
                        'my.cnf' => 'xxx',
                    ],
                ],
                'log' => [
                    'nginx' => [
                        'access.log',
                    ],
                    'mail.log' => 'xxx',
                ],
                'www' => [
                    'deskpro' => [
                        'attachments' => [
                            'example.txt' => 'xxx',
                        ],
                        'var' => [
                            'cache' => [
                                'example.txt' => 'xxx',
                            ],
                        ],
                    ],
                ],
            ],
        ], $this->root);

        SafeFile::resetBlacklist();
        SafeFile::addBlacklistDir('/etc/');
        SafeFile::addBlacklistDir('/var/log/');
        SafeFile::addBlacklistDir('/var/www/deskpro/attachments/');
        SafeFile::addBlacklistDir('/var/www/deskpro/backups/');
    }

    public function testSafeFileAny()
    {
        $this->assertTrue(SafeFile::isValid('/var/www/deskpro/var/cache/example.txt', SafeFile::ANY));
        $this->assertTrue(SafeFile::isValid('/var/www/deskpro/var/cache/dontexist', SafeFile::ANY));
        $this->assertTrue(SafeFile::isValid('/tmp/foo', SafeFile::ANY));
        $this->assertTrue(SafeFile::isValid('/var/etc/mysql/my.cnf', SafeFile::ANY));

        $this->assertFalse(SafeFile::isValid('/var/www/deskpro/attachments/example.txt', SafeFile::ANY));
        $this->assertFalse(SafeFile::isValid('/var/www/deskpro/attachments/dontexist', SafeFile::ANY));
    }

    public function testException()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        SafeFile::assertValid('/var/www/deskpro/attachments/example.txt', SafeFile::ANY);
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

    public function testSafeFileData()
    {
        $testFile = 'data:text/plain;charset=utf-8;base64,VEVTVA==';

        $this->assertFalse(SafeFile::isValid('/var/log/mail.log', SafeFile::DATA));
        $this->assertFalse(SafeFile::isValid('file://var/log/mail.log', SafeFile::DATA));
        $this->assertFalse(SafeFile::isValid('ftp://var/log/mail.log', SafeFile::DATA));
        $this->assertTrue(SafeFile::isValid($testFile, SafeFile::DATA));
    }
}
