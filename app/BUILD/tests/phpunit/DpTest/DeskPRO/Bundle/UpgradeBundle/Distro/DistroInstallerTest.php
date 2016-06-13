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

namespace DpTest\DeskPRO\Bundle\UpgradeBundle\Distro;

use Alchemy\Zippy\Zippy;
use DeskPRO\Bundle\UpgradeBundle\Distro\DistroInstaller;
use DeskPRO\Bundle\UpgradeBundle\Instance\InstanceStatus;
use DpTest\DeskProTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class DistroInstallerTest extends DeskProTestCase
{
    /**
     * @var Zippy
     */
    private $zippy;

    /**
     * @var vfsStreamDirectory
     */
    private $root;

    /**
     * @var string[]
     */
    private $cleanupTmps = [];

    /**
     * @return string
     */
    private function getDistroZipPath()
    {
        return __DIR__.'/../data/distro_15742.0.zip';
    }

    public function setUp()
    {
        $this->zippy = Zippy::load();

        $this->root = vfsStream::setup('install_test');
        vfsStream::create(require(__DIR__.'/../data/app_structure.php'), $this->root);
    }

    /**
     * {@inheritdoc}
     */
    public function __destruct()
    {
        $this->cleanupTmps;
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $this->cleanupTempDirs();
    }

    public function cleanupTempDirs()
    {
        foreach ($this->cleanupTmps as $d) {
            //todo
        }

        $this->cleanupTmps = [];
    }

    /**
     * @return string
     */
    private function tmpAppStructure()
    {
        $tmpDir = sys_get_temp_dir().'/'.uniqid('install_test');

        // Dumps virtual structure to real fs so we can test the real
        // way we'll install files

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->root->url()), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
            /** @var \SplFileInfo $item */
            if ($item->getFilename() === '.' || $item->getFilename() === '..') {
                continue;
            }

            $path     = str_replace($this->root->url().'/', '', $item->getPathname());
            $realPath = $tmpDir.'/'.$path;

            if ($item->isDir()) {
                mkdir($realPath, 0755, true);
            } else {
                file_put_contents($realPath, file_get_contents($item->getPathname()));
            }

            chmod($realPath, $item->getPerms());
        }

        $this->cleanupTmps[] = $tmpDir;

        return $tmpDir;
    }

    /**
     * @test
     */
    public function it_installs_dirs()
    {
        $tmpDir = $this->tmpAppStructure();

        $instance = new InstanceStatus(
            $tmpDir.'/app',
            $tmpDir.'/www',
            $tmpDir.'/var/kernel_cache'
        );
        $inst = new DistroInstaller($this->zippy, $instance);
        $inst->installFromZip($this->getDistroZipPath());

        $this->assertTrue(is_file($tmpDir.'/app/15742.0/sys/config/build_num.txt'), 'Check that app dir was copied');
        $this->assertTrue(is_file($tmpDir.'/var/kernel_cache/15742.0/file.txt'), 'Check that kernel cache dir was copied');
        $this->assertTrue(is_dir($tmpDir.'/var/kernel_cache/15742.0/dp_run'), 'Check that dp_run was copied to kernel cache dir');
        $this->assertTrue(is_file($tmpDir.'/www/assets/15742.0/pub/file.txt'), 'Check that pub dir was copied');
        $this->assertTrue(is_file($tmpDir.'/www/assets/15742.0/web/file.txt'), 'Check that web dir was copied');
        $this->assertTrue(is_file($tmpDir.'/www/assets/15742.0/pub/deskpro.zip'), 'Check that deskpro.zip was copied');

        $inst->enableRunFromBuild('15742.0');
        $this->assertTrue(is_file($tmpDir.'/app/run/new_file.txt'), 'Check that run dir was enabled');
    }

    /**
     * @test
     * @expectedException DeskPRO\Component\Exception\Filesystem\FileWriteException
     */
    public function it_throws_exception_on_perm_error()
    {
        $tmpDir = $this->tmpAppStructure();

        chmod($tmpDir.'/app', 0555);

        $instance = new InstanceStatus(
            $tmpDir.'/app',
            $tmpDir.'/www',
            $tmpDir.'/var/kernel_cache'
        );
        $inst = new DistroInstaller($this->zippy, $instance);
        $inst->installFromZip($this->getDistroZipPath());
    }
}
