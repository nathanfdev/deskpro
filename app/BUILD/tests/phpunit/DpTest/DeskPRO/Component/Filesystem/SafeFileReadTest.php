<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Component\Filesystem;

use DeskPRO\Component\Filesystem\SafeFile;
use DpTest\DeskProTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class SafeFileReadTest extends DeskProTestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    public function setUp()
    {
        $this->root = vfsStream::setup('SafeFileReadTest');
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
        SafeFile::setEmitWarningsOption(false);
        SafeFile::addBlacklistDir($this->root->url().'/etc/');
        SafeFile::addBlacklistDir($this->root->url().'/var/log/');
        SafeFile::addBlacklistDir($this->root->url().'/var/www/deskpro/attachments/');
        SafeFile::addBlacklistDir($this->root->url().'/var/www/deskpro/backups/');
    }

    public function testSafeFileRead()
    {
        $this->assertEquals('xxx', SafeFile::fileGetContents($this->root->url().'/var/www/deskpro/var/cache/example.txt', SafeFile::UNSPECIFIED));
        $this->assertEquals('xxx', SafeFile::fileGetContents($this->root->url().'/var/etc/mysql/my.cnf', SafeFile::UNSPECIFIED));
        $this->assertEquals(false, SafeFile::fileGetContents($this->root->url().'/var/www/deskpro/attachments/noexist.txt', SafeFile::UNSPECIFIED));
        $this->assertEquals(false, SafeFile::fileGetContents($this->root->url().'/var/www/deskpro/attachments/example.txt', SafeFile::UNSPECIFIED));
        $this->assertEquals(false, SafeFile::fileGetContents($this->root->url().'/var/www/deskpro/attachments/noexist.txt', SafeFile::UNSPECIFIED));
    }
}
