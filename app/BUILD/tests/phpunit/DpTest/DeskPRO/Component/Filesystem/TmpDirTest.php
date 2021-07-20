<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Component\Filesystem;

use DeskPRO\Component\Filesystem\TmpDir;
use DpTest\DeskProTestCase;

class TmpDirTest extends DeskProTestCase
{
    public function testTmpDir()
    {
        $tmpdir = new TmpDir();

        $this->assertStringStartsWith(TmpDir::getSysTempDir(), realpath(sys_get_temp_dir()));
        $this->assertStringStartsWith(TmpDir::getSysTempDir(), $tmpdir->getPath());
        $this->assertTrue(is_dir($tmpdir->getPath()), 'dir exists');

        $tmpdir->cleanup();
    }

    public function testTmpDirCreate()
    {
        $path = TmpDir::makeTmpDir();
        $this->assertStringStartsWith(TmpDir::getSysTempDir(), $path);
        $this->assertTrue(is_dir($path), 'dir exists');
    }

    public function testTmpDirCleanup()
    {
        $tmpdir = new TmpDir();
        $this->assertTrue(is_dir($tmpdir->getPath()), 'dir exists');

        $this->assertTrue(touch($tmpdir->getPath().DIRECTORY_SEPARATOR.'test1'));
        $this->assertTrue(mkdir($tmpdir->getPath().DIRECTORY_SEPARATOR.'sub'));
        $this->assertTrue(touch($tmpdir->getPath().DIRECTORY_SEPARATOR.'sub'.DIRECTORY_SEPARATOR.'test2'));

        $tmpdir->cleanup();
        $this->assertFalse(is_dir($tmpdir->getPath()), 'dir not exists after cleanup');
    }
}
