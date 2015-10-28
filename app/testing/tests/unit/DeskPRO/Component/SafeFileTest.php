<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DpUnitTests\DeskPRO\Component\TaskRunner;

use DeskPRO\Component\Filesystem\SafeFile;

class SafeFileTest extends \DpUnitTestCase
{
    public function testRealUseBlacklist()
    {
        SafeFile::resetBlacklist();

        // example base dir
        $base_dir = '/var/www/deskpro/';

        SafeFile::addBlacklistDir($base_dir.'data/');
        SafeFile::addBlacklistDir($base_dir.'data/logs.');
        SafeFile::addBlacklistDir($base_dir.'data/tmp/');
        SafeFile::addBlacklistDir($base_dir.'data/cache/');
        SafeFile::addBlacklistDir($base_dir.'data/backups/');
        SafeFile::addBlacklistDir($base_dir.'data/debug/');
        SafeFile::addBlacklistFile($base_dir.'config.php');

        $this->assertFalse(SafeFile::isValid($base_dir.'config.php', '/var/'));
        $this->assertFalse(SafeFile::isValid($base_dir.'config.php', $base_dir));
        $this->assertFalse(SafeFile::isValid($base_dir.'config.php', array($base_dir, $base_dir.'config.php')));
        $this->assertTrue(SafeFile::isValid($base_dir.'dp.php', '/var/'));
        $this->assertTrue(SafeFile::isValid($base_dir.'dp.php', $base_dir));

        $this->assertFalse(SafeFile::isValid($base_dir.'data/backups/2015-10-28.database.sql.gz', '/var/'));
        $this->assertFalse(SafeFile::isValid($base_dir.'data/backups/2015-10-28.database.sql.gz', $base_dir));
        $this->assertFalse(SafeFile::isValid($base_dir.'data/backups/2015-10-28.database.sql.gz', $base_dir.'data/'));
        $this->assertFalse(SafeFile::isValid($base_dir.'data/backups/2015-10-28.database.sql.gz', $base_dir.'data/backups/'));
        $this->assertFalse(SafeFile::isValid($base_dir.'data/backups/deep-subdir/2015-10-28.database.sql.gz', $base_dir.'data/backups/'));
        $this->assertTrue(SafeFile::isValid($base_dir.'data/backups/deep-subdir/2015-10-28.database.sql.gz', $base_dir.'data/backups/deep-subdir/'));
    }

    public function testRealUseWindowsBlacklist()
    {
        SafeFile::resetBlacklist();

        // example base dir
        $base_dir = 'C:\\inetpub\\www\\';

        SafeFile::addBlacklistDir($base_dir.'data\\');
        SafeFile::addBlacklistDir($base_dir.'data\\logs.');
        SafeFile::addBlacklistDir($base_dir.'data\\tmp\\');
        SafeFile::addBlacklistDir($base_dir.'data\\cache\\');
        SafeFile::addBlacklistDir($base_dir.'data\\backups\\');
        SafeFile::addBlacklistDir($base_dir.'data\\debug\\');
        SafeFile::addBlacklistFile($base_dir.'config.php');

        $this->assertFalse(SafeFile::isValid($base_dir.'config.php', 'C:\\inetpub\\'));
        $this->assertFalse(SafeFile::isValid($base_dir.'config.php', $base_dir));
        $this->assertFalse(SafeFile::isValid($base_dir.'config.php', array($base_dir, $base_dir.'config.php')));
        $this->assertTrue(SafeFile::isValid($base_dir.'dp.php', 'C:\\inetpub\\'));
        $this->assertTrue(SafeFile::isValid($base_dir.'dp.php', $base_dir));

        $this->assertFalse(SafeFile::isValid($base_dir.'data\\backups\\2015-10-28.database.sql.gz', 'C:\\inetpub\\'));
        $this->assertFalse(SafeFile::isValid($base_dir.'data\\backups\\2015-10-28.database.sql.gz', $base_dir));
        $this->assertFalse(SafeFile::isValid($base_dir.'data\\backups\\2015-10-28.database.sql.gz', $base_dir.'data\\'));
        $this->assertFalse(SafeFile::isValid($base_dir.'data\\backups\\2015-10-28.database.sql.gz', $base_dir.'data\\backups\\'));
        $this->assertFalse(SafeFile::isValid($base_dir.'data\\backups\\deep-subdir\\2015-10-28.database.sql.gz', $base_dir.'data\\backups\\'));
        $this->assertTrue(SafeFile::isValid($base_dir.'data\\backups\\deep-subdir\\2015-10-28.database.sql.gz', $base_dir.'data\\backups\\deep-subdir\\'));
    }

    public function testBlacklist()
    {
        SafeFile::resetBlacklist();
        SafeFile::addBlacklistDir('/example/foo');
        SafeFile::addBlacklistDir('/example/foo/bar/baz');
        SafeFile::addBlacklistFile('/example/exact.txt');
        SafeFile::addBlacklistFile('/example/foo/bar/baz/exact.txt');

        $this->assertFalse(SafeFile::isValid('/var/nomatch.txt', '/example/'));
        $this->assertFalse(SafeFile::isValid('/var/nomatch.txt'));

        $this->assertFalse(SafeFile::isValid('/example/foo/bar/test.txt', '/example/'));
        $this->assertTrue(SafeFile::isValid('/example/foo/bar/test.txt', '/example/foo/bar/'));

        $this->assertFalse(SafeFile::isValid('/example/foo/bar/baz/test.txt', '/example/'));
        $this->assertFalse(SafeFile::isValid('/example/foo/bar/baz/test.txt', '/example/foo/bar/'));
        $this->assertfalse(SafeFile::isValid('/example/foo/bar/baz/test.txt', '/example/foo/bar/baz/'));
        $this->assertTrue(SafeFile::isValid('/example/foo/bar/baz/deep/test.txt', '/example/foo/bar/baz/deep/'));

        $this->assertFalse(SafeFile::isValid('/example/exact.txt', '/example/'));
        $this->assertFalse(SafeFile::isValid('/example/foo/bar/baz/exact.txt', '/example/'));
        $this->assertFalse(SafeFile::isValid('/example/foo/bar/baz/exact.txt', '/example/foo/bar/baz/'));
        $this->assertFalse(SafeFile::isValid('/example/foo/bar/baz/exact.txt', array('/example/foo/bar/baz/', '/example/foo/bar/baz/exact.txt')));
    }
}
