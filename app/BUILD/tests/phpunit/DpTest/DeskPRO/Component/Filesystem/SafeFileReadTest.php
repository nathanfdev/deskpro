<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Component\Filesystem;

use DeskPRO\Component\Filesystem\SafeFile;
use DpTest\DeskProTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Symfony\Component\Filesystem\Filesystem;

class SafeFileReadTest extends DeskProTestCase
{
    private $baseDir;

    public function setUp()
    {
        $this->baseDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('test-', true);

        $files = [
            '/etc/mysql/my.cnf',
            '/var/log/nginx/access.log',
            '/var/log/mail.log',
            '/var/www/deskpro/attachments/example.txt',
            '/var/www/deskpro/var/cache/example.txt',
        ];

        foreach ($files as $f) {
            $fullpath = $this->baseDir . $f;
            $dirname = dirname($fullpath);

            if (!is_dir($dirname)) {
                mkdir($dirname, 0777, true);
            }
            file_put_contents($fullpath, 'xxx');
        }

        SafeFile::resetBlacklist();
        SafeFile::setEmitWarningsOption(false);
        SafeFile::addBlacklistDir($this->baseDir.'/etc/');
        SafeFile::addBlacklistDir($this->baseDir.'/var/log/');
        SafeFile::addBlacklistDir($this->baseDir.'/var/www/deskpro/attachments/');
        SafeFile::addBlacklistDir($this->baseDir.'/var/www/deskpro/backups/');
    }

    public function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->baseDir);
    }

    public function testSafeFileRead()
    {
        $this->assertEquals('xxx', SafeFile::fileGetContents($this->baseDir.'/var/www/deskpro/var/cache/example.txt', SafeFile::UNSPECIFIED));

        $this->assertEquals(false, SafeFile::fileGetContents($this->baseDir.'/etc/mysql/my.cnf', SafeFile::UNSPECIFIED));
        $this->assertEquals('xxx', SafeFile::fileGetContents($this->baseDir.'/etc/mysql/my.cnf', $this->baseDir.'/etc/mysql/'));

        $this->assertEquals(false, SafeFile::fileGetContents($this->baseDir.'/var/etc/mysql/my.cnf', SafeFile::UNSPECIFIED));
        $this->assertEquals(false, SafeFile::fileGetContents($this->baseDir.'/var/www/deskpro/attachments/noexist.txt', SafeFile::UNSPECIFIED));
        $this->assertEquals(false, SafeFile::fileGetContents($this->baseDir.'/var/www/deskpro/attachments/example.txt', SafeFile::UNSPECIFIED));
        $this->assertEquals(false, SafeFile::fileGetContents($this->baseDir.'/var/www/deskpro/attachments/noexist.txt', SafeFile::UNSPECIFIED));
    }

    public function testFail()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        SafeFile::assertValid($this->baseDir.'/etc/mysql/my.cnf', SafeFile::UNSPECIFIED);
    }
}
