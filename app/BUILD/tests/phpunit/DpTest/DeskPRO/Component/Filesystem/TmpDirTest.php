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
        $tmpdir = new TmpDir(dp_get_tmp_dir());
        $this->assertStringStartsWith(realpath(dp_get_tmp_dir()), $tmpdir->getPath());
        $this->assertTrue(is_dir($tmpdir->getPath()), 'dir exists');
        $tmpdir->cleanup();
    }

    public function testTmpDirCrate()
    {
        $path = TmpDir::makeTmpDir(dp_get_tmp_dir());
        $this->assertStringStartsWith(realpath(dp_get_tmp_dir()), $path);
        $this->assertTrue(is_dir($path), 'dir exists');
    }

    public function testTmpDirCleanup()
    {
        $tmpdir = new TmpDir(dp_get_tmp_dir());
        $this->assertTrue(is_dir($tmpdir->getPath()), 'dir exists');

        $this->assertTrue(touch($tmpdir->getPath().DIRECTORY_SEPARATOR.'test1'));
        $this->assertTrue(mkdir($tmpdir->getPath().DIRECTORY_SEPARATOR.'sub'));
        $this->assertTrue(touch($tmpdir->getPath().DIRECTORY_SEPARATOR.'sub'.DIRECTORY_SEPARATOR.'test2'));

        $tmpdir->cleanup();
        $this->assertFalse(is_dir($tmpdir->getPath()), 'dir not exists after cleanup');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testBadTmpDir()
    {
        new TmpDir('/this/does/not/exist');
    }
}
