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

    public function testSafeFileMixedSlash()
    {
        $this->assertTrue(SafeFile::isValid('C:\\Windows\\Temp\\tmp_1631624849_CHUCBTXZNDXVWHDIWTSQCBHPH\\2429088.blob', 'C:\\Windows\\Temp\\tmp_1631624849_CHUCBTXZNDXVWHDIWTSQCBHPH'));
        $this->assertTrue(SafeFile::isValid('C:\\Windows\\Temp/tmp_1631624849_CHUCBTXZNDXVWHDIWTSQCBHPH\\2429088.blob', 'C:\\Windows\\Temp\\tmp_1631624849_CHUCBTXZNDXVWHDIWTSQCBHPH'));
        $this->assertTrue(SafeFile::isValid('C:\\Windows\\Temp/tmp_1631624849_CHUCBTXZNDXVWHDIWTSQCBHPH\\2429088.blob', 'C:\\Windows\\Temp\\/tmp_1631624849_CHUCBTXZNDXVWHDIWTSQCBHPH'));

        // mixed slash
        $this->assertEquals('C:/var/log/mail.log', SafeFile::normalizePath('C:\\var/log\\mail.log'));

        // double slash
        $this->assertEquals('C:/DeskPRO/DeskPRO/app/52602/locales/de/localeInfo.yml', SafeFile::normalizePath('C:\DeskPRO\DeskPRO\app\52602\/locales\de\localeInfo.yml'));
        $this->assertEquals('C:/DeskPRO/DeskPRO/app/52602/locales/de/localeInfo.yml', SafeFile::normalizePath('C:\DeskPRO\DeskPRO\app\52602//locales\de\localeInfo.yml'));
        $this->assertEquals('C:/DeskPRO/DeskPRO/app/52602/locales/de/localeInfo.yml', SafeFile::normalizePath('C:\DeskPRO\DeskPRO\app\52602\\\\locales\de\localeInfo.yml'));

        $this->assertTrue(SafeFile::isValid('C:\\var\\log\\mail.log', 'C:\\var\\log\\'));
    }
}
