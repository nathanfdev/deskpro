<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
