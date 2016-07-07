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

    /**
     * @test
     * @expectedException \Symfony\Component\Filesystem\Exception\IOException
     */
    public function it_excepts_when_cannot_write()
    {
        $build = new BuildInstance(
            '15741.0',
            $this->root->url().'/app/15741.0',
            $this->root->url().'/www/assets/15741.0',
            $this->root->url().'/var/kernel_cache/15741.0'
        );

        chmod($this->root->url().'/app', 0555);

        $runActivator = new RunActivator(Zippy::load(), sys_get_temp_dir());
        $runActivator->activateRunDir($build);
    }
}
