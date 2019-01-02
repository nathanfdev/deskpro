<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\UpdateBundle\BuildActivate\ReqCheck;

use Alchemy\Zippy\Zippy;
use DeskPRO\Bundle\UpdateBundle\BuildActivate\RunActivator\RunActivator;
use DeskPRO\Bundle\UpdateBundle\Instance\BuildInstance;
use DpTest\DeskProTestCase;
use org\bovigo\vfs\vfsStream;

class RunActivatorTest extends DeskProTestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    public function setUp()
    {
        $this->root = vfsStream::setup('instance_test');
        vfsStream::create(require(__DIR__.'/../../data/app_structure.php'), $this->root);
    }

    /**
     * @test
     */
    public function it_moves_run_dir()
    {
        $build = new BuildInstance(
            '15741.0',
            $this->root->url().'/app/15741.0',
            $this->root->url().'/www/assets/15741.0',
            $this->root->url().'/var/kernel_cache/15741.0'
        );

        $runActivator = new RunActivator(Zippy::load(), sys_get_temp_dir());
        $runActivator->activateRunDir($build);

        $this->assertTrue(file_exists($this->root->url().'/app/run/run_15741'));
        $this->assertFalse(file_exists($this->root->url().'/app/run/file.txt'));
    }

    /**
     * @test
     */
    public function it_skips_moving_already_set_build()
    {
        $build = new BuildInstance(
            '15740.0',
            $this->root->url().'/app/15740.0',
            $this->root->url().'/www/assets/15740.0',
            $this->root->url().'/var/kernel_cache/15740.0'
        );

        $runActivator = new RunActivator(Zippy::load(), sys_get_temp_dir());
        $runActivator->activateRunDir($build);

        $this->assertFalse(file_exists($this->root->url().'/app/run/run_15740'));
        $this->assertTrue(file_exists($this->root->url().'/app/run/file.txt'));
    }
}
