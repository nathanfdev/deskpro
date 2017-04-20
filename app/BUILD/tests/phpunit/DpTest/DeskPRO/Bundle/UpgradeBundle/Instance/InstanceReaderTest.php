<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DpTest\DeskPRO\Bundle\UpdateBundle\Distro;

use DeskPRO\Bundle\UpdateBundle\Instance\InstanceReader;
use DpTest\DeskProTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class InstanceReaderTest extends DeskProTestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    public function setUp()
    {
        $this->root = vfsStream::setup('instance_test');
        vfsStream::create(require(__DIR__.'/../data/app_structure.php'), $this->root);
    }

    private function getInstanceReader()
    {
        return new InstanceReader(
            '15741.0',
            $this->root->getChild('app')->url(),
            $this->root->getChild('www')->url(),
            $this->root->getChild('var/kernel_cache')->url()
        );
    }

    /**
     * @test
     */
    public function it_instantiates()
    {
        $this->getInstanceReader();
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function it_throws_exception_on_invalid_path()
    {
        new InstanceReader(
            '15741.0',
            $this->root->url().'/foo',
            $this->root->getChild('www')->url(),
            $this->root->getChild('var/kernel_cache')->url()
        );
    }

    /**
     * @test
     */
    public function it_returns_correct_paths()
    {
        $inst = $this->getInstanceReader();

        $this->assertEquals($this->root->url().'/app', $inst->getAppBasePath());
        $this->assertEquals($this->root->url().'/app/FOO', $inst->getAppPath('FOO'));

        $this->assertEquals($this->root->url().'/var/kernel_cache', $inst->getKernelCacheBasePath());
        $this->assertEquals($this->root->url().'/var/kernel_cache/FOO', $inst->getKernelCachePath('FOO'));

        $this->assertEquals($this->root->url().'/www/assets', $inst->getAssetsBasePath());
        $this->assertEquals($this->root->url().'/www/assets/FOO', $inst->getAssetsPath('FOO'));
    }

    /**
     * @test
     */
    public function it_detects_build_dirs()
    {
        $inst = $this->getInstanceReader();

        $this->assertEquals([
            1465734864 => '15740.0',
            1465734865 => '15741.0',
        ], $inst->getBuilds());

        $this->assertTrue($inst->hasBuild('15740.0'));
        $this->assertFalse($inst->hasBuild('Foo'));
    }
}
