<?php

namespace DpTest\DeskPRO\Component\Filesystem;

use DeskPRO\Component\Filesystem\SafeFile;
use DpTest\DeskProTestCase;

class SafeFileWinTest extends DeskProTestCase
{
    public function setUp()
    {
        SafeFile::resetBlacklist();
        SafeFile::addBlacklistDir('C:\\etc\\');
        SafeFile::addBlacklistDir('C:\\var\\log\\');
        SafeFile::addBlacklistDir('C:\\var\\www\\deskpro\\attachments\\');
        SafeFile::addBlacklistDir('C:\\var\\www\\deskpro\\backups\\');
    }

    public function testSafeFileAny()
    {
        $this->assertTrue(SafeFile::isValid('C:\\var\\www\\deskpro\\var\\cache\\example.txt', SafeFile::UNSPECIFIED));
        $this->assertTrue(SafeFile::isValid('C:\\var\\www\\deskpro\\var\\cache\\dontexist', SafeFile::UNSPECIFIED));
        $this->assertTrue(SafeFile::isValid('C:\\tmp\\foo', SafeFile::UNSPECIFIED));
        $this->assertTrue(SafeFile::isValid('C:\\var\\etc\\mysql\\my.cnf', SafeFile::UNSPECIFIED));

        $this->assertFalse(SafeFile::isValid('C:\\var\\www\\deskpro\\attachments\\example.txt', SafeFile::UNSPECIFIED));
        $this->assertFalse(SafeFile::isValid('C:\\var\\www\\deskpro\\attachments\\dontexist', SafeFile::UNSPECIFIED));
    }

    public function testSafeFileReverseSlashAny()
    {
        $this->assertTrue(SafeFile::isValid('C:/var/www/deskpro/var/cache/example.txt', SafeFile::UNSPECIFIED));
        $this->assertTrue(SafeFile::isValid('C:/var/www/deskpro/var/cache/dontexist', SafeFile::UNSPECIFIED));
        $this->assertTrue(SafeFile::isValid('C:/tmp/foo', SafeFile::UNSPECIFIED));
        $this->assertTrue(SafeFile::isValid('C:/var/etc/mysql/my.cnf', SafeFile::UNSPECIFIED));

        $this->assertFalse(SafeFile::isValid('C:/var/www/deskpro/attachments/example.txt', SafeFile::UNSPECIFIED));
        $this->assertFalse(SafeFile::isValid('C:/var/www/deskpro/attachments/dontexist', SafeFile::UNSPECIFIED));
    }

    public function testException()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        SafeFile::assertValid('C:\\var\\www\\deskpro\\attachments\\example.txt', SafeFile::UNSPECIFIED);
    }

    public function testSafeFileWhitelist()
    {
        $this->assertTrue(SafeFile::isValid('C:\\tmp\\foo', 'C:\\tmp\\'));
        $this->assertTrue(SafeFile::isValid('C:\\tmp\\foo', ['C:\\bar\\', 'C:\\tmp\\']));

        $this->assertFalse(SafeFile::isValid('C:\\var\\www\\deskpro\\attachments\\example.txt', 'C:\\tmp\\'));
        $this->assertFalse(SafeFile::isValid('C:\\run\\foo', 'C:\\tmp\\'));

        $this->assertTrue(SafeFile::isValid('C:\\run\\foo\\bar\\baz', ['C:\\run\\bar\\', 'C:\\run\\foo\\bar\\']));
    }

    public function testSafeFileWhitelistSpecific()
    {
        $this->assertFalse(SafeFile::isValid('C:\\var\\log\\mail.log', 'C:\\var\\log\\nginx'));
        $this->assertTrue(SafeFile::isValid('C:\\var\\log\\mail.log', 'C:\\var\\log\\mail.log'));
        $this->assertTrue(SafeFile::isValid('C:\\var\\log\\mail.log', 'C:\\var\\log\\'));

        $this->assertTrue(SafeFile::isValid('C:\\var\\log\\nginx\\access.log', 'C:\\var\\log\\nginx\\'));
    }

    public function testSafeFileHttp()
    {
        $this->assertFalse(SafeFile::isValid('C:\\var\\log\\mail.log', SafeFile::HTTP));
        $this->assertFalse(SafeFile::isValid('file://var/log/mail.log', SafeFile::HTTP));
        $this->assertFalse(SafeFile::isValid('ftp://var/log/mail.log', SafeFile::HTTP));
        $this->assertTrue(SafeFile::isValid('http://google.com/', SafeFile::HTTP));
        $this->assertTrue(SafeFile::isValid('https://google.com/', SafeFile::HTTP));
        $this->assertTrue(SafeFile::isValid('HTTPS://google.com/', SafeFile::HTTP));
    }

    public function testSafeFileData()
    {
        $testFile = 'data:text\\plain;charset=utf-8;base64,VEVTVA==';

        $this->assertFalse(SafeFile::isValid('C:\\var\\log\\mail.log', SafeFile::DATA));
        $this->assertFalse(SafeFile::isValid('file://var/log/mail.log', SafeFile::DATA));
        $this->assertFalse(SafeFile::isValid('ftp://var/log/mail.log', SafeFile::DATA));
        $this->assertTrue(SafeFile::isValid($testFile, SafeFile::DATA));
    }
}
